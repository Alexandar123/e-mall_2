<?php 

namespace MGS\Blog\Model\Category;

use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MGS\Blog\Model\Resource\Category\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider {
    protected $collection;

    protected $store;

    protected $loadedData;

    protected $scope;

    protected $resource;
    public function __construct(
		$name,
		$primaryFieldName,
        $requestFieldName,
        CollectionFactory $postFactory,
        DataPersistorInterface $dataPersistor,
        \MGS\Blog\Model\Resource\Category $resource,
        \Magento\Framework\App\Request\Http $request,
        ScopeConfigInterface $store,
		PoolInterface $pool = null,
		array $meta = [],
        array $data = []
    )
    {
        $this->collection = $postFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->store= $request;
        $this->scope = $store;
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
        foreach ($items as $categorys) {
            $categorys->setStoreId($this->store->getParam('store'));
            $data = $categorys->getData();
            $convert = $this->convertArray($data);
            $tempId =$categorys->getId();
            $tempData = $data;
            $this->loadedData[$categorys->getId()] = $convert;
        }
        if ($this->store->getParam('store')) {
            $connection = $this->resource->getConnection();
            foreach ($tempData as $key => $value) {
                if ($key === 'category_id' || $key === "scope_id") {
                    continue;
                } else {
                    $select = $connection->select()->from(
                        $this->resource->getTable('mgs_blog_category_update'),
                        'value',
                    )->where(
                        'category_id = ?',
                        (int)$tempId
                    )->where(
                            'scope_id = ?',
                            (int) $this->store->getParam('store')
                    )->where(
                            'field = ?',
                            $key
                        );
                    $category=$connection->fetchRow($select);
                    if ($category) {
                        $this->loadedData[$tempId]['general'][$key] = $category['value'];
                        $this->loadedData[$tempId]['meta'][$key] = $category['value'];
                    }
                }
            }
        }
        return $this->loadedData;
    }

    public function convertArray($category) {
        
        foreach ($category as $key => $value) {
            $data['general'][$key]= $value;
            $data['meta'][$key]=$value;
        }
        return $data;
    }

    public function getMeta() {
        $meta = parent::getMeta();
        $store =  $this->store->getParam('store');
        $category_id = $this->store->getParam('category_id');
        $ob = ['title','url_key','status','meta_keywords','meta_description'];
        $category = $this->getFieldUpdate($category_id,$store);
        if ($store) {
            foreach($category as $key => $value) {
                $id = array_search($key, $ob);
                unset($ob[$id]);
                if($key == 'meta_keywords' || $key == 'meta_description') {
                    $meta['meta']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['meta']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
                else {
                    $meta['general']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['general']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
            }
            foreach ($ob as $key => $value) {
                if($value == 'meta_keywords' || $value == 'meta_description') {
                    $meta['meta']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['meta']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
                else {
                    $meta['general']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['general']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
            }
        }
        return $meta;
    }

    public function getFieldUpdate($category_id,$store) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_blog_category_update'),
            'field',
        )->where(
            'category_id = ?',
            (int)$category_id
        )->where(
                'scope_id = ?',
                $store
        );
        return $connection->fetchAssoc($select);
    }

}