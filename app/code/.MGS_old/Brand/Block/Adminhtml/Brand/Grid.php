<?php

namespace MGS\Brand\Block\Adminhtml\Brand;

use MGS\Brand\Model\System\Config\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;
    protected $_brand;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MGS\Brand\Model\Brand $brand,
        \MGS\Brand\Model\Resource\Brand\Grid\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_brand = $brand;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('brandGrid');
        $this->setDefaultSort('brand_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'brand_id',
            [
                'header' => __('Brand ID'),
                'index' => 'brand_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'small_image',
            [
                'header' => __('Small Image'),
                'index' => 'small_image',
                'renderer' => '\MGS\Brand\Block\Adminhtml\Brand\Grid\Renderer\Image',
                'sortable' => false,
                'filter' => false
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
            'url_key',
            [
                'header' => __('URL Key'),
                'index' => 'url_key'
            ]
        );
        $this->addColumn(
            'is_featured',
            [
                'header' => __('Featured Brand'),
                'index' => 'is_featured',
                'type' => 'options',
                'options' => array(
                    1 => __('Yes'),
                    0 => __('No')
                )
            ]
        );
        $this->addColumn(
            'sort_order',
            [
                'header' => __('Sort Order'),
                'index' => 'sort_order'
            ]
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                [
                    'header' => __('Store View'),
                    'index' => 'store_id',
                    'type' => 'store',
                    'store_all' => true,
                    'store_view' => true,
                    'sortable' => false,
                    'filter_condition_callback' => [$this, '_filterStoreCondition']
                ]
            );
        }
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => array(
                    1 => __('Enabled'),
                    2 => __('Disabled')
                )
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'brand_id'
                    ]
                ],
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('brand_id');
        $this->getMassactionBlock()->setFormFieldName('brand');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brand/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = Status::getAvailableStatuses();
        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change Status'),
                'url' => $this->getUrl('brand/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['brand_id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
