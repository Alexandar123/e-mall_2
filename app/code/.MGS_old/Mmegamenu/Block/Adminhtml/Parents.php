<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Block\Adminhtml;

class Parents extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_mmegamenu';
        $this->_blockGroup = 'MGS_Mmegamenu';
        $this->_headerText = __('Mmegamenu');
        $this->_addButtonLabel = __('Add Menu');
        $this->addButton('clear_cache', ['label'=>'Flush Menu Cache', 'class'=>'primary','onclick'=>'setLocation("'.$this->getUrl("adminhtml/mmegamenu/flushcache").'")']);
        parent::_construct();
    }

	/**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newparent');
    }
}
