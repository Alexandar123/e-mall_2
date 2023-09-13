<?php
namespace MGS\Ajaxlayernavigation\Model\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \MGS\Ajaxlayernavigation\Helper\Config $configHelper,
        array $data = []
    ) {
        $this->_url = $url;
        $this->_configHelper = $configHelper;
        $this->_htmlPagerBlock = $htmlPagerBlock;
        parent::__construct($url, $htmlPagerBlock, $data);
    }

    public function getUrl()
    {
        $filter = $this->getFilter();
        $filterUrlValue = $this->getValue();
        if ($filter->appliedFilter) {
            $filterUrlValue = $filter->appliedFilter . ',' . $this->getValue();
        }

        $query = [
            $filter->getRequestVar() => $filterUrlValue,
            // exclude current page from urls
            $this->_htmlPagerBlock->getPageVarName() => null,
            '_' => null
        ];

        $itemUrl = $this->_url->getUrl(
            '*/*/*', [
                '_current' => true,
                '_use_rewrite' => true,
                '_escape' => true,
                '_query' => $query
            ]
        );
        return urldecode($itemUrl);
    }

    public function getRemoveUrl()
    {
        $filter = $this->getFilter();
        $filterUrlValue = $this->getValue();
        $query = [];
        if ($filter->appliedFilter) {
            $activeFilters = explode(',', $filter->appliedFilter);
            foreach ($activeFilters as $activeFilter) {
                if($filterUrlValue != $activeFilter) {
                    $query[] = $activeFilter;
                }
            }
        }
        $removeValue = null;
        if(count($query) > 0) {
            $removeValue = implode(',', $query);
        }
        $query = [
            $this->getFilter()->getRequestVar() => $removeValue,
            '_' => null
        ];

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;
        $removeUrl = $this->_url->getUrl('*/*/*', $params);
        return urldecode($removeUrl);
    }

    public function getValueString()
    {
        $value = $this->getFilter()->appliedFilter;

        if (is_array($value)) {
            return implode(',', $value);
        }
        return $value;
    }
}
