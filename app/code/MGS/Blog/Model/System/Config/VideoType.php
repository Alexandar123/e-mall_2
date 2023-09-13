<?php

namespace MGS\Blog\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class VideoType implements ArrayInterface
{
    const YOUTOBE = 'youtube';
    const VIMEO = 'vimeo';

    public function toOptionArray()
    {
        $options = [
            self::YOUTOBE => __('Youtube'),
            self::VIMEO => __('Vimeo')
        ];
        return $options;
    }
}
