<?php 

namespace MGS\Mmegamenu\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class Columns implements ArrayInterface {
    function toOptionArray() {
        $options = [
            '1-columns' => [
                'label' => '1',
                'value' => 1
            ],
            '2-columns' => [
                'label' => '2',
                'value' => 2
            ],
            '3-columns' => [
                'label' => '3',
                'value' => 3
            ],
            '4-columns' => [
                'label' => '4',
                'value' => 4
            ],
            '6-columns' => [
                'label' => '6',
                'value' => 6
            ]
        ];
        return $options;
    }
}