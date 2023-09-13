<?php 

namespace MGS\Mmegamenu\Model;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MGS\Mmegamenu\Model\ResourceModel\Mmegamenu\CollectionFactory;
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
        CollectionFactory $megamenuFactory,
        DataPersistorInterface $dataPersistor,
        \MGS\Mmegamenu\Model\ResourceModel\Mmegamenu $resource,
        \Magento\Framework\App\Request\Http $request,
		PoolInterface $pool = null,
		array $meta = [],
        array $data = []
    )
    {
        $this->collection = $megamenuFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->resource = $resource;
        $this->store= $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData() {
        $general = ['store_id','title','url', 'status','position', 'columns',
                    'html_label','menu_type','special_class',
                    'parent_id','stores'];
        $static_content =['top_content','bottom_content','left_content', 
                            'right_content','left_col','right_col'];
        $tempId ='';
        $tempData='';
        $storeView = [];
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
      
        foreach ($items as $post) {
            
            $tempId =$post->getId();
            $store = $this->getStoreView($tempId);
            $post->setStoreId($this->store->getParam('store'));
            $data = $post->getData();
            foreach($store as $key => $value) {
                $storeView[]= $key;
            }
            $data['stores'] = implode(',',$storeView);
            $tempData = $data;
            $convert = $this->convertArray($data);
            $this->loadedData[$post->getId()] = $convert;
        }
     
        if ($this->store->getParam('store')) {
            $connection = $this->resource->getConnection();
            foreach($tempData as $key => $value) {
                if ($key === 'megamenu_id' || $key === "scope_id") {
                    continue;
                } else {
                    $select = $connection->select()->from(
                        $this->resource->getTable('mgs_megamenu_update'),
                        'value',
                    )->where(
                        'megamenu_id = ?',
                        (int)$tempId
                    )->where(
                        'scope_id = ?',
                        (int) $this->store->getParam('store')
                    )->where(
                        'field = ?',
                        $key
                    );
                    $post = $connection->fetchRow($select);
                    if ($post && in_array($key,$general)) {
                        $this->loadedData[$tempId]['general'][$key] = $post['value'];
                    }
                    if ($post && in_array($key,$static_content))
                        $this->loadedData[$tempId]['meta'][$key] = $post['value'];
                    if($post && $key=='category_id') {
                        $this->loadedData[$tempId]['category'][$key] = $post['value'];
                    }
                    if($post && $key=='static_content') {
                        $this->loadedData[$tempId]['static_contentt'][$key] = $post['value'];
 
                    }
                }
            }
        }
        return $this->loadedData;

    }
    function convertArray($data) {
        $general = ['store_id','title','url', 'status','position', 'columns',
                     'html_label','menu_type','special_class',
                     'parent_id','stores'];
        $static_content =['top_content','bottom_content','left_content', 
                            'right_content','left_col','right_col'];
        foreach($data as $key => $value) {
            if(in_array($key,$general)) {
                $data['general'][$key] = $value;
            }
            elseif(in_array($key,$static_content)) {
                $data['static_contents'][$key] = $value;
            }
            elseif($key == 'category_id') {
                $data['category'][$key] = $value;
            }
            else 
            $data['static_contentt'][$key] = $value;
        }
        return $data;
    }

    function getStoreView($megamenu_id) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_megamenu_store'),
            'store_id',
        )->where(
            'megamenu_id = ?',
            (int)$megamenu_id
        );
        return $connection->fetchAssoc($select);
    }
    function getMeta(){
        $meta = parent::getMeta();
        $store =  $this->store->getParam('store');
        $megamenu = $this->store->getParam('id');
        $ob = ['title','url','status','columns','html_label','top_content','bottom_content',
                'left_content', 'right_content','left_col',
                'right_col','static_content'];
        $static_content =['top_content','bottom_content','left_content', 
                       'right_content','left_col','right_col'];
        $post = $this->getFieldUpdate($megamenu,$store);
        if ($store) {
            foreach($post as $key => $value) {
                $id = array_search($key, $ob);
                unset($ob[$id]);
                if($key == 'static_content') {
                    $meta['static_contentt']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['static_contentt']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
                else if($key== 'category_id' ) {
                    $meta['category']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['category']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
                else if(in_array($key,$static_content)) {
                    $meta['static_contents']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['static_contents']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
                else {
                $meta['general']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                $meta['general']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
            }
            foreach ($ob as $key => $value) {
                if($value == 'static_content') {
                    $meta['static_contentt']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['static_contentt']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
                else if($value== 'category_id' ) {
                    $meta['category']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['category']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
                else if(in_array($value,$static_content)) {
                    $meta['static_contents']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['static_contents']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
                else {
                    $meta['general']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['general']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
            }
                $meta['general']['children']['stores']['arguments']['data']['config']['disabled'] = 1;

                $meta['general']['children']['menu_type']['arguments']['data']['config']['disabled'] = 1;

                $meta['general']['children']['position']['arguments']['data']['config']['disabled'] = 1;

                $meta['general']['children']['special_class']['arguments']['data']['config']['disabled'] = 1;

                $meta['general']['children']['menu_type']['arguments']['data']['config']['disabled'] = 1;
                
                $meta['general']['children']['parent_id']['arguments']['data']['config']['disabled'] = 1;

                $meta['category']['children']['category_id']['arguments']['data']['config']['disabled'] = 1;
            }
        return $meta;
    }

    public function getFieldUpdate($megamenu_id,$store) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_megamenu_update'),
            'field',
        )->where(
            'megamenu_id = ?',
            (int)$megamenu_id
        )->where(
                'scope_id = ?',
                $store
        );
        return $connection->fetchAssoc($select);
    }


}