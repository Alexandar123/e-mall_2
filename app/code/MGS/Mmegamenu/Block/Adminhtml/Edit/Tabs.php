<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Block\Adminhtml\Edit;

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
        $this->setId('mmegamenu_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Megamenu Information'));
    }
	
	protected function _beforeToHtml()
    {
		$this->addTab(
			'main_section',
			[
				'label' => __('General Information'),
				'content' => $this->getLayout()->createBlock('MGS\Mmegamenu\Block\Adminhtml\Edit\Tab\Main')->toHtml(),
			]
		);
		

		$this->addTab(
			'category',
			[
				'label' => __('Category'),
				'content' => $this->getLayout()->createBlock('MGS\Mmegamenu\Block\Adminhtml\Edit\Tab\Category')->toHtml(),
			]
		);
		
		$this->addTab(
			'topbottom',
			[
				'label' => __('Static Contents'),
				'content' => $this->getLayout()->createBlock('MGS\Mmegamenu\Block\Adminhtml\Edit\Tab\Topbottom')->toHtml(),
			]
		);

		$this->addTab(
			'static',
			[
				'label' => __('Static Content'),
				'content' => $this->getLayout()->createBlock('MGS\Mmegamenu\Block\Adminhtml\Edit\Tab\Statics')->toHtml(),
			]
		);
		
        return parent::_beforeToHtml();
    }
}
