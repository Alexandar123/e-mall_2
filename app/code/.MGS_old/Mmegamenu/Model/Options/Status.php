<?php 

namespace MGS\Mmegamenu\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface {
    function toOptionArray() {
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