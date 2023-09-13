<?php

namespace MGS\Blog\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Yesno implements ArrayInterface
{
    const YES = 1;
    const NO = 0;

    public function toOptionArray()
    {
        $options = [
            self::YES => __('Yes'),
            self::NO => __('No')
        ];
        return $options;
    }
}
