<?php

namespace MGS\Blog\Block\Adminhtml;

class Post extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'MGS_Blog';
        $this->_headerText = __('Manage Posts');
        $this->_addButtonLabel = __('Add New Post');
        parent::_construct();
    }
}
