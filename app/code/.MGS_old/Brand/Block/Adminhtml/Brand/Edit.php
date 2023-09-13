<?php

namespace MGS\Brand\Block\Adminhtml\Brand;

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
        $this->_objectId = 'brand_id';
        $this->_controller = 'adminhtml_brand';
        $this->_blockGroup = 'MGS_Brand';

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
        $brand = $this->_coreRegistry->registry('current_brand');
        if ($brand->getId()) {
            return __("Edit Brand '%1'", $this->escapeHtml($brand->getName()));
        } else {
            return __('New New Brand');
        }
    }
}
