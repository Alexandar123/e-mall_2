<?php

namespace MGS\Blog\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class ImageType implements ArrayInterface
{
    const IMAGE = 'image';
    const VIDEO = 'video';

    public function toOptionArray()
    {
        $options = [
            self::IMAGE => __('Image'),
            self::VIDEO => __('Video')
        ];
        return $options;
    }
}
