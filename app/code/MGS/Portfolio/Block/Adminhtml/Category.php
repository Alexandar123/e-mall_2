<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block\Adminhtml;

class Category extends \Magento\Backend\Block\Widget\Grid\Container
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
        $this->_headerText = __('Portfolio Categories');
        $this->_addButtonLabel = __('Add Category');
        parent::_construct();
    }
	
	/**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newcategory');
    }
}
