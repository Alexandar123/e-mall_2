<?php
namespace MGS\AjaxCart\Block\Product;

use Magento\Framework\Registry;

/**
 * Class Actions
 * @package MGS\AjaxCart\Block\Product
 */
class Actions extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->jsonHelper   = $jsonHelper;
        $this->coreRegistry = $coreRegistry;
        $this->formKey = $formKey;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->coreRegistry->registry('current_product');
        return parent::_toHtml();
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
