<?php
namespace Dynamic\Postcode\Block\Checkout;

class PostcodeDataProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!isset($jsLayout['components']['checkoutProvider']['dictionaries']['postcode'])) {
            $jsLayout['components']['checkoutProvider']['dictionaries']['postcode'] = $this->getCityOptions();
        }
        
        return $jsLayout;
    }

    /**
     * Get country options list.
     *
     * @return array
     */
    private function getCityOptions()
    {
        //Add your city list here
        $options = [
            '1' => [
                'value'=>'city1',
                'name'=>'city1',
                'region_id'=>'1',//From database
            ],
            '2' => [
                'value'=>'city2',
                'name'=>'city2',
                'region_id'=>'2',//From databse
            ]
        ];

        if (count($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please select a postcode.')]
            );
        }
        // print_r($options);die;

        return $options;
    }
}