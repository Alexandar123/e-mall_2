<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 */

namespace MGS\OSCheckout\Helper;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;
use Magento\Catalog\Block\Product\View\Options;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Downloadable\Block\Checkout\Cart\Item\Renderer;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout\BuilderFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Item
 * @package MGS\OSCheckout\Helper
 */
class Item extends Data
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Image
     */
    private $catalogHelper;

    /**
     * @var ConfigInterface
     */
    private $viewConfig;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * Item constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param LayoutFactory $layoutFactory
     * @param BuilderFactory $builderFactory
     * @param Registry $registry
     * @param Image $catalogHelper
     * @param ConfigInterface $viewConfig
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        LayoutFactory $layoutFactory,
        BuilderFactory $builderFactory,
        Registry $registry,
        Image $catalogHelper,
        ConfigInterface $viewConfig,
        Repository $repository
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->builderFactory = $builderFactory;
        $this->registry = $registry;
        $this->catalogHelper = $catalogHelper;
        $this->viewConfig = $viewConfig;
        $this->repository = $repository;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param Quote $quote
     * @param QuoteItem|int|bool $item
     *
     * @return array
     */
    public function getItemOptionsConfig($quote, $item)
    {
        $result = [];

        if (!is_object($item)) {
            $item = $quote->getItemById($item);
        }

        $product = $item->getProduct();

        $product->setPreconfiguredValues(
            $product->processBuyRequest($item->getBuyRequest())
        );

        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);

        if ($product->getOptions()) {
            /** @var Options $options */
            $options = $this->getLayout()->getBlock('mpOSCheckout.product.options');
            $options->setProduct($product);

            $result['customOptions'] = [
                'template' => $options->toHtml(),
                'optionConfig' => $options->getJsonConfig()
            ];
        }

        switch ($item->getProductType()) {
            case 'configurable':
                $result['configurableAttributes'] = $this->getConfigurableConfig($item, $product);
                break;
            case 'bundle':
                $result['customOptions'] = $this->getBundleConfig($item);
                break;
            case 'downloadable':
                $result['customOptions'] = $this->getDownloadableConfig($item);
                break;
            case 'giftcard':
                $result['customOptions'] = $this->getGiftCardConfig($item);
                break;
        }

        return $result;
    }

    /**
     * @return LayoutInterface
     */
    protected function getLayout()
    {
        if ($this->layout === null) {
            $layout = $this->layoutFactory->create();

            $this->builderFactory->create(BuilderFactory::TYPE_LAYOUT, ['layout' => $layout]);

            $layout->getUpdate()->addHandle(['default', 'onestepcheckout_product_config']);

            /** @var AbstractBlock $block */
            foreach ($layout->getAllBlocks() as $block) {
                $block->setData('area', Area::AREA_FRONTEND);
            }

            $this->layout = $layout;
        }

        return $this->layout;
    }

    /**
     * @param QuoteItem $item
     * @param Product $product
     *
     * @return array
     */
    private function getConfigurableConfig($item, $product)
    {
        /** @var Configurable $block */
        $block = $this->getLayout()->getBlock('mpOSCheckout.configurable.options');
        //$block->unsetData('allow_products');
        $block->addData([
            'product' => $product,
            'quote_item' => $item
        ]);

        return [
            'template' => $block->toHtml(),
            'spConfig' => $block->getJsonConfig(),
        ];
    }

    /**
     * @param QuoteItem $item
     *
     * @return array
     */
    private function getBundleConfig($item)
    {
        /** @var Bundle $block */
        $block = $this->getLayout()->getBlock('mpOSCheckout.bundle.options');
        $block->setData([
            'product' => $item->getProduct(),
            'item' => $item
        ]);
        $block->getOptions(true);

        return [
            'template' => $block->toHtml(),
            'optionConfig' => $block->getJsonConfig()
        ];
    }

    /**
     * @param QuoteItem $item
     *
     * @return array
     */
    private function getDownloadableConfig($item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('mpOSCheckout.downloadable.options');
        $block->setData([
            'product' => $item->getProduct(),
            'item' => $item
        ]);

        return [
            'template' => $item->getProduct()->getLinksPurchasedSeparately() ? $block->toHtml() : '',
            'optionConfig' => null
        ];
    }

    /**
     * @param QuoteItem $item
     *
     * @return array
     */
    private function getGiftCardConfig($item)
    {
        /** @var Giftcard $block */
        if (!$block = $this->getLayout()->getBlock('mpOSCheckout.giftcard.options')) {
            $block = $this->getLayout()->createBlock(
                Giftcard::class,
                'mpOSCheckout.giftcard.options'
            );
        }

        $block->setTemplate('MGS_OSCheckout::product/view/type/options/giftcard.phtml');
        $block->setData([
            'product' => $item->getProduct(),
            'item' => $item
        ]);

        return [
            'template' => $block->toHtml(),
            'optionConfig' => null
        ];
    }

    /**
     * @param $item
     *
     * @return array
     * @throws LocalizedException
     */
    public function getItemImages($item)
    {
        if ($this->versionCompare('2.3.0')) {
            /** @var ItemResolverInterface $itemResolver */
            $itemResolver = $this->getObject(ItemResolverInterface::class);

            /** @var Product $finalProduct */
            $finalProduct = $itemResolver->getFinalProduct($item);
        } else {
            $finalProduct = $item->getProduct();
        }

        $mediaId = 'mini_cart_product_thumbnail';
        $attributes = $this->viewConfig
            ->getViewConfig(['area' => Area::AREA_FRONTEND])
            ->getMediaAttributes('Magento_Catalog', Image::MEDIA_TYPE_CONFIG_NODE, $mediaId);

        if (empty($attributes)) {
            $attributes = [
                'type' => 'thumbnail',
                'width' => 75,
                'height' => 75,
            ];
        }

        $image = $this->catalogHelper->init($finalProduct, $mediaId, $attributes);

        $url = $image->getUrl();

        if (strpos($url, 'webapi_rest') !== false) {
            $asset = $this->repository->createAsset($image->getPlaceholder(), ['area' => Area::AREA_FRONTEND]);
            $url = $asset->getUrl();
        }

        return [
            'src' => $url,
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'alt' => $image->getLabel()
        ];
    }
}
