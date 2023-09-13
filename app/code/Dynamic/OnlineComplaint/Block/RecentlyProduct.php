<?php

namespace Dynamic\OnlineComplaint\Block;

class RecentlyProduct extends \Magento\Framework\View\Element\Template

{

    /**

     * @var \Magento\Reports\Block\Product\Viewed

     */

    protected $recentlyViewed;

    /**

     * @param \Magento\Framework\View\Element\Template\Context $context

     * @param \Magento\Reports\Block\Product\Viewed            $recentlyViewed

     * @param array                                            $data

     */

    public function __construct(
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollectionFactory,   
        \Magento\Store\Model\StoreManagerInterface $storeManager   

    ){
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->storeManager = $storeManager;
    }


   public function getMostViewedProducts()
   {
         $storeId =  $this->storeManager->getStore()->getId();

         $collection = $this->reportCollectionFactory->create()
               ->addAttributeToSelect(
                   '*'
               )->addViewsCount()->setStoreId(
                       $storeId
               )->addStoreFilter(
                       $storeId
               );
         $items = $collection->getItems();
         return $items;
   }

}

?>