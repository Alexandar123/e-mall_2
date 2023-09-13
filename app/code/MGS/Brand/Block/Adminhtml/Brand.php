<?php

namespace MGS\Brand\Block\Adminhtml;

class Brand extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_brand';
        $this->_blockGroup = 'MGS_Brand';
        $this->_headerText = __('Manage Brands');
        $this->_addButtonLabel = __('Add New Brand');
        parent::_construct();
    }
}
