<?php
namespace MGS\Ajaxlayernavigation\Model\Layer\Filter; 

class Category extends \MGS\Ajaxlayernavigation\Model\Layer\Filter\DefaultFilter
{
    protected $appliyedFilter;
    
    protected $filterPlus;

    public function __construct(
        \MGS\Ajaxlayernavigation\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \MGS\Ajaxlayernavigation\Model\Layer\Filter\ItemBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->appliedFilter = [];
        $this->filterPlus = false;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }
       
        $categoryIds = explode(',', $categoryId);
        $categoryIds = array_unique($categoryIds); 
        $productCollection = $this->getLayer()->getProductCollection();

        if ($request->getParam('id') != $categoryId) {  
             $this->appliedFilter = $categoryId; 
            if (!$this->filterPlus) {
                $this->filterPlus = true;
            }
            $productCollection->addCategoriesFilter(['in' => $categoryIds]);
            $category = $this->getLayer()->getCurrentCategory();
            $child = $category->getCollection()
                ->addFieldToFilter($category->getIdFieldName(), ['in' => $categoryIds])
                ->addAttributeToSelect('name');
            $categoriesInState = [];
            foreach ($categoryIds as $categoryId) {
                if ($currentCategory = $child->getItemById($categoryId)) {
                    $categoriesInState[$currentCategory->getId()] = $currentCategory->getName();
                }
            }
            foreach ($categoriesInState as $key => $category) {
                $state = $this->_createItem($category, $key);
                $this->getLayer()->getState()->addFilter($state);
            }
        }
        return $this;
    }

    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();  
        $collectionSize = $productCollection->getSize();
        $activeFilters = [];
        if($this->appliedFilter) {
            $activeFilters = explode(',', $this->appliedFilter);
        }  
        $currentProductIds = $productCollection->getAllIds();
        if ($category->getIsActive()) {
            foreach ($categories as $category) {  
                if ($category->getIsActive()
                    && isset($optionsFacetedData[$category->getId()]) 
                ) { 
                    $active = in_array($category->getId(), $activeFilters);
                    $this->_itemBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $optionsFacetedData[$category->getId()]['count'],
                        $active,
                        $this->filterPlus
                    );
                }
            }
        } 
        return $this->_itemBuilder->build();
    }

    public function getName()
    {
        return __('Category');
    }
}
