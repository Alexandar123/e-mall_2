<?php
namespace MGS\AjaxCart\Block\Product;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Config
 * @package MGS\AjaxCart\Block\Product
 */
class Config extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get additional JSON-formatted ACP options for product page
     *
     * @return string
     */
    public function getOptions()
    {
        return \Zend_Json::encode([
            'productCategoryUrl' => $this->getCategoryUrl()
        ]);
    }

    /**
     * Get first of the product categories
     *
     * @return string|null
     */
    private function getCategoryUrl()
    {
        if (!$product = $this->coreRegistry->registry('current_product')) {
            return null;
        }
        $firstCategory = $product->getCategoryCollection()->getFirstItem();
        return  $firstCategory->getUrl();
    }
}
