<?php

namespace MGS\Blog\Block\Adminhtml\Comment;

use MGS\Blog\Model\System\Config\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;
    protected $_comment;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MGS\Blog\Model\Comment $comment,
        \MGS\Blog\Model\Resource\Comment\Grid\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_comment = $comment;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('commentGrid');
        $this->setDefaultSort('comment_id');
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
            'comment_id',
            [
                'header' => __('ID'),
                'index' => 'comment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
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
                'options' => array(
                    1 => __('Approved'),
                    2 => __('Unapproved')
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
                        'caption' => __('Approve'),
                        'url' => [
                            'base' => 'blog/comment/approve',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'comment_id'
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
        $this->setMassactionIdField('comment_id');
        $this->getMassactionBlock()->setFormFieldName('comment');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('blog/comment/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = Status::getAvailableStatuses();
        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change Status'),
                'url' => $this->getUrl('blog/comment/massStatus', ['_current' => true]),
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
        return $this->getUrl('blog/comment/approve', ['comment_id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('blog/comment/grid', ['_current' => true]);
    }
}
