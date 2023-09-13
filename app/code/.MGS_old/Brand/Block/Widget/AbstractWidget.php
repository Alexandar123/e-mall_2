<?php

namespace MGS\Brand\Block\Widget;

class AbstractWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_brandHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MGS\Brand\Helper\Data $brandHelper,
        array $data = []
    )
    {
        $this->_brandHelper = $brandHelper;
        parent::__construct($context, $data);
    }

    public function getConfig($key, $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        return $default;
    }
}