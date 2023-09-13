<?php


namespace MGS\Portfolio\Model\Options;

use Magento\Framework\Option\ArrayInterface;
use MGS\Portfolio\Model\ResourceModel\Category\CollectionFactory;

class Category implements ArrayInterface
{
    protected $collection;

    protected $resource;

    protected $request;

    public function __construct(
        \MGS\Portfolio\Model\ResourceModel\Category\CollectionFactory $category,
        \MGS\Portfolio\Model\ResourceModel\Category $resource,
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
        $category= $this->collection->create();
        foreach ($category as $items) {
            $options[] = [
                'label' => $items->getCategoryName(),
                'value' => $items->getCategoryId()
            ];
        }
        return $options;
    }
}
