<?php
/**
 * Decoder
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader;


class Decoder
{

    /**
     * @type
     */
    private $fileStream;

    /**
     * @type
     */
    private $pointerBase;

    /**
     * @var \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\InvalidDatabaseException
     */
    private $invalidDatabaseException;

    /**
     * @var \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\Util
     */
    private $util;

    // This is only used for unit testing
    private $pointerTestHack;

    /**
     * @type
     */
    private $switchByteOrder;

    /**
     * @type array
     */
    private $types = array(
        0 => 'extended',
        1 => 'pointer',
        2 => 'utf8_string',
        3 => 'double',
        4 => 'bytes',
        5 => 'uint16',
        6 => 'uint32',
        7 => 'map',
        8 => 'int32',
        9 => 'uint64',
        10 => 'uint128',
        11 => 'array',
        12 => 'container',
        13 => 'end_marker',
        14 => 'boolean',
        15 => 'float',
    );

    /**
     * @param \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\InvalidDatabaseException $invalidDatabaseException
     * @param \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\Util $until
     */
    public function __construct(
        InvalidDatabaseException $invalidDatabaseException,
        Util                     $until
    )
    {
        $this->invalidDatabaseException = $invalidDatabaseException;
        $this->util = $until;
    }

    /**
     * @param $fileStream
     * @param int $pointerBase
     * @param bool|false $pointerTestHack
     * @return $this
     */
    public function init($fileStream, $pointerBase = 0, $pointerTestHack = false)
    {
        $this->fileStream = $fileStream;
        $this->pointerBase = $pointerBase;
        $this->pointerTestHack = $pointerTestHack;
        $this->switchByteOrder = $this->isPlatformLittleEndian();

        return $this;
    }

    /**
     * @param $offset
     * @return array
     */
    public function decode($offset)
    {
        list(, $ctrlByte) = unpack(
            'C',
            Util::read($this->fileStream, $offset, 1)
        );
        $offset++;

        $type = $this->types[$ctrlByte >> 5];

        // Pointers are a special case, we don't read the next $size bytes, we
        // use the size to determine the length of the pointer and then follow
        // it.
        if ($type == 'pointer') {
            list($pointer, $offset) = $this->decodePointer($ctrlByte, $offset);

            // for unit testing
            if ($this->pointerTestHack) {
                return array($pointer);
            }

            list($result) = $this->decode($pointer);

            return array($result, $offset);
        }

        if ($type == 'extended') {
            list(, $nextByte) = unpack(
                'C',
                Util::read($this->fileStream, $offset, 1)
            );

            $typeNum = $nextByte + 7;

            if ($typeNum < 8) {
                throw new InvalidDatabaseException(
                    "Something went horribly wrong in the decoder. An extended type "
                    . "resolved to a type number < 8 ("
                    . $this->types[$typeNum]
                    . ")"
                );
            }

            $type = $this->types[$typeNum];
            $offset++;
        }

        list($size, $offset) = $this->sizeFromCtrlByte($ctrlByte, $offset);

        return $this->decodeByType($type, $offset, $size);
    }

    /**
     * @param $type
     * @param $offset
     * @param $size
     * @return array
     */
    private function decodeByType($type, $offset, $size)
    {
        switch ($type) {
            case 'map':
                return $this->decodeMap($size, $offset);
            case 'array':
                return $this->decodeArray($size, $offset);
            case 'boolean':
                return array($this->decodeBoolean($size), $offset);
        }

        $newOffset = $offset + $size;
        $bytes = Util::read($this->fileStream, $offset, $size);
        switch ($type) {
            case 'utf8_string':
                return array($this->decodeString($bytes), $newOffset);
            case 'double':
                $this->verifySize(8, $size);

                return array($this->decodeDouble($bytes), $newOffset);
            case 'float':
                $this->verifySize(4, $size);

                return array($this->decodeFloat($bytes), $newOffset);
            case 'bytes':
                return array($bytes, $newOffset);
            case 'uint16':
            case 'uint32':
                return array($this->decodeUint($bytes), $newOffset);
            case 'int32':
                return array($this->decodeInt32($bytes), $newOffset);
            case 'uint64':
            case 'uint128':
                return array($this->decodeBigUint($bytes, $size), $newOffset);
            default:
                throw new InvalidDatabaseException(
                    "Unknown or unexpected type: " . $type
                );
        }
    }

    /**
     * @param $expected
     * @param $actual
     */
    private function verifySize($expected, $actual)
    {
        if ($expected != $actual) {
            throw new InvalidDatabaseException(
                "The MaxMind DB file's data section contains bad data (unknown data type or corrupt data)"
            );
        }
    }

    /**
     * @param $size
     * @param $offset
     * @return array
     */
    private function decodeArray($size, $offset)
    {
        $array = array();

        for ($i = 0; $i < $size; $i++) {
            list($value, $offset) = $this->decode($offset);
            array_push($array, $value);
        }

        return array($array, $offset);
    }

    /**
     * @param $size
     * @return bool
     */
    private function decodeBoolean($size)
    {
        return $size == 0 ? false : true;
    }

    /**
     * @param $bits
     * @return mixed
     */
    private function decodeDouble($bits)
    {
        // XXX - Assumes IEEE 754 double on platform
        list(, $double) = unpack('d', $this->maybeSwitchByteOrder($bits));

        return $double;
    }

    /**
     * @param $bits
     * @return mixed
     */
    private function decodeFloat($bits)
    {
        // XXX - Assumes IEEE 754 floats on platform
        list(, $float) = unpack('f', $this->maybeSwitchByteOrder($bits));

        return $float;
    }

    /**
     * @param $bytes
     * @return mixed
     */
    private function decodeInt32($bytes)
    {
        $bytes = $this->zeroPadLeft($bytes, 4);
        list(, $int) = unpack('l', $this->maybeSwitchByteOrder($bytes));

        return $int;
    }

    /**
     * @param $size
     * @param $offset
     * @return array
     */
    private function decodeMap($size, $offset)
    {

        $map = array();

        for ($i = 0; $i < $size; $i++) {
            list($key, $offset) = $this->decode($offset);
            list($value, $offset) = $this->decode($offset);
            $map[$key] = $value;
        }

        return array($map, $offset);
    }

    private $pointerValueOffset = array(
        1 => 0,
        2 => 2048,
        3 => 526336,
        4 => 0,
    );

    /**
     * @param $ctrlByte
     * @param $offset
     * @return array
     */
    private function decodePointer($ctrlByte, $offset)
    {
        $pointerSize = (($ctrlByte >> 3) & 0x3) + 1;

        $buffer = Util::read($this->fileStream, $offset, $pointerSize);
        $offset = $offset + $pointerSize;

        $packed = $pointerSize == 4
            ? $buffer
            : (pack('C', $ctrlByte & 0x7)) . $buffer;

        $unpacked = $this->decodeUint($packed);
        $pointer = $unpacked + $this->pointerBase
            + $this->pointerValueOffset[$pointerSize];

        return array($pointer, $offset);
    }

    /**
     * @param $bytes
     * @return mixed
     */
    private function decodeUint($bytes)
    {
        list(, $int) = unpack('N', $this->zeroPadLeft($bytes, 4));

        return $int;
    }

    /**
     * @param $bytes
     * @param $byteLength
     * @return int|string
     */
    private function decodeBigUint($bytes, $byteLength)
    {
        $maxUintBytes = log(PHP_INT_MAX, 2) / 8;

        if ($byteLength == 0) {
            return 0;
        }

        $numberOfLongs = ceil($byteLength / 4);
        $paddedLength = $numberOfLongs * 4;
        $paddedBytes = $this->zeroPadLeft($bytes, $paddedLength);
        $unpacked = array_merge(unpack("N$numberOfLongs", $paddedBytes));

        $integer = 0;

        // 2^32
        $twoTo32 = '4294967296';

        foreach ($unpacked as $part) {
            // We only use gmp or bcmath if the final value is too big
            if ($byteLength <= $maxUintBytes) {
                $integer = ($integer << 32) + $part;
            } elseif (extension_loaded('gmp')) {
                $integer = gmp_strval(gmp_add(gmp_mul($integer, $twoTo32), $part));
            } elseif (extension_loaded('bcmath')) {
                $integer = bcadd(bcmul($integer, $twoTo32), $part);
            } else {
                throw new \RuntimeException(
                    'The gmp or bcmath extension must be installed to read this database.'
                );
            }
        }

        return $integer;
    }

    /**
     * @param $bytes
     * @return mixed
     */
    private function decodeString($bytes)
    {
        // XXX - NOOP. As far as I know, the end user has to explicitly set the
        // encoding in PHP. Strings are just bytes.
        return $bytes;
    }

    /**
     * @param $ctrlByte
     * @param $offset
     * @return array
     */
    private function sizeFromCtrlByte($ctrlByte, $offset)
    {
        $size = $ctrlByte & 0x1f;
        $bytesToRead = $size < 29 ? 0 : $size - 28;
        $bytes = Util::read($this->fileStream, $offset, $bytesToRead);
        $decoded = $this->decodeUint($bytes);

        if ($size == 29) {
            $size = 29 + $decoded;
        } elseif ($size == 30) {
            $size = 285 + $decoded;
        } elseif ($size > 30) {
            $size = ($decoded & (0x0FFFFFFF >> (32 - (8 * $bytesToRead))))
                + 65821;
        }

        return array($size, $offset + $bytesToRead);
    }

    /**
     * @param $content
     * @param $desiredLength
     * @return string
     */
    private function zeroPadLeft($content, $desiredLength)
    {
        return str_pad($content, $desiredLength, "\x00", STR_PAD_LEFT);
    }

    /**
     * @param $bytes
     * @return string
     */
    private function maybeSwitchByteOrder($bytes)
    {
        return $this->switchByteOrder ? strrev($bytes) : $bytes;
    }

    /**
     * @return bool
     */
    private function isPlatformLittleEndian()
    {
        $testint = 0x00FF;
        $packed = pack('S', $testint);

        return $testint === current(unpack('v', $packed));
    }
}
