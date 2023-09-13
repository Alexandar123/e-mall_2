<?php
/**
 * Design
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model\System\Config\Source;

class Design
{
    const DESIGN_DEFAULT = 'default';
    const DESIGN_FLAT = 'flat';
    const DESIGN_MATERIAL = 'material';

    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Default'),
                'value' => self::DESIGN_DEFAULT
            ],
            [
                'label' => __('Flat'),
                'value' => self::DESIGN_FLAT
            ],
            [
                'label' => __('Material'),
                'value' => self::DESIGN_MATERIAL
            ]
        ];

        return $options;
    }
}
