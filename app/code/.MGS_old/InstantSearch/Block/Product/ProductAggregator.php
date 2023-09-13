<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\Product;

use \MGS\InstantSearch\Block\Product as ProductBlock;
use \Magento\Catalog\Helper\Product as CatalogProductHelper;
use \Magento\Catalog\Block\Product\ReviewRendererInterface;
use \Magento\Framework\Stdlib\StringUtils;
use \Magento\Framework\Url\Helper\Data as UrlHelper;
use \Magento\Framework\Data\Form\FormKey;

/**
 * ProductAggregator class for autocomplete data
 *
 * @method Product setProduct(\Magento\Catalog\Model\Product $product)
 */
class ProductAggregator extends \Magento\Framework\DataObject
{
    /**
     * @var \MGS\InstantSearch\Block\Product
     */
    protected $productBlock;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * Product constructor.
     *
     * @param CatalogProductHelper $catalogHelperProduct
     * @param StringUtils $string
     * @param UrlHelper $urlHelper
     * @param FormKey $formKey
     * @param ProductContext $context
     * @param array $data
     */
    public function __construct(
        ProductBlock $productBlock,
        CatalogProductHelper $catalogHelperProduct,
        StringUtils $string,
        UrlHelper $urlHelper,
        FormKey $formKey
    ) {
        $this->productBlock = $productBlock;
        $this->catalogHelperProduct = $catalogHelperProduct;
        $this->string = $string;
        $this->urlHelper = $urlHelper;
        $this->formKey = $formKey;
    }

    /**
     * Set product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Retrieve product name
     *
     * @return string
     */
    public function getName()
    {
        return html_entity_decode($this->product->getName());
    }

    /**
     * Retrieve product small image url
     *
     * @return bool|string
     */
    public function getSmallImage()
    {
        return $this->catalogHelperProduct->getSmallImageUrl($this->product);
    }

    /**
     * Retrieve product reviews rating html
     *
     * @return string
     */
    public function getReviewsRating()
    {
        return $this->productBlock->getReviewsSummaryHtml(
            $this->product,
            ReviewRendererInterface::SHORT_VIEW,
            true
        );
    }

    /**
     * Retrieve product short description
     *
     * @return string
     */
    public function getShortDescription()
    {
        $shortDescription = $this->product->getShortDescription();

        return $this->cropDescription($shortDescription);
    }
    
    /**
     * Crop description to 50 symbols
     *
     * @param string|html $data
     * @return string
     */
    protected function cropDescription($data)
    {
        if (!$data) {
            return '';
        }

        $data = strip_tags($data);
        $data = (strlen($data) > 50) ? $this->string->substr($data, 0, 50) . '...' : $data;

        return $data;
    }

    /**
     * Retrieve product price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->productBlock->getProductPrice($this->product, \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
    }

    /**
     * Retrieve product url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->productBlock->getProductUrl($this->product);
    }

    /**
     * Retrieve product add to cart data
     *
     * @return array
     */
    public function getAddToCartData()
    {
        $formUrl = $this->productBlock->getAddToCartUrl($this->product, ['mgs_instantsearch' => true]);
        $productId = $this->product->getEntityId();
        $paramNameUrlEncoded = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
        $urlEncoded = $this->urlHelper->getEncodedUrl($formUrl);
        $formKey = $this->formKey->getFormKey();

        $addToCartData = [
            'formUrl' => $formUrl,
            'productId' => $productId,
            'paramNameUrlEncoded' => $paramNameUrlEncoded,
            'urlEncoded' => $urlEncoded,
            'formKey' => $formKey
        ];

        return $addToCartData;
    }
}
