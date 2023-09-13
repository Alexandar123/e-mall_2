<?php

namespace MGS\Aquickview\Block;

class FormKey extends \Magento\Framework\View\Element\Template
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        $attr = null
    ) {
    
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }


    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getFormKey()
    {
        $formKey = $this->_objectManager->create('\Magento\Framework\View\Element\FormKey')->getFormKey();
        return $formKey;
    }
}
