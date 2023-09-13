<?php

namespace MGS\Blog\Block\Adminhtml;

class Comment extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_comment';
        $this->_blockGroup = 'MGS_Blog';
        $this->_headerText = __('Manage Comments');
        $this->_addButtonLabel = __('Add New Comment');
        parent::_construct();
    }
}
