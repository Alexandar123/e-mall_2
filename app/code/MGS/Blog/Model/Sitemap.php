<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Blog\Model;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;

/**
 * Sitemap model.
 *
 * @method string getSitemapType()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapType(string $value)
 * @method string getSitemapFilename()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapFilename(string $value)
 * @method string getSitemapPath()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapPath(string $value)
 * @method string getSitemapTime()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapTime(string $value)
 * @method int getStoreId()
 * @method \Magento\Sitemap\Model\Sitemap setStoreId(int $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * Item resolver
     *
     * @var ItemProviderInterface
     */
    protected $itemProvider;

    /**
     * Sitemap config reader
     *
     * @var SitemapConfigReaderInterface
     */
    protected $configReader;

    /**
     * Sitemap Item Factory
     *
     * @var \Magento\Sitemap\Model\SitemapItemInterfaceFactory
     */
    protected $sitemapItemFactory;

    /**
     * Last mode min timestamp value
     *
     * @var int
     */
    protected $lastModMinTsVal;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DocumentRoot
     */
    protected $documentRoot;

    protected $_post;

    protected $_category;


    /**
     * Initialize sitemap
     *
     * @return void
     */

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \MGS\Blog\Model\Post $post,
        \MGS\Blog\Model\Category $category,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null,
        \Magento\Sitemap\Model\ItemProvider\ItemProviderInterface $itemProvider = null,
        \Magento\Sitemap\Model\SitemapConfigReaderInterface $configReader = null,
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $sitemapItemFactory = null
    ) {
        $this->documentRoot = $documentRoot ?: ObjectManager::getInstance()->get(DocumentRoot::class);
        $this->itemProvider = $itemProvider ?: ObjectManager::getInstance()->get(ItemProviderInterface::class);
        $this->configReader = $configReader ?: ObjectManager::getInstance()->get(SitemapConfigReaderInterface::class);
        $this->sitemapItemFactory = $sitemapItemFactory ?: ObjectManager::getInstance()->get(
            \Magento\Sitemap\Model\SitemapItemInterfaceFactory::class
        );
        $this->_post = $post;
        $this->_category = $category;
        parent::__construct($context, $registry, $escaper, $sitemapData, $filesystem, $categoryFactory, $productFactory, $cmsFactory, $modelDate, $storeManager, $request, $dateTime, $resource, $resourceCollection, $data, $documentRoot, $itemProvider, $configReader, $sitemapItemFactory);
    }

    public function getAllPost() {
        $items = [];

        $postCollection = $this->_post->getCollection()
            ->addFieldToFilter('status', 1)
            ->setOrder('created_at', 'ASC');

		return $postCollection;
	}

    public function getAllCategoryPost() {
        $items = [];


        $categoryCollection = $this->_category->getCollection()
            ->addFieldToFilter('status', 1)
            ->setOrder('sort_order', 'ASC');



		return $categoryCollection;
	}


    protected function _initSitemapItems()
    {
        $sitemapItems = $this->itemProvider->getItems($this->getStoreId());
        $mappedItems = $this->mapToSitemapItem();

        $postCollection = $this->getAllPost();
        $categoryCollection = $this->getAllCategoryPost();

        $this->_sitemapItems = array_merge($sitemapItems, $mappedItems);

        $helper = $this->_sitemapData;

        $this->_sitemapItems[] = $this->sitemapItemFactory->create(
            [
                'url' => 'blog',
                'updatedAt' => $this->_getCurrentDateTime(),
                'images' => '',
                'priority' => $helper->getPagePriority($this->getStoreId()),
                'changeFrequency' => $helper->getPageChangefreq($this->getStoreId()),
            ]
        );

        foreach ($postCollection as $item) {
            $this->_sitemapItems[] = $this->sitemapItemFactory->create(
                [
                    'url' => 'blog/'.$item->getUrlKey(),
                    'updatedAt' => $item->getUpdatedAt(),
                    'images' => '',
                    'priority' => $helper->getPagePriority($this->getStoreId()),
                    'changeFrequency' => $helper->getPageChangefreq($this->getStoreId()),
                ]
            );
        }

        foreach ($categoryCollection as $item) {
            $this->_sitemapItems[] = $this->sitemapItemFactory->create(
                [
                    'url' => 'blog/'.$item->getUrlKey(),
                    'updatedAt' => '',
                    'images' => '',
                    'priority' => $helper->getPagePriority($this->getStoreId()),
                    'changeFrequency' => $helper->getPageChangefreq($this->getStoreId()),
                ]
            );
        }

        $this->_tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                    ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * Sitemap item mapper for backwards compatibility
     *
     * @return array
     */
    protected function mapToSitemapItem()
    {
        $items = [];

        foreach ($this->_sitemapItems as $data) {
            foreach ($data->getCollection() as $item) {
                $items[] = $this->sitemapItemFactory->create(
                    [
                        'url' => $item->getUrl(),
                        'updatedAt' => $item->getUpdatedAt(),
                        'images' => $item->getImages(),
                        'priority' => $data->getPriority(),
                        'changeFrequency' => $data->getChangeFrequency(),
                    ]
                );
            }
        }

        return $items;
    }
}
