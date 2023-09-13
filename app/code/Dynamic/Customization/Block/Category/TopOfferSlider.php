<?php

namespace Dynamic\Customization\Block\Category;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\App\ActionInterface;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;

class TopOfferSlider extends Template 
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Block\Product\Context $productcontext,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Helper\Cart $cartHelper,
        Data $urlHelper,
        Context $context,
        array $data = [],
        ?OutputHelper $outputHelper = null
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_imageHelper = $productcontext->getImageHelper();
        $this->imageBuilder = $productcontext->getImageBuilder();
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->_storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->_cartHelper = $cartHelper;
        $data['outputHelper'] = $outputHelper ?? ObjectManager::getInstance()->get(OutputHelper::class);
        parent::__construct($context, $data);
    }
    public function getProductCollection()
    {
        $ids = 4; 
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => 4]);
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        // echo "<pre>";

        // print_r($collection->getData());
        // exit;
        return $collection;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }
    public function getProductImageUrl($productId)
    {
        $prdId = $productId;
        $product = $this->productRepositoryInterface->getById($prdId);
        $store = $this->_storeManager->getStore();
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        return $productImageUrl;
    }
    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return $this->getProductPriceHtml(
            $product,
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
        );
    }
    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render($priceType, $product, $arguments);
        }
        return $price;
    }
        /**
     * Get post parameters
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product, ['_escape' => false]);
        return [
            'action' => $url,
            'data' => [
                'product' => (int) $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * Retrieve url for direct adding product to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

}
