<?php

namespace MGS\Blog\Block\Adminhtml\Post;

use MGS\Blog\Model\System\Config\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;
    protected $_post;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MGS\Blog\Model\Post $post,
        \MGS\Blog\Model\Resource\Post\Grid\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_post = $post;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('post_id');
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
            'post_id',
            [
                'header' => __('ID'),
                'index' => 'post_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'thumbnail',
            [
                'header' => __('Thumbnail'),
                'index' => 'small_image',
                'renderer' => '\MGS\Blog\Block\Adminhtml\Post\Grid\Renderer\Thumbnail',
                'sortable' => false,
                'filter' => false
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
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
                            'base' => 'blog/post/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'post_id'
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
        $this->setMassactionIdField('post_id');
        $this->getMassactionBlock()->setFormFieldName('post');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('blog/post/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = Status::getAvailableStatuses();
        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change Status'),
                'url' => $this->getUrl('blog/post/massStatus', ['_current' => true]),
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
        return $this->getUrl('blog/post/edit', ['post_id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('blog/post/grid', ['_current' => true]);
    }
}
