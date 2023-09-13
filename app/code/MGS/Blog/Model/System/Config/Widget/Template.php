<?php

namespace MGS\Blog\Model\System\Config\Widget;

class Template implements \Magento\Framework\Option\ArrayInterface
{
    protected $postModel;

    public function __construct(\MGS\Blog\Model\Post $postModel)
    {
        $this->postModel = $postModel;
    }

    public function toOptionArray()
    {
        $options = array(
            array(
                'label' => __('Default Template'),
                'value' => 'widget/default.phtml'
            )
        );
        return $options;
    }
}
