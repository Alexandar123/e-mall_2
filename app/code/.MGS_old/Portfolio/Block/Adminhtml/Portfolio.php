<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block\Adminhtml;

class Portfolio extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_portfolio';
        $this->_blockGroup = 'MGS_Portfolio';
        $this->_headerText = __('Portfolio');
        $this->_addButtonLabel = __('Add Item');
        parent::_construct();
    }

}
