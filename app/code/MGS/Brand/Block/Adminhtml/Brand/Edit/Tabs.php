<?php

namespace MGS\Brand\Block\Adminhtml\Brand\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brand_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Brand'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'product_section',
            [
                'label' => __('Products'),
                'url' => $this->getUrl('brand/brand/product', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return parent::_beforeToHtml();
    }
}
