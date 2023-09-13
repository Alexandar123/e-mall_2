<?php

namespace MGS\Mmegamenu\Model\Options;

use MGS\Mmegamenu\Model\ResourceModel\Parents\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class MenuParent implements ArrayInterface {

    protected $collection;

    public function __construct(
        CollectionFactory $collection
    ){
        $this->collection = $collection->create();
    }

    function toOptionArray() {
        $options = [];

        foreach($this->collection as $item) {
            $options[] = [
                'label' => $item->getTitle(),
                'value' => $item->getParentId()
           ];
        }
        return $options;
    }
}
