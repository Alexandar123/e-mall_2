<?php 

namespace MGS\Testimonial\Model\Options;

class Status implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray() {
        $option = [
            0 => [
                'label' => "Enable",
                'value' => "1"
            ],
            1 => [
                'label' => 'Disable',
                'value' => '0'
            ]
        ];
    return $option; 
    }
}
