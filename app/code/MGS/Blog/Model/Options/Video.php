<?php 

namespace MGS\Blog\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class Video implements ArrayInterface {
    function toOptionArray()
    {
        $option = [
            "thumbnail_vimeo" => [
                'label'=> 'Vimeo',
                'value' => 'vimeo'
            ],
            "thumbnail_youtube" => [
                'label' => 'Youtube',
                'value' => 'youtube'
            ],
            ];
        return $option ;

    }
}