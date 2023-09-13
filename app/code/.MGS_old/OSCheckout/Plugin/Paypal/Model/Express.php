<?php
/**
 * Express
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Plugin\Paypal\Model;

use Magento\Quote\Api\Data\PaymentInterface;

class Express
{
    /**
     * @param \Magento\Paypal\Model\Express $express
     * @param \Magento\Framework\DataObject $data
     * @return array
     */
    public function beforeAssignData(\Magento\Paypal\Model\Express $express, \Magento\Framework\DataObject $data)
    {
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (is_array($additionalData) && isset($additionalData['extension_attributes'])) {
            unset($additionalData['extension_attributes']);
            $data->setData(PaymentInterface::KEY_ADDITIONAL_DATA, $additionalData);
        }

        return [$data];
    }
}
