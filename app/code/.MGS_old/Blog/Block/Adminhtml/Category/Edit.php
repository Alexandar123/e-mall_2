<?php

namespace MGS\Blog\Block\Adminhtml\Category;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'category_id';
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'MGS_Blog';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
    }

    public function getHeaderText()
    {
        $category = $this->_coreRegistry->registry('current_category');
        if ($category->getId()) {
            return __("Edit Category '%1'", $this->escapeHtml($category->getTitle()));
        } else {
            return __('New New Category');
        }
    }
}
