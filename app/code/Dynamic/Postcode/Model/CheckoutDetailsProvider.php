<?php
namespace Dynamic\Postcode\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class SampleConfigProvider
 */
class CheckoutDetailsProvider implements ConfigProviderInterface
{
    protected $helper;
    public function __construct(
        \Dynamic\Postcode\Helper\Data $helper    
        ){
        $this->helper = $helper; 
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    { 
        return [
            'postcodeData' => [
                'postOpt' => $this->helper->getPostcodeOptions()
            ],
        ];
    }
}