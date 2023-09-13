<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lookbook_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Lookbook Information'));
    }
	
	protected function _beforeToHtml()
    {
		$this->addTab(
			'main_section',
			[
				'label' => __('General Information'),
				'content' => $this->getLayout()->createBlock('MGS\Lookbook\Block\Adminhtml\Edit\Tab\Main')->toHtml(),
			]
		);
		
        return parent::_beforeToHtml();
    }
}
