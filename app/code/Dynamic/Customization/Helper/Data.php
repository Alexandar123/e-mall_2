<?php

/**
 * Banners data helper
 */

namespace Dynamic\Customization\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MEDIA_PATH    = 'attribute/swatch';
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Store manager
     *
     * @var \Magento\Swatches\Model\ResourceModel\Swatch\Collection
     */
    protected $swatchCollection;

    /**
     * Category manager
     *
     * @var Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * Category manager
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider $filterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    protected $imageFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Swatches\Model\ResourceModel\Swatch\Collection $swatchCollection,
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\CatalogRule\Model\ResourceModel\Rule $catalogrule,
        \Magento\Customer\Model\Session $customersession
    ) {
        $this->customersession = $customersession;
        $this->catalogrule = $catalogrule;
        $this->dateTime = $dateTime;
        $this->productFactory = $productFactory;
        $this->priceCurrency = $priceCurrency;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->swatchCollection = $swatchCollection;
        $this->category = $category;
        $this->product = $product;
        $this->registry = $registry;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->filterProvider = $filterProvider;
        $this->stockRegistryInterface = $stockRegistryInterface;
        parent::__construct($context);
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . '/' . self::MEDIA_PATH;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getProductLabel($labelId)
    {
        $swatchItems = $this->swatchCollection->addFilterByOptionsIds([$labelId]);
        if ($swatchItems->getData()) {
            foreach ($swatchItems->getData() as $swatchItem) {
                if ($swatchItem['type'] == 2)
                    return $this->getBaseUrl() . $swatchItem['value'];
            }
        }
        return;
    }

    public function getDesignerInfoById($designer_id)
    {
        return $this->category->load($designer_id);
    }

    public function getProductLabelById($id)
    {
        $product = $this->product->load($id);
        return $product->getProductLabel();
    }

    public function getProductRowtotalRegularPrice($sku, $itemPrice, $qty)
    {
        $product = $this->product->loadByAttribute('sku', $sku);
        if ($product) {
            $originalPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
            $originalPrice = $originalPrice * $qty;
            if ($itemPrice != $originalPrice && $itemPrice < $originalPrice) {
                return $originalPrice;
            }
        }
        return 0;
    }

    /**
     * Extract images for carousel slider from ImagesJson
     *
     * @param string $dataJson
     * @return array
     */
    public function extractCarouselImages($photos)
    {
        $photoItems = [];
        if ($photos) {
            foreach ($photos->getItems() as $photo) {
                $images = $photo->getImages();
                $photoItems[] = [
                    'small' => isset($images['small']) ? $images['small'] : '',
                    'caption' => $this->getContent($photo->getPhotoDescription())
                ];
            }
        }
        return $photoItems;
    }

    /**
     * Extract images for carousel slider from ImagesJson
     *
     * @param string $dataJson
     * @return array
     */
    public function getImagesJson($photos)
    {
        $photoItems = [];
        if ($photos) {
            foreach ($photos->getItems() as $photo) {
                $images = $photo->getImages();
                $photoItems[] = [
                    'thumb' => isset($images['thumbnail']) ? $images['thumbnail'] : '',
                    'small' => isset($images['small']) ? $images['small'] : '',
                    'img' => isset($images['base']) ? $images['base'] : '',
                    'full' => isset($images['full']) ? $images['full'] : '',
                    'caption' => '',
                    'position' => $photo->getPosition(),
                    'isMain' => false,
                    'type' => 'image',
                    'videoUrl' => null,
                ];
            }
        }
        return json_encode($photoItems);
    }

    public function getContent($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
    public function getCurrentProductQty()
    {
        return $this->stockRegistryInterface;
    }

    public function resize($image, $width = null, $height = null, $aspectratio = null)
    {
        if (strpos($image, "/magento73/lifeshopping/pub/") !== false) {
            $image = str_replace("/magento73/lifeshopping/pub/", "", $image);
        }
        $absolutePath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::PUB)->getAbsolutePath() . $image;
        $imageResized = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/' . $width . '/') . $image;
        //create image factory...
        $imageResize = $this->imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(true);
        $imageResize->keepAspectRatio($aspectratio);
        $imageResize->backgroundColor(array(255, 255, 255));
        $imageResize->resize($width, $height);
        //destination folder                
        $destination = $imageResized;
        //save image      
        $imageResize->save($destination);


        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/' . $width . '/' . $image;
        return $resizedURL;
    }
    public function getCurrencyWithFormat($price)
    {
        return $this->priceCurrency->format($price, true, 2);
    }
    public function getCustomerid()
    {
        return $this->customersession;
    }
    public function getCatalogPriceRuleFromProduct($productId, $customerGroupId)
    {
        /**
         * @var \Magento\Catalog\Model\ProductFactory
         */
        $product = $this->productFactory->create()->load($productId);

        $storeId = $product->getStoreId();
        $store = $this->_storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();
        /**
         * @var \Magento\Framework\Stdlib\DateTime\DateTime
         */
        $date = $this->dateTime;
        $dateTs = $date->gmtDate();

        /**
         * @var \Magento\CatalogRule\Model\ResourceModel\Rule
         */
        $resource = $this->catalogrule;

        $rules = $resource->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);

        return $rules;
    }
}
