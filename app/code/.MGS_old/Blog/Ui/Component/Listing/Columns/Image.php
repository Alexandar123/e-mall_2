<?php

namespace MGS\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use MGS\Blog\Model\Post as PostModel;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'image';
    const ALT_FIELD = 'name';
    protected $filesystem;
    protected $_storeManager;
    protected $postModel;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PostModel $postModel,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->postModel = $postModel;
    }

    public function prepareDataSource(array $dataSource)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $mediaFolder = 'mgs_blog/';
        $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['image'])) continue;
                if ($item['image']) {
                    $thumbnailUrl = $path . $item['image'];
                    $item[$fieldName . '_src'] = $thumbnailUrl;
                    $item[$fieldName . '_alt'] = $item['title'];
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'blog/post/edit',
                        ['post_id' => $item['post_id'], 'store' => $this->context->getRequestParam('store')]
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
