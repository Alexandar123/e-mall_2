<?php
namespace MGS\AjaxCart\Block\Product;

use Magento\Framework\View\Element\Template\Context;

/**
 * Class StockOption
 * @package MGS\AjaxCart\Block\Product
 */
class StockOption extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable 
     */
    private $_configurableProductModel;

	/**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $_stockItemRegistry;
	
	/**
     * @var \Magento\Catalog\Block\Product\View
     */
    private $_product;
	
    /**
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable  $configurableProductModel,
        \Magento\CatalogInventory\Model\StockRegistry  $stockItemRegistry,
        \Magento\Catalog\Block\Product\View  $product,
        Context $context,
        array $data = []
    ) {
        $this->_configurableProductModel = $configurableProductModel;
        $this->_stockItemRegistry = $stockItemRegistry;
        $this->_product = $product;
        parent::__construct($context, $data);
    }

    /**
     * Array of attributes to be used for stock data
     * 
     * @var array
     */
    protected $_stockDataAttributes = [
        'qty',
        'is_in_stock'
    ];

	public function getProduct(){
		return $this->_product->getProduct();
	}
	
    public function getProductChildData($productId)
    {
        $data = [];
        $children = $this->_configurableProductModel->getChildrenIds($productId);

        foreach ($children as $child) {
            foreach ($child as $item) {
                /** @var \Magento\CatalogInventory\Model\StockRegistry $_stockItemRegistry */
                $stockItem = $this->_stockItemRegistry->getStockItem($item);
                if($stockItem) {
                    foreach ($this->_stockDataAttributes as $attribute) {
                        $data[$item][$attribute] = $stockItem->getData($attribute);
                    }
                }
            }
        }

        return $data;
    }
}
