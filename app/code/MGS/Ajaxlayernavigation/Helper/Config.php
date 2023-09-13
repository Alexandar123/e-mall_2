<?php
namespace MGS\Ajaxlayernavigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{

    protected $_priceCurrency;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    public function getPriceCurrencyPos()
    {
        $currency = $this->_priceCurrency->getCurrency();
        $symbol = $currency->getCurrencySymbol();
        $tmpPrice = $currency->format(0);
        if (strpos($symbol, $tmpPrice) > 0) {
            $prefix = '';
            $postfix = ' '.$symbol;
        } else {
            $prefix = $symbol;
            $postfix = '';
        }

        return ['prefix' => $prefix, 'postfix' => $postfix];
    }

    public function getClearAllUrl()
    {
        $searchQuery = $this->_getRequest()->getParam('q');
        $query = [
            '_' => null
        ];

        if ($this->iaAjaxEnable()) {
            $query['is_ajax'] = 1;
        }
        if ($searchQuery) {
            $query['q'] = $searchQuery;
        }

        return $this->_urlBuilder->getUrl('*/*/*',
            [
                '_use_rewrite' => true,
                '_query' => $query
            ]
        );
    }

	public function getActiveItems($count){
		if($this->defaultExpanded()){
			if($count>0){
				$arrActive = '[';
				for($i=0; $i<$count; $i++){
					$arrActive .= $i . ',';
				}
				$arrActive = substr($arrActive, 0, -1);
				$arrActive .= ']';
				return $arrActive;
			}
		}
		return 'false';
	}

	public function getFilterPriceButtonText(){
		return $this->scopeConfig->getValue(
            "mgs_ajaxnavigation/general/button_text", ScopeInterface::SCOPE_STORE);
	}

    public function iaAjaxEnable()
    {
        return $this->scopeConfig->getValue(
            "mgs_ajaxnavigation/general/enable", ScopeInterface::SCOPE_STORE);
    }

    public function usePriceSlide(){
        return $this->scopeConfig->getValue(
            "mgs_ajaxnavigation/general/range_price", ScopeInterface::SCOPE_STORE);
    }

	public function removeShoppingBy(){
		return $this->scopeConfig->getValue(
            "mgs_ajaxnavigation/general/shopping_by", ScopeInterface::SCOPE_STORE);
    }

    public function defaultExpanded(){
		return $this->scopeConfig->getValue(
            "mgs_ajaxnavigation/general/expanded_filter", ScopeInterface::SCOPE_STORE);
	}

	public function getFilterByText(){
		return $this->scopeConfig->getValue(
            "mgs_ajaxnavigation/general/filter_by", ScopeInterface::SCOPE_STORE);
	}
}
