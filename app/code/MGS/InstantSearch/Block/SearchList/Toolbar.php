<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\SearchList;
use MGS\InstantSearch\Model\SearchList\Toolbar as ToolbarModel;
use MGS\InstantSearch\Helper\Data as InstantHelper;
/**
 * Product list toolbar
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Toolbar extends \Magento\Framework\View\Element\Template
{
    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * @var ToolbarModel
     */
    protected $_toolbarModel;

    /**
     * @var InstantHelper
     */
    protected $_inSearchHelper;
    private $currentType;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ToolbarModel $toolbarModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ToolbarModel $toolbarModel,
        InstantHelper $inSearchHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_toolbarModel = $toolbarModel;
        $this->_inSearchHelper = $inSearchHelper;
    }
    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = $this->getLimit();
        if ($limit && $limit != 'all') {
            $this->_collection->setPageSize($limit);
        }
        return $this;
    }

    /**
     * Return products collection instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Set view type to pager
     *
     * @param $viewType
     * @return $this
     */
    public function setCurrentType($viewType)
    {
        $this->currentType = $viewType;
        return $this;
    }

    /**
     * Retrieve current View type
     *
     * @return string
     */
    public function getCurrentType()
    {
        return $this->currentType;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_toolbarModel->getCurrentPage();
    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        return $this->_inSearchHelper->getDefaultLimitPerPageValue($this->getCurrentType());
    }
    /**
     * Retrieve available limits for current view mode
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        return $this->_inSearchHelper->getAvailableLimit($this->getCurrentType());
    }

    /**
     * Get specified products limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        $limit = $this->_getData('_current_limit');
        if ($limit) {
            return $limit;
        }

        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->_toolbarModel->getLimit();
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        $this->setData('_current_limit', $limit);
        return $limit;
    }

    /**
     * @param int $limit
     * @return bool
     */
    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    /**
     * @return int
     */
    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }

    /**
     * @return int
     */
    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }

    /**
     * @return int
     */
    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    /**
     * @return int
     */
    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Retrieve widget options in json format
     *
     * @param array $customOptions Optional parameter for passing custom selectors from template
     * @return string
     */
    public function getWidgetOptionsJson(array $customOptions = [])
    {
        $defaultType = $this->getCurrentType();
        $options = [
            'limit' => ToolbarModel::LIMIT_PARAM_NAME,
            'limitDefault' => $this->_inSearchHelper->getDefaultLimitPerPageValue($defaultType),
            'url' => $this->getPagerUrl(),
        ];
        $options = array_replace_recursive($options, $customOptions);
        return json_encode(['searchListToolbarForm' => $options]);
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('instance_search_list_toolbar');
        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Magento\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());
            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );

            return $pagerBlock->toHtml();
        }

        return '';
    }
}
