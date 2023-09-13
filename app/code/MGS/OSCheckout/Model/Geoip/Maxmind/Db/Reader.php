<?php
/**
 * Reader
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model\Geoip\Maxmind\Db;

use MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\Decoder;
use MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\InvalidDatabaseException;
use MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\Metadata;
use MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\Util;

class Reader
{
    private static $DATA_SECTION_SEPARATOR_SIZE = 16;
    private static $METADATA_START_MARKER = "\xAB\xCD\xEFMaxMind.com";
    private static $METADATA_START_MARKER_LENGTH = 14;
    private static $METADATA_MAX_SIZE = 131072; // 128 * 1024 = 128KB

    /**
     * @type \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\Decoder
     */
    private $decoder;

    /**
     * @type \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\InvalidDatabaseException
     */
    private $invalidDatabaseException;

    /**
     * @type \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\Util
     */
    private $util;

    /**
     * @type
     */
    private $fileHandle;

    /**
     * @type
     */
    private $fileSize;

    /**
     * @type
     */
    private $ipV4Start;

    /**
     * @type \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\Metadata
     */
    private $metadata;

    /**
     * @param \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\Decoder $decoder
     * @param \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\InvalidDatabaseException $invalidDatabaseException
     * @param \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\Metadata $metadata
     * @param \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\Util $util
     */
    public function __construct(
        Decoder                  $decoder,
        InvalidDatabaseException $invalidDatabaseException,
        Metadata                 $metadata,
        Util                     $util
    )
    {
        $this->decoder = $decoder;
        $this->invalidDatabaseException = $invalidDatabaseException;
        $this->metadata = $metadata;
        $this->util = $util;
        $this->initReader();
    }

    public function initReader()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $database = $objectManager->create('MGS\OSCheckout\Helper\Data')->checkHasLibrary();
        if (!$database) {
            return $this;
        }

        if (!is_readable($database)) {
            throw new \InvalidArgumentException(
                "The file \"$database\" does not exist or is not readable."
            );
        }
        $this->fileHandle = @fopen($database, 'rb');
        if ($this->fileHandle === false) {
            throw new \InvalidArgumentException(
                "Error opening \"$database\"."
            );
        }
        $this->fileSize = @filesize($database);
        if ($this->fileSize === false) {
            throw new \UnexpectedValueException(
                "Error determining the size of \"$database\"."
            );
        }

        $start = $this->findMetadataStart($database);
        $metadataDecoder = $this->decoder->init($this->fileHandle, $start);
        // // $metadataDecoder = new Decoder($this->fileHandle, $start);
        list($metadataArray) = $metadataDecoder->decode($start);
        $this->metadata = $this->metadata->init($metadataArray);
        $this->decoder = $this->decoder->init(
            $this->fileHandle,
            $this->metadata->searchTreeSize + self::$DATA_SECTION_SEPARATOR_SIZE
        );
    }

    /**
     * Looks up the <code>address</code> in the MaxMind DB.
     *
     * @param string $ipAddress
     *            the IP address to look up.
     * @return array the record for the IP address.
     * @throws \BadMethodCallException if this method is called on a closed database.
     * @throws \InvalidArgumentException if something other than a single IP address is passed to the method.
     * @throws InvalidDatabaseException
     *             if the database is invalid or there is an error reading
     *             from it.
     */
    public function get($ipAddress)
    {
        if (func_num_args() != 1) {
            throw new \InvalidArgumentException(
                'Method takes exactly one argument.'
            );
        }

        if (!is_resource($this->fileHandle)) {
            throw new \BadMethodCallException(
                'Attempt to read from a closed MaxMind DB.'
            );
        }

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(
                "The value \"$ipAddress\" is not a valid IP address."
            );
        }

        if ($this->metadata->ipVersion == 4 && strrpos($ipAddress, ':')) {
            throw new \InvalidArgumentException(
                "Error looking up $ipAddress. You attempted to look up an"
                . " IPv6 address in an IPv4-only database."
            );
        }
        $pointer = $this->findAddressInTree($ipAddress);
        if ($pointer == 0) {
            return null;
        }

        return $this->resolveDataPointer($pointer);
    }

    /**
     * @param $ipAddress
     * @return int
     * @throws \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\InvalidDatabaseException
     */
    private function findAddressInTree($ipAddress)
    {
        // XXX - could simplify. Done as a byte array to ease porting
        $rawAddress = array_merge(unpack('C*', inet_pton($ipAddress)));

        $bitCount = count($rawAddress) * 8;

        // The first node of the tree is always node 0, at the beginning of the
        // value
        $node = $this->startNode($bitCount);

        for ($i = 0; $i < $bitCount; $i++) {
            if ($node >= $this->metadata->nodeCount) {
                break;
            }
            $tempBit = 0xFF & $rawAddress[$i >> 3];
            $bit = 1 & ($tempBit >> 7 - ($i % 8));

            $node = $this->readNode($node, $bit);
        }
        if ($node == $this->metadata->nodeCount) {
            // Record is empty
            return 0;
        } elseif ($node > $this->metadata->nodeCount) {
            // Record is a data pointer
            return $node;
        }
        throw new InvalidDatabaseException("Something bad happened");
    }

    /**
     * @param $length
     * @return int
     */
    private function startNode($length)
    {
        // Check if we are looking up an IPv4 address in an IPv6 tree. If this
        // is the case, we can skip over the first 96 nodes.
        if ($this->metadata->ipVersion == 6 && $length == 32) {
            return $this->ipV4StartNode();
        }
        // The first node of the tree is always node 0, at the beginning of the
        // value
        return 0;
    }

    /**
     * @return int
     * @throws \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\InvalidDatabaseException
     */
    private function ipV4StartNode()
    {
        //This is a defensive check. There is no reason to call this when you
        //have an IPv4 tree.
        if ($this->metadata->ipVersion == 4) {
            return 0;
        }

        if ($this->ipV4Start != 0) {
            return $this->ipV4Start;
        }
        $node = 0;

        for ($i = 0; $i < 96 && $node < $this->metadata->nodeCount; $i++) {
            $node = $this->readNode($node, 0);
        }
        $this->ipV4Start = $node;

        return $node;
    }

    /**
     * @param $nodeNumber
     * @param $index
     * @return mixed
     * @throws \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\InvalidDatabaseException
     */
    private function readNode($nodeNumber, $index)
    {
        $baseOffset = $nodeNumber * $this->metadata->nodeByteSize;

        // XXX - probably could condense this.
        switch ($this->metadata->recordSize) {
            case 24:
                $bytes = Util::read($this->fileHandle, $baseOffset + $index * 3, 3);
                list(, $node) = unpack('N', "\x00" . $bytes);

                return $node;
            case 28:
                $middleByte = Util::read($this->fileHandle, $baseOffset + 3, 1);
                list(, $middle) = unpack('C', $middleByte);
                if ($index == 0) {
                    $middle = (0xF0 & $middle) >> 4;
                } else {
                    $middle = 0x0F & $middle;
                }
                $bytes = Util::read($this->fileHandle, $baseOffset + $index * 4, 3);
                list(, $node) = unpack('N', chr($middle) . $bytes);

                return $node;
            case 32:
                $bytes = Util::read($this->fileHandle, $baseOffset + $index * 4, 4);
                list(, $node) = unpack('N', $bytes);

                return $node;
            default:
                throw new InvalidDatabaseException(
                    'Unknown record size: '
                    . $this->metadata->recordSize
                );
        }
    }

    /**
     * @param $pointer
     * @return mixed
     * @throws \MGS\OSCheckout\Model\Geoip\MaxMind\Db\Reader\InvalidDatabaseException
     */
    private function resolveDataPointer($pointer)
    {
        $resolved = $pointer - $this->metadata->nodeCount
            + $this->metadata->searchTreeSize;
        if ($resolved > $this->fileSize) {
            throw new InvalidDatabaseException(
                "The MaxMind DB file's search tree is corrupt"
            );
        }

        list($data) = $this->decoder->decode($resolved);

        return $data;
    }

    /*
     * This is an extremely naive but reasonably readable implementation. There
     * are much faster algorithms (e.g., Boyer-Moore) for this if speed is ever
     * an issue, but I suspect it won't be.
     */
    private function findMetadataStart($filename)
    {
        $handle = $this->fileHandle;
        $fstat = fstat($handle);
        $fileSize = $fstat['size'];
        $marker = self::$METADATA_START_MARKER;
        $markerLength = self::$METADATA_START_MARKER_LENGTH;
        $metadataMaxLengthExcludingMarker
            = min(self::$METADATA_MAX_SIZE, $fileSize) - $markerLength;

        for ($i = 0; $i <= $metadataMaxLengthExcludingMarker; $i++) {
            for ($j = 0; $j < $markerLength; $j++) {
                fseek($handle, $fileSize - $i - $j - 1);
                $matchBit = fgetc($handle);
                if ($matchBit != $marker[$markerLength - $j - 1]) {
                    continue 2;
                }
            }

            return $fileSize - $i;
        }
        throw new InvalidDatabaseException(
            "Error opening database file ($filename). " .
            'Is this a valid MaxMind DB file?'
        );
    }

    /**
     * @return Metadata object for the database.
     * @throws \BadMethodCallException if the database has been closed.
     * @throws \InvalidArgumentException if arguments are passed to the method.
     */
    public function metadata()
    {
        if (func_num_args()) {
            throw new \InvalidArgumentException(
                'Method takes no arguments.'
            );
        }

        // Not technically required, but this makes it consistent with
        // C extension and it allows us to change our implementation later.
        if (!is_resource($this->fileHandle)) {
            throw new \BadMethodCallException(
                'Attempt to read from a closed MaxMind DB.'
            );
        }

        return $this->metadata;
    }

    /**
     * Closes the MaxMind DB and returns resources to the system.
     *
     * @throws \Exception
     *             if an I/O error occurs.
     */
    public function close()
    {
        if (!is_resource($this->fileHandle)) {
            throw new \BadMethodCallException(
                'Attempt to close a closed MaxMind DB.'
            );
        }
        fclose($this->fileHandle);
    }
}
