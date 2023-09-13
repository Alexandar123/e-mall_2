<?php
namespace Dynamic\Postcode\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const POSTCODE_XML_PATH = 'postcode/settings/postcode';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ){
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }
    public function getConfig($path){
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
    }

    public function getPostcodeOptions(){
        $poscodes = $this->getConfig(self::POSTCODE_XML_PATH);
        $poscodes = explode(',', $poscodes);
        $options = [
            '1' => [
                'value'=>'',
                'label'=>__('Please select a postcode.'),
            ],
        ];
        $i = 2;
        foreach($poscodes as $value){
            $options[$i] =[
                    'value' => $value,
                    'label' => $value,
                ];
            $i++;
        }
        
        return $options;
    }
}
