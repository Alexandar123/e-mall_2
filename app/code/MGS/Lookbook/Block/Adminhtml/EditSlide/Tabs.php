<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml\EditSlide;

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
        $this->setId('lookbook_slide_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Slider Information'));
    }
	
	protected function _beforeToHtml()
    {
		$this->addTab(
			'main_section',
			[
				'label' => __('General Information'),
				'content' => $this->getLayout()->createBlock('MGS\Lookbook\Block\Adminhtml\EditSlide\Tab\Main')->toHtml(),
			]
		);
		
		$this->addTab(
            'lookbook_section',
            [
                'label' => __('Slides'),
                'url' => $this->getUrl('adminhtml/lookbookslide/items', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
		
        return parent::_beforeToHtml();
    }
}
