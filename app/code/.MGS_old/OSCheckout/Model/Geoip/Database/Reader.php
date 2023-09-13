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

namespace MGS\OSCheckout\Model\Geoip\Database;

use MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader as DbReader;
use MGS\OSCheckout\Model\Geoip\ProviderInterface;

class Reader implements ProviderInterface
{
    /**
     * @type \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader
     */
    private $_dbReader;

    /**
     * @type array
     */
    private $locales;


    /**
     * @param \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader $dbreader
     */
    public function __construct(
        DbReader $dbreader
    )
    {
        $this->_dbReader = $dbreader;
        $this->locales = array('en');
    }

    /**
     * This method returns a GeoIP2 City model.
     * @param string $ipAddress IPv4 or IPv6 address as a string.
     * @return array
     */
    public function city($ipAddress)
    {
        return $this->modelFor('City', 'City', $ipAddress);
    }

    /**
     * This method returns a GeoIP2 Country model.
     * @param string $ipAddress IPv4 or IPv6 address as a string.
     * @return array
     */
    public function country($ipAddress)
    {
        return $this->modelFor('Country', 'Country', $ipAddress);
    }


    /**
     * @param $class
     * @param $type
     * @param $ipAddress
     * @return array
     */
    private function modelFor($class, $type, $ipAddress)
    {
        $record = $this->getRecord($class, $type, $ipAddress);

        $record['traits']['ip_address'] = $ipAddress;
        $this->close();

        return $record;
    }

    /**
     * @param $class
     * @param $type
     * @param $ipAddress
     * @return array
     * @throws \Exception
     */
    private function getRecord($class, $type, $ipAddress)
    {
        if (strpos($this->metadata()->databaseType, $type) === false) {
            $method = lcfirst($class);
            throw new \Exception(
                "The $method method cannot be used to open a "
                . $this->metadata()->databaseType . " database"
            );
        }
        $record = $this->_dbReader->get($ipAddress);
        if ($record === null) {
            throw new \Exception(
                "The address $ipAddress is not in the database."
            );
        }
        if (!is_array($record)) {

            throw new \Exception(
                "Expected an array when looking up $ipAddress but received: "
                . gettype($record)
            );
        }

        return $record;
    }

    /**
     * @return \MGS\OSCheckout\Model\Geoip\Maxmind\Db\Reader\Metadata object for the database.
     * @throws \BadMethodCallException if the database has been closed.
     * @throws \InvalidArgumentException if arguments are passed to the method.
     */
    public function metadata()
    {
        return $this->_dbReader->metadata();
    }


    public function close()
    {
        $this->_dbReader->close();
    }
}
