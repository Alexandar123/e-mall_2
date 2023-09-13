<?php 

namespace MGS\Mmegamenu\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class LeftCol implements ArrayInterface {
    function toOptionArray() {
        $options = [
            'disable-leftcol' => [
                'label' => 'Disable',
                'value' => '0'
            ],
            '1-leftcol' => [
                'label' => '1',
                'value' => '1'
            ],
            '2-leftcol' => [
                'label' => '2',
                'value' => '2'
            ],
            '3-leftcol' => [
                'label' => '3',
                'value' => '3'
            ],
            '4-leftcol' => [
                'label' => '4',
                'value' => '4'
            ]
        ];
        return $options;
    }
}