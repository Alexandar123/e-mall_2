<?php


namespace MGS\Portfolio\Model;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MGS\Portfolio\Model\Portfolio\FileInfo;
use MGS\Portfolio\Model\ResourceModel\Portfolio\CollectionFactory;
use MGS\Portfolio\Model\ResourceModel\Category;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    protected $collection;

    protected $loadedData;

    protected $store;

    protected $_fileInfo;

    protected $resource;

    protected $_storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \MGS\Portfolio\Model\ResourceModel\Portfolio\CollectionFactory $portfolioCollectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FileInfo $fileInfo,
        \MGS\Portfolio\Model\ResourceModel\Category $resource,
		PoolInterface $pool = null,
		array $meta = [],
        array $data = []
    )
    {
        $this->collection = $portfolioCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager = $storeManager;
        $this->_fileInfo = $fileInfo;
        $this->store= $request;
        $this->resource = $resource;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $tempId ='';
        $tempData='';
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $portfolio) {
            $tempId =$portfolio->getId();
            $portfolio->setStoreId($this->store->getParam('store'));
            if (isset($portfolio['base_image']) && $portfolio['base_image']!= null) {
                $portfolio= $this-> convertValues($portfolio);
            }
            if (isset($portfolio['thumbnail_image']) && $portfolio['thumbnail_image']!= null) {
                $portfolio = $this->convertThumbnail($portfolio);
            }
            $portfolio['category_id'] = $this->getCategory($tempId);
            $data = $portfolio->getData();
            $tempData = $data;
            $this->loadedData[$portfolio->getId()] = $data;
        }

        if ($this->store->getParam('store')) {
            $connection = $this->resource->getConnection();
            foreach ($tempData as $key => $value) {
                if ($key === 'portfolio_id' || $key === "scope_id") {
                    continue;
                } else {
                    $select = $connection->select()->from(
                        $this->resource->getTable('mgs_portfolio_items_update'),
                        'value',
                    )->where(
                        'portfolio_id = ?',
                        (int)$tempId
                    )->where(
                        'scope_id = ?',
                        (int) $this->store->getParam('store')
                    )->where(
                        'field = ?',
                        $key
                    );
                    $portfolio = $connection->fetchRow($select);
                    if ($portfolio) {
                        $this->loadedData[$tempId][$key] = $portfolio['value'];
                    }
                }
            }
        }
        return $this->loadedData;
    }


    private function convertValues($portfolio)
    {
        $fileName = $portfolio->getBaseImage();
        $temp =  strpos($fileName,'mgs_portfolio');
        if($temp !== false)
            $fileName = substr($fileName,$temp + 8);
        $image = [];
        $baseImage = explode(' ', $fileName);

        $image = [];

       for ( $i = 0; $i < count($baseImage); $i++) {

            if ($this->getFileInfo()->isExist($baseImage[$i])) {
                $stat = $this->getFileInfo()->getStat($baseImage[$i]);
                $mime = $this->getFileInfo()->getMimeType($baseImage[$i]);
                $image[$i]['name'] = $baseImage[$i];
                $image[$i]['url'] = $this->_storeManager->getStore()->getBaseUrl((\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)).'mgs_portfolio/'.$baseImage[$i];
                $image[$i]['size'] = isset($stat) ? $stat['size'] : 0;
                $image[$i]['type'] = $mime;
            }
        }

        $portfolio->setBaseImage($image);
        return $portfolio;
    }

    private function convertThumbnail($data)
    {
        $fileName = $data->getThumbnailImage();
        $temp =  strpos($fileName,'mgs_portfolio');
        if($temp !== false)
            $fileName = substr($fileName,$temp + 8);
        $image = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $image[0]['name'] = $fileName;
            $image[0]['url'] = $this->_storeManager->getStore()->getBaseUrl((\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)).'mgs_portfolio/'.$fileName;
            $image[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $image[0]['type'] = $mime;
        }
        $data->setThumbnailImage($image);
        return $data;
    }

    public function getCategory($id){
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_portfolio_category_items'),
            'category_id'
        )->where(
            'portfolio_id = ?',
            (int)$id
        );
        return  $connection->fetchCol($select);
    }

    private function getFileInfo()
    {
        if ($this->_fileInfo === null) {
            $this->_fileInfo = ObjectManager::getInstance()->get(\MGS\Portfolio\Model\Portfolio\FileInfo::class);
        }
        return $this->_fileInfo;
    }

    public function getMeta() {
        $meta = parent::getMeta();
        $store =  $this->store->getParam('store');
        $portfolio_id = $this->store->getParam('id');
        $ob = ['name','client','services','project_url','portfolio_date','skills','status','description'];
        $portfolio = $this->getFieldUpdate($portfolio_id,$store);
        if ($store) {
            foreach($portfolio as $key => $value) {
                $id = array_search($key, $ob);
                unset($ob[$id]);
                $meta['general']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                $meta['general']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
            }
            foreach ($ob as $key => $value) {
                $meta['general']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                $meta['general']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
            }
            $meta['general']['children']['identifier']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['category_id']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['thumbnail_image']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['base_image']['arguments']['data']['config']['disabled'] = 1;
        }
        return $meta;
    }

    public function getFieldUpdate($category_id,$store) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_portfolio_items_update'),
            'field',
        )->where(
            'portfolio_id = ?',
            (int)$category_id
        )->where(
            'scope_id = ?',
            $store
        );
        return $connection->fetchAssoc($select);
    }
}
