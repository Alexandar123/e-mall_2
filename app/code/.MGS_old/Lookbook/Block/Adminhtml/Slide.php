<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml;

class Slide extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_lookbook_slide';
        $this->_blockGroup = 'MGS_Lookbook';
        $this->_headerText = __('Lookbook Slider');
        $this->_addButtonLabel = __('Add Slider');
        parent::_construct();
    }

}
