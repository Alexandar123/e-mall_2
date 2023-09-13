<?php


namespace MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader;

/**
 * Class Util
 * @package MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader
 */
class Util
{
    /**
     * @param $stream
     * @param $offset
     * @param $numberOfBytes
     * @return bool|string
     * @throws \Exception
     */
    public static function read($stream, $offset, $numberOfBytes)
    {
        if ($numberOfBytes == 0) {
            return '';
        }
        if (fseek($stream, $offset) == 0) {
            $value = fread($stream, $numberOfBytes);

            // We check that the number of bytes read is equal to the number
            // asked for. We use ftell as getting the length of $value is
            // much slower.
            if (ftell($stream) - $offset === $numberOfBytes) {
                return $value;
            }
        }
        throw new \Exception(
            "The MaxMind DB file contains bad data"
        );
    }
}
