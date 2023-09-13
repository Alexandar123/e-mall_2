<?php

namespace MGS\Brand\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use MGS\Brand\Model\Brand as BrandModel;

class Smallimage extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'small_image';
    const ALT_FIELD = 'name';
    protected $filesystem;
    protected $_storeManager;
    protected $brandModel;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BrandModel $brandModel,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->brandModel = $brandModel;
    }

    public function prepareDataSource(array $dataSource)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $mediaFolder = 'mgs_brand/';
        $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['small_image'])) continue;
                if ($item['small_image']) {
                    $thumbnailUrl = $path . $item['small_image'];
                    $item[$fieldName . '_src'] = $thumbnailUrl;
                    $item[$fieldName . '_alt'] = $item['name'];
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'brand/brand/edit',
                        ['brand_id' => $item['brand_id'], 'store' => $this->context->getRequestParam('store')]
                    );
                    $item[$fieldName . '_orig_src'] = $thumbnailUrl;
                }
            }
        }
        return $dataSource;
    }

    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
