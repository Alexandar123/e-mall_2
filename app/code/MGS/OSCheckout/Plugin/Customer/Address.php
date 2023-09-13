<?php
/**
 * Address
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Plugin\Customer;

use Magento\Customer\Api\Data\AddressInterface;

class Address
{
    /**
     * @param \Magento\Customer\Model\Address $subject
     * @param \Closure $proceed
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return mixed
     */
    public function aroundUpdateData(\Magento\Customer\Model\Address $subject, \Closure $proceed, AddressInterface $address)
    {
        $object = $proceed($address);

        $addressData = $address->__toArray();
        if (isset($addressData['should_ignore_validation'])) {
            $object->setShouldIgnoreValidation($addressData['should_ignore_validation']);
        }

        return $object;
    }
}
