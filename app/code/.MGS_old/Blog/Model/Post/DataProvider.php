<?php 

namespace MGS\Blog\Model\Post;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MGS\Blog\Model\Resource\Post\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use MGS\Blog\Model\Post\FileInfo;

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
        CollectionFactory $postFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Request\Http $request,
        \MGS\Blog\Model\Resource\Category $resource,
		PoolInterface $pool = null,
		array $meta = [],
        array $data = []
    )
    {
        $this->collection = $postFactory->create();
        $this->dataPersistor = $dataPersistor;
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
        foreach ($items as $post) {
            $tempId =$post->getId();
            $post->setStoreId($this->store->getParam('store'));
            if (isset($post['image']) && $post['image']!= null) {
                $post= $this-> convertValues($post);
            }
            if (isset($post['thumbnail']) && $post['thumbnail']!= null) {
                $post = $this->convertThumbnail($post);
            }
            $post['categories'] = $this->getCategory($tempId);
            $data = $post->getData();
            $tempData = $data;
            $thum_type = $post->getThumbType();
            $convert = $this->convertArray($data);
            $this->loadedData[$post->getId()] = $convert;
        }
     
        if ($this->store->getParam('store')) {
            $connection = $this->resource->getConnection();
            foreach ($tempData as $key => $value) {
                if ($key === 'post_id' || $key === "scope_id") {
                    continue;
                } else {
                    $select = $connection->select()->from(
                        $this->resource->getTable('mgs_blog_post_update'),
                        'value',
                    )->where(
                        'post_id = ?',
                        (int)$tempId
                    )->where(
                            'scope_id = ?',
                            (int) $this->store->getParam('store')
                        )->where(
                            'field = ?',
                            $key
                        );
                    $post = $connection->fetchRow($select);
                    if($post) {
                        if ($key == 'meta_keywords' || $key == 'meta_description'){
                            $this->loadedData[$tempId]['meta'][$key] = $post['value'];
                        } else {
                            $this->loadedData[$tempId]['general'][$key] = $post['value'];
                        }
                    }
                    
                }
            }
        }
        return $this->loadedData;
    }

    public function convertArray($post) {
        
        foreach ($post as $key => $value) {
            if ($key == 'meta_keywords' || $key == 'meta_description'){
                $data['meta'][$key]=$value;
            } else {
                $data['general'][$key]= $value;
            }
        }
        return $data;
    }

    private function convertValues($banner)
    {
        $fileName = $banner->getImage();
        $temp =  strpos($fileName,'mgs_blog');
        if($temp !== false)
            $fileName = substr($fileName,$temp + 8);
        $image = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $image[0]['name'] = $fileName;
            $image[0]['url'] = $this->convertUrl($banner->getImageUrl()).'mgs_blog/'.$fileName;
            $image[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $image[0]['type'] = $mime;  
        }
        $banner->setImage($image);
        return $banner;
    }

    private function convertThumbnail($data)
    {
        $fileName = $data->getThumbnail();
        $temp =  strpos($fileName,'mgs_blog');
        if($temp !== false)
            $fileName = substr($fileName,$temp + 8);
        $image = [];
        if ($this->getFileInfo()->isExist($fileName)) {
            $stat = $this->getFileInfo()->getStat($fileName);
            $mime = $this->getFileInfo()->getMimeType($fileName);
            $image[0]['name'] = $fileName;
            $image[0]['url'] = $this->convertUrl($data->getthumbnailUrl()).'mgs_blog/'.$fileName;
            $image[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $image[0]['type'] = $mime;
        }
        $data->setThumbnail($image);
        return $data;
    }
    
    public function getCategory($id){
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_blog_category_post'),
            'category_id'
        )->where(
            'post_id = ?',
            (int)$id
        );
        return  $connection->fetchCol($select);
    }

    private function convertUrl($name) {
        $temp = strpos($name,'media');
        $name = substr($name,0,$temp + 6);
        return $name;
    }

    private function getFileInfo()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(FileInfo::class);
        }
        return $this->fileInfo;
    }

    public function getMeta() {
        $meta = parent::getMeta();
        $store =  $this->store->getParam('store');
        $post_id = $this->store->getParam('post_id');
        $ob = ['title','status','content','short_content','tags','meta_keywords','meta_description'];
        $post = $this->getFieldUpdate($post_id,$store);
        if ($store) {
            foreach($post as $key => $value) {
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
            $meta['general']['children']['url_key']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['image_type']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['thumb_type']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['thumbnail']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['image']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['categories']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['video_thumbnail_type']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['video_thumb_id']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['video_big_type']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['video_big_id']['arguments']['data']['config']['disabled'] = 1;
        }
        return $meta;
    }
    
    public function getFieldUpdate($category_id,$store) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_blog_post_update'),
            'field',
        )->where(
            'post_id = ?',
            (int)$category_id
        )->where(
                'scope_id = ?',
                $store
        );
        return $connection->fetchAssoc($select);
    }
}