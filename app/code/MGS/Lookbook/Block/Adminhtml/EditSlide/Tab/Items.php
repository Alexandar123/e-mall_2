<?php

namespace MGS\Lookbook\Block\Adminhtml\EditSlide\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Items extends Extended
{
    protected $_lookbookCollectionFactory;
    protected $_itemCollectionFactory;
    protected $_slideFactory;

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
		\MGS\Lookbook\Model\Slide $slideFactory,
		\MGS\Lookbook\Model\ResourceModel\Item\CollectionFactory $itemFactory,
		\MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory $lookbookFactory,
        array $data = []
    ) {
        $this->_slideFactory = $slideFactory;
        $this->_itemCollectionFactory = $itemFactory;
		$this->_lookbookCollectionFactory = $lookbookFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('lookbook_grid');
        $this->setDefaultSort('lookbook_id');
        $this->setUseAjax(true);
        if ($this->_getSlider()->getId()) {
            $this->setDefaultFilter(array('in_slider' => 1));
        }
    }

	protected function _getSlider() {
        $sliderId = $this->getRequest()->getParam('id');
        return $this->_slideFactory->load($sliderId);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_slider') {
            $lookbookIds = $this->_getSelectedLookbooks();
            if (empty($lookbookIds)) {
                $lookbookIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('lookbook_id', ['in' => $lookbookIds]);
            } else {
                if ($lookbookIds) {
                    $this->getCollection()->addFieldToFilter('lookbook_id', ['nin' => $lookbookIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->_lookbookCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_slider',
            [
                'type' => 'checkbox',
                'name' => 'in_slider',
                'values' => $this->_getSelectedLookbooks(),
                'align' => 'center',
                'index' => 'lookbook_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );
        $this->addColumn(
            'lookbook_id',
            [
                'header' => __('Lookbook ID'),
                'sortable' => true,
                'index' => 'lookbook_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		
		$this->addColumn(
            'image',
            [
                'header' => __('Image'),
                'index' => 'image',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title',
				'sortable' => false,
                'filter' => false,
				'renderer' => 'MGS\Lookbook\Block\Adminhtml\Grid\Renderer\Image',
            ]
        );
		
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options'   => [
					1 => 'Enabled',
					0 => 'Disabled',
				],
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );
        
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'sortable' => false,
                'filter' => false,
                'editable' => true,
                'edit_only' => false,
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/itemsGrid', ['_current' => true]);
    }

    protected function _getSelectedLookbooks()
    {
        $lookbookIds = $this->getLookbookIds();
        if (is_null($lookbookIds)) {
            $lookbookIds = array_keys($this->getSelectedLookbooks());
        }
        return $lookbookIds;
    }

    public function getSelectedLookbooks()
    {
        $lookbookIds = [];
        if ($this->_getSlider() && $this->_getSlider()->getId()) {
            $collection = $this->_itemCollectionFactory->create();
            $collection->addFieldToFilter('slide_id', $this->_getSlider()->getId());
            foreach ($collection as $item) {
                $lookbookIds[$item->getLookbookId()] = ['position' => $item->getPosition()];
            }
        }
        return $lookbookIds;
    }
}