<?php

namespace MGS\Brand\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Template implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '1column',
                'label' => __('1 column')
            ],
            [
                'value' => '2columns-left',
                'label' => __('2 columns left')
            ],
            [
                'value' => '2columns-right',
                'label' => __('2 columns right')
            ],
            [
                'value' => '3columns',
                'label' => __('3 columns')
            ]
        ];
    }

}
