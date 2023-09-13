<?php

namespace MGS\StoreLocator\Block;

class Toplink extends \Magento\Framework\View\Element\Template
{
    protected $scopeConfig;
    const XML_PATH_LOCATOR_LABEL = 'locator/general/top_link_label';
    const XML_PATH_LOCATOR_TOPLINK = 'locator/general/top_link';

    protected $_defaultPath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_defaultPath = $defaultPath;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath());
    }

    /**
     * Get current mca
     *
     * @return string
     */
    private function getMca()
    {
        $routeParts = [
            'module' => $this->_request->getModuleName(),
            'controller' => $this->_request->getControllerName(),
            'action' => $this->_request->getActionName(),
        ];

        $parts = [];
        foreach ($routeParts as $key => $value) {
            if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }

    /**
     * Check if link leads to URL equivalent to URL of currently displayed page
     *
     * @return bool
     */
    public function isCurrent()
    {
        return $this->getCurrent() || $this->getUrl($this->getPath()) == $this->getUrl($this->getMca());
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if(!$this->scopeConfig->getValue(self::XML_PATH_LOCATOR_TOPLINK, $storeScope)) {
            return '';
        }
        $storeLabel = empty($this->scopeConfig->getValue(self::XML_PATH_LOCATOR_LABEL, $storeScope))
        ? $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()))
        : $this->scopeConfig->getValue(self::XML_PATH_LOCATOR_LABEL, $storeScope);
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $highlight = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        if ($this->isCurrent()) {
            $html = '<li class="nav item current">';
            $html .= '<strong>'
                . $storeLabel
                . '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle()
                ? ' title="' . $storeLabel . '"'
                : '';
            $html .= $this->getAttributesHtml() . '>';

            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }

            $html .= $storeLabel;

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }

        return $html;
    }

    /**
     * Generate attributes' HTML code
     *
     * @return string
     */
    private function getAttributesHtml()
    {
        $attributesHtml = '';
        $attributes = $this->getAttributes();
        if ($attributes) {
            foreach ($attributes as $attribute => $value) {
                $attributesHtml .= ' ' . $attribute . '="' . $this->escapeHtml($value) . '"';
            }
        }

        return $attributesHtml;
    }

}
