<?php

namespace MGS\Blog\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Sort implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'DESC',
                'label' => __('Newest')
            ],
            [
                'value' => 'ASC',
                'label' => __('Older')
            ]
        ];
    }

}
