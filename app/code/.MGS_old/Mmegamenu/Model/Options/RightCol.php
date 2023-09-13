<?php 

namespace MGS\Mmegamenu\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class RightCol implements ArrayInterface {
    function toOptionArray() {
        $options = [
            'disable-rightcol' => [
                'label' => 'Disable',
                'value' => '0'
            ],
            '1-rightcol' => [
                'label' => '1',
                'value' => '1'
            ],
            '2-rightcol' => [
                'label' => '2',
                'value' => '2'
            ],
            '3-rightcol' => [
                'label' => '3',
                'value' => '3'
            ],
            '4-rightcol' => [
                'label' => '4',
                'value' => '4'
            ]
        ];
        return $options;
    }
}