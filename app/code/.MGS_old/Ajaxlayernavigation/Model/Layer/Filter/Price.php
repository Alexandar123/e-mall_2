<?php
namespace MGS\Ajaxlayernavigation\Model\Layer\Filter; 

class Price extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    protected $appliyedFilter;

    protected $filterPlus;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \MGS\Ajaxlayernavigation\Helper\Config $helper,
        array $data = []
    ) {
        $this->_requestVar = 'price';
        $this->appliedFilter = [];
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemBuilder, $data);
    }

    public function getMinPrice()
    {
        $productCollection = $this->getLayer()->getProductCollection();
        return $productCollection->getMinPrice();
    }

    public function getMaxPrice()
    {
        $productCollection = $this->getLayer()->getProductCollection();
        return $productCollection->getMaxPrice();
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $priceFilter = $request->getParam(
            $this->getRequestVar()
        );

        if (!$priceFilter || is_array($priceFilter)) {
            return $this;
        }
        if (!$this->filterPlus) {
            $this->filterPlus = true;
        }
        $this->appliedFilter = $priceFilter;
        $priceFromTo = explode('-', $priceFilter);
        $from = $priceFromTo[0];
        $to = $priceFromTo[1];

        $this->getLayer()->getProductCollection()->addFieldToFilter(
            'price',
            ['from' => $from, 'to' =>  empty($to) || $from == $to ? $to : $to]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem(
                $this->_renderPriceLabel(empty($from) ? 0 : $from,$to), $priceFilter)
        );

        return $this;
    }

    protected function _renderPriceLabel($fromPrice, $toPrice)
    {   
        if($this->helper->usePriceSlide()){
            return __('%1 - %2', $this->priceCurrency->format($fromPrice), $this->priceCurrency->format($toPrice));
        }
        $fromPrice = empty($fromPrice) ? 0 : $fromPrice;
        $toPrice = empty($toPrice) ? $toPrice : $toPrice;

        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }

            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    protected function _getItemsData()
    {   
        if($this->helper->usePriceSlide()){
            return [1];
        }

        $attribute = $this->getAttributeModel(); 

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $facets = $productCollection->getFacetedData($attribute->getAttributeCode());

        $activeFilters = [];
        if($this->appliedFilter) {
            $activeFilters = explode(',', $this->appliedFilter);
        }
        if (count($facets) > 1) { // two range minimum
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }
                $key = str_replace('_', '-', $key);
                $key = str_replace('*', '', $key);
                $fromTo = explode('-', $key);
                $label = $this->_renderPriceLabel($fromTo[0] , $fromTo[1]);
                $active = in_array($key, $activeFilters);
                 $this->_itemBuilder->addItemData(
                        $label,
                        $key,
                        $count,
                        $active,
                        $this->filterPlus
                    );
            }
        } 
        return $this->_itemBuilder->build();
    }

    public function isActive()
    {
        return $this->filterPlus;
    }
}
