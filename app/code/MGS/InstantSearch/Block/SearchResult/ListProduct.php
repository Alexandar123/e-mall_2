<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\SearchResult;
use Magento\Framework\DataObject\IdentityInterface;
use MGS\InstantSearch\Helper\Data;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Search\Model\QueryFactory;

/**
 * Search result block
 */
class ListProduct extends \Magento\Catalog\Block\Product\AbstractProduct implements IdentityInterface
{
    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $_layerResolver;

    /**
     * @var ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    private $currentType = 'product';
    private $limit = 12;
    /**
     * @param Context $context
     * @param Data $inSearchHelper
     * @param Resolver $layerResolver
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Data $inSearchHelper,
        Resolver $layerResolver,
        QueryFactory $queryFactory,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->_imageBuilder = $context->getImageBuilder();
        $this->_inSearchHelper = $inSearchHelper;
        $this->_layerResolver = $layerResolver;
        $this->urlHelper = $urlHelper;
        $this->_queryFactory = $queryFactory;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_layerResolver->get();
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function _getProductCollection()
    {
        if(null === $this->_productCollection){
            $layer = $this->getLayer();
            $limit = $this->getPositionLimit() ? $this->getPositionLimit() : $this->limit;
            $productCollection = $layer->getProductCollection();
            $this->_productCollection = $productCollection;
        }
        return $this->_productCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->_getProductCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }

    /**
     * Get product image
     *
     * @return string
     */
    public function getProductImage(\Magento\Catalog\Model\Product $product, $imageDisplayArea)
    {
        if ($product) {
            return $this->_imageBuilder->setProduct($product)
                ->setImageId($imageDisplayArea)
                ->create();
        }
        return '';
    }

    /*
     * 
     * show short description product search result
     * @return string
     */
    public function showShortDescription()
    {
        return $this->_inSearchHelper->showShortDescriptionProductSearch();

    }

    /*
     * 
     * show review product search result
     * @return string
     */
    public function showReview()
    {
        return $this->_inSearchHelper->showReviewProductSearch();
    }

    /*
     * return number result
     * @return string
     */
    public function getPositionLimit()
    {
        return $this->getDefaultPerPageValue();

    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        return $this->_inSearchHelper->getDefaultLimitPerPageValue($this->currentType);
    }

    /*
     * @return string
     */
    public function getViewMoreLabel()
    {
        return $this->_inSearchHelper->getViewMoreLabel();
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl()
    {
        $query = $this->_queryFactory->get()->getQueryText();
        $url = 'catalogsearch/result';
        return $this->_inSearchHelper->getResultUrl($url,$query);
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_getProductCollection()->load();
        return parent::_beforeToHtml();
    }
}