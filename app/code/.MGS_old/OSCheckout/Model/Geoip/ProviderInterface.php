<?php
/**
 * ProviderInterface
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model\Geoip;


interface ProviderInterface
{
    /**
     * @param ipAddress
     *            IPv4 or IPv6 address to lookup.
     * @return Country model for the requested IP address.
     */
    public function country($ipAddress);

    /**
     * @param ipAddress
     *            IPv4 or IPv6 address to lookup.
     * @return City model for the requested IP address.
     */
    public function city($ipAddress);
}
