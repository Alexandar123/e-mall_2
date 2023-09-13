<?php 

namespace MGS\Testimonial\Model;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MGS\Testimonial\Model\ResourceModel\Testimonial\CollectionFactory;
use Magento\Framework\App\ObjectManager;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider {
    protected $collection;

    protected $loadedData;

    protected $store;

    protected $fileInfo;

    protected $resource;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $testimonialFactory,
        DataPersistorInterface $dataPersistor,
        \MGS\Testimonial\Model\ResourceModel\Testimonial $resource,
        \Magento\Framework\App\Request\Http $request,
		PoolInterface $pool = null,
		array $meta = [],
        array $data = []
    )
    {
        $this->collection = $testimonialFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->resource = $resource;
        $this->store= $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    public function getData() {
        if(isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems(); 
        foreach($items as $item ) {
            $item->setStores($this->store->getParam('store'));
            $item = $this->convertValues($item);
            $item = $item->getData();
            $store = $this->getStoreView($item['testimonial_id']);
            foreach($store as $key => $value) {
                $item['store_id'][] = (string)$key;
            }
            $item['store_id'][] = (string)1;
            if($this->store->getParam('store')){
                $data = $this->convertData($item);
                $this->loadedData[$item['testimonial_id']] = $data;
            }
            else 
                $this->loadedData[$item['testimonial_id']] = $item;
        }
        return $this->loadedData;
    }
    private function convertValues($banner)
    {
        $fileName = $banner->getAvatar();
        $this->getFileInfo()->getPath($fileName);
        $image = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $image[0]['name'] = $fileName;
            $image[0]['url'] = $this->getFileInfo()->getPath($fileName);
            $image[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $image[0]['type'] = $mime;
        }
        $banner->setAvatar($image);
        return $banner;
    }
    private function getFileInfo()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(FileInfo::class);
        }
        return $this->fileInfo;
    }

    private function convertData($data) {
        foreach ($data as $key => $value) {
            if ($key === 'testimonial_id' || $key === 'store_id' || $key === 'avatar') {
                continue;
            }
        $update = $this->getValueUpdate($data['testimonial_id'], $key);
            if($update) {
                $data[$key] = $update['value'];
            }
        }
        return $data;
    }

    public function getMeta() {
        $meta = parent::getMeta();
        $store = $this->store->getParam('store');
        $id = $this->store->getParam('id');
        $default = ['name', 'information', 'status', 'content'];
        $update = $this->getUpdateFile($id, $store);
        if($store) {
            $meta['general']['children']['avatar']['arguments']['data']['config']['disabled'] = 1;
            $meta['general']['children']['store_id']['arguments']['data']['config']['disabled'] = 1;
            foreach($update as $key => $value) {
                $position = array_search($key, $default);
                unset($default[$position]);
                $meta['general']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                $meta['general']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
            }
            foreach ($default as $key => $value) {
                $meta['general']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                $meta['general']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
            }
        }
        return $meta; 
    }
    function getStoreView($testimonial_id) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_testimonial_store'),
            'store_id',
        )->where(
            'testimonial_id = ?',
            (int)$testimonial_id
        );
        return $connection->fetchAssoc($select);
    }
    public function getUpdateFile($id, $store) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
                  $this->resource->getTable('mgs_testimonial_update'),
                  'field'
                  )-> where(
                     'testimonial_id = ?',
                     (int) $id
                  )->where(
                      'scope_id = ?',
                      $store
                  );
        return $connection->fetchAssoc($select);
    }

    public function getValueUpdate($id, $field ) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
                  $this->resource->getTable('mgs_testimonial_update'),
                    'value',
                )->where(
                    'testimonial_id = ?',
                    (int)$id
                )->where(
                    'scope_id = ?',
                    (int) $this->store->getParam('store')
                )->where(
                    'field = ?',
                    $field
                );
        return $connection->fetchRow($select);
    }
}