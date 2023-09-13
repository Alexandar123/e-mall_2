<?php
/**
 * DeliveryTime
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model\System\Config\Source;

use Magento\Framework\Model\AbstractModel;

class DeliveryTime extends AbstractModel
{
    const DAY_MONTH_YEAR = 'dd/mm/yy';
    const MONTH_DAY_YEAR = 'mm/dd/yy';
    const YEAR_MONTH_DAY = 'yy/mm/dd';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Day/Month/Year'),
                'value' => self::DAY_MONTH_YEAR
            ],
            [
                'label' => __('Month/Day/Year'),
                'value' => self::MONTH_DAY_YEAR
            ],
            [
                'label' => __('Year/Month/Day'),
                'value' => self::YEAR_MONTH_DAY
            ]
        ];

        return $options;
    }
}
