<?php

namespace MGS\StoreLocator\Block\Adminhtml;

use MGS\StoreLocator\Model\ResourceModel\Store\Collection;

class Locator extends \Magento\Backend\Block\Template
{

    /**
     * @var \MGS\StoreLocator\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_collectionFactory;

    protected $_template = 'locator/list.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \MGS\StoreLocator\Model\ResourceModel\Store\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, \MGS\StoreLocator\Model\ResourceModel\Store\CollectionFactory $collectionFactory, array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->addButton(
            'save',
            [
                'id' => 'save',
                'label' => __('Add New Store'),
                'class' => 'save primary',
                'onclick' => "window.location = '" . $this->getUrl('locator/*/edit') . "'"
            ]
        );
    }

    protected function addButton($buttonId, array $data)
    {
        $childBlockId = $buttonId . '_button';
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button', $this->getNameInLayout() . '-' . $childBlockId);
        $button->setData($data);
        $block = $this->getLayout()->getBlock('page.actions.toolbar');
        if ($block) {
            $block->setChild($childBlockId, $button);
        }
    }

    /**
     * Prepares block to render
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

}
