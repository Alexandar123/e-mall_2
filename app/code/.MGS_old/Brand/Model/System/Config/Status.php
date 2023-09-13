<?php

namespace MGS\Brand\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    const ENABLED = 1;
    const DISABLED = 2;

    public function toOptionArray()
    {
        $options = [
            self::ENABLED => __('Enabled'),
            self::DISABLED => __('Disabled')
        ];
        return $options;
    }

    public static function getAvailableStatuses()
    {
        return [
            self::ENABLED => __('Enabled')
            , self::DISABLED => __('Disabled'),
        ];
    }
}
