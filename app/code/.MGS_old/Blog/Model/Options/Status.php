<?php 

namespace MGS\Blog\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface {

    public function toOptionArray()
    {
        $options = [
            1 => [
                'label' => 'Enable',
                'value' => 1
            ],
            0 => [
                'label' => 'Disable',
                'value' => 0
            ],
        ];

        return $options;
    }

}