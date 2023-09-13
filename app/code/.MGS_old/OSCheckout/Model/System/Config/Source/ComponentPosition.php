<?php
/**
 * ComponentPosition
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model\System\Config\Source;

use Magento\Framework\Model\AbstractModel;


class ComponentPosition extends AbstractModel
{
    const NOT_SHOW = 0;
    const SHOW_IN_PAYMENT = 1;
    const SHOW_IN_REVIEW = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::NOT_SHOW => __('No'),
            self::SHOW_IN_PAYMENT => __('In Payment Area'),
            self::SHOW_IN_REVIEW => __('In Review Area')
        ];
    }
}
