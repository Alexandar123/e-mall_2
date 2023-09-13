<?php
namespace MGS\Ajaxlayernavigation\Model\Layer\Filter;
 

abstract class DefaultFilter extends \Magento\Framework\DataObject implements \Magento\Catalog\Model\Layer\Filter\FilterInterface
{
    const ONLY_WITH_RESULT = 1;

    protected $_requestVar;
    protected $_items;
    protected $_layer;
    protected $_storeManager;
    protected $_itemFactory;
    protected $_itemBuilder;
    protected $_filterItemsCount;

    public function __construct(
        \MGS\Ajaxlayernavigation\Model\Layer\Filter\ItemFactory $_itemFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Catalog\Model\Layer $_layer,
        \MGS\Ajaxlayernavigation\Model\Layer\Filter\ItemBuilder $_itemBuilder,
        array $data = []
    ) {
        $this->_itemFactory = $_itemFactory;
        $this->_storeManager = $_storeManager;
        $this->_layer = $_layer;
        $this->_itemBuilder = $_itemBuilder;
        parent::__construct($data);
        if ($this->hasAttributeModel()) {
            $this->_requestVar = $this->getAttributeModel()->getAttributeCode();
        }
    }

    public function setRequestVar($varName)
    {
        $this->_requestVar = $varName;
        return $this;
    }

    public function getRequestVar()
    {
        return $this->_requestVar;
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        return $this;
    }

    public function getResetValue()
    {
        $result = null;
        return $result;
    }

    public function getCleanValue()
    {
        $result = null;
        return $result;
    }

    protected function _getDefaultItemsProducts()
    {
        $itemsCount = [];
        return $itemsCount;
    } 
 
    public function getFilterItemsCount()
    {
        if ($this->_filterItemsCount === null) {
            $this->_initFilterItemsCount();
        }
        return $this->_filterItemsCount;
    }

    protected function _getDefaultItemsCount()
    {
        $itemsCount = [];
        return $itemsCount;
    }

    protected function _initFilterItemsCount()
    {
        $this->_filterItemsCount = $this->_getDefaultItemsCount();
        return $this;
    }


    public function setItems(array $itemList)
    {
        $this->_items = $itemList;
        return $this;
    }

    public function getItems()
    {
        if ($this->_items === null) {
            $this->_initItems();
        }
        return $this->_items;
    }

    public function getItemsCount()
    {
        $items = $this->getItems();
        return count($items);
    }

    protected function _getItemsData()
    {
        $itemsData = [];
        return $itemsData;
    }

    protected function _initItems()
    {
        $itemsData = $this->_getItemsData();
        $itemList = [];
        foreach ($itemsData as $itemData) {
            $itemList[] = $this->_createItem(
                $itemData['label'],
                $itemData['value'],
                $itemData['count'],
                $itemData['active'],
                $itemData['plus']
            );
        }

        $this->_items = $itemList;
        return $this;
    }

    public function getLayer()
    {
        $layer = $this->_getData('layer');
        if ($layer === null) {
            $layer = $this->_layer;
            $this->setData('layer', $layer);
        }
        return $layer;
    }

    protected function _createItem($itemLabel, $itemValue, $itemCount = 0, $active = false, $plus = false)
    {
        return $this->_itemFactory->create()
            ->setFilter($this)
            ->setLabel($itemLabel)
            ->setValue($itemValue)
            ->setCount($itemCount)
            ->setActive($active)
            ->setPlus($plus);
    }

    protected function _getFilterEntityIds()
    {
        $collection = $this->getLayer()->getProductCollection();
        return $collection->getAllIdsCache();
    }

    protected function _getBaseCollectionSql()
    {
        $collection = $this->getLayer()->getProductCollection();
        return $collection->getSelect();
    }

    public function setAttributeModel($attributeModel)
    {
        $code = $attributeModel->getAttributeCode();
        $this->setRequestVar($code);
        $this->setData('attribute_model', $attributeModel);
        return $this;
    }

    public function getAttributeModel()
    {
        $attributeModel = $this->getData('attribute_model');
        if (null === $attributeModel) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The attribute model is not defined.')
            );
        }
        return $attributeModel;
    }

    public function getName()
    {
        $name = $this->getAttributeModel()->getStoreLabel();
        return $name;
    }

    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    public function getStoreId()
    {
        $id = $this->_getData('store_id');
        if (null === $id) {
            $id = $this->_storeManager->getStore()->getId();
        }
        return $id;
    }

    public function getWebsiteId()
    {
        $website = $this->_getData('website_id');
        if (null === $website) {
            $website = $this->_storeManager->getStore()->getWebsiteId();
        }
        return $website;
    }

    public function setWebsiteId($websiteId)
    {
        return $this->setData('website_id', $websiteId);
    }

    public function getClearLinkText()
    {
        return false;
    }

    protected function getOptionText($id)
    {
        $attribute = $this->getAttributeModel();
        return $attribute->getFrontend()->getOption($id);
    }

    protected function getAttributeIsFilterable($attr)
    {
        return (int)$attr->getIsFilterable();
    }

    protected function isOptionReducesResults($count, $size)
    {
        return $count < $size;
    }
}
