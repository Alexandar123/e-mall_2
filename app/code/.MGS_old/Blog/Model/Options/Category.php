<?php 

namespace MGS\Blog\Model\Options ;

use Magento\Framework\Option\ArrayInterface;
use MGS\Blog\Model\Resource\Category\CollectionFactory;

class Category implements ArrayInterface {

    protected $collection;

    protected $resource;

    protected $request;

    public function __construct(
        CollectionFactory $category,
        \MGS\Blog\Model\Resource\Category $resource,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->collection = $category;
        $this->resource = $resource;
        $this->request = $request;
    }

   function toOptionArray()
   {   
       $options = [];
       $store_id  = $this->request->getParam('store');
       $category= $this->collection->create();
       if($store_id) { 
        $temp = $this->getCategorybyStore($store_id);
            if ($temp) {
                foreach ($temp as $key => $value) {
                    $options[] = [
                        'label' => $value['value'] ,
                        'value' => $value['category_id']
                    ];
                }
            }
            else {
                foreach ($category as $items) {
                    $options[] = [
                         'label' => $items->getTitle(),
                         'value' => $items->getCategoryId()
                    ];
                }
            }
        }
       else {
           foreach ($category as $items) {
               $options[] = [
                    'label' => $items->getTitle(),
                    'value' => $items->getCategoryId()
                ];
            }
        }
       return $options; 
    }

    function getCategorybyStore($store_id)
    {
        $table = $this->resource->getTable('mgs_blog_category_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `category_id`, `value`
                 FROM `$table` 
                 WHERE `scope_id`= $store_id
                 AND `field`='title'";
        $category = $connection->fetchAssoc($sql);
        return $category;          
        }
    }