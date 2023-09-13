<?php

namespace MGS\Blog\Block\Widget;

class AbstractWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_blogHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MGS\Blog\Helper\Data $blogHelper,
        array $data = []
    )
    {
        $this->_blogHelper = $blogHelper;
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
