<?php
namespace MGS\AjaxCart\Block\Source;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Actions
 * @package MGS\AjaxCart\Block\Source
 */
class Actions extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->jsonHelper   = $jsonHelper;
        $this->formKey = $formKey;
    }

    /**
     * Check if can redirect to cart
     *
     * @return bool
     */
    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->jsonHelper->jsonEncode($this->formKey->getFormKey());
    }
}
