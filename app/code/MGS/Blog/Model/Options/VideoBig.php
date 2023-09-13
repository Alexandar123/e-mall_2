<?php 

namespace MGS\Blog\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class VideoBig implements ArrayInterface {
    function toOptionArray()
    {
        $option = [
            'videobig-vime' => [
                'label'=> 'Vimeo',
                'value' => 'vimeo'
            ],
            'videobig-youtube' => [
                'label' => 'Youtube',
                'value' => 'youtube'
            ],
            ];
        return $option ;
    }
}