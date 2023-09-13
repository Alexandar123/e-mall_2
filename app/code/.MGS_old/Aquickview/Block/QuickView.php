<?php

namespace MGS\Aquickview\Block;

class QuickView extends \Magento\Framework\View\Element\Template
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

    public function _getCurrentUrl()
    {
        $urlinterface = $this->_objectManager->get('\Magento\Framework\UrlInterface');
        return $urlinterface->getCurrentUrl();
    }
}
