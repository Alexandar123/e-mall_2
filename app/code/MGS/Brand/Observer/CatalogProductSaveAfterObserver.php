<?php

namespace MGS\Brand\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($product->getId()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fullActionName = $objectManager->get('Magento\Framework\App\Request\Http')->getFullActionName();
                if ($fullActionName != 'brand_brand_save') {
                    $productId = $product->getId();
                    $value = $product->getMgsBrand();
                    if ((int)$value) {
                        $brandCollection = $objectManager->create('MGS\Brand\Model\Brand')->getCollection();
                        $brandCollection->addFieldToFilter('option_id', ['eq' => $value]);
                        if (count($brandCollection)) {
                            $p = $objectManager->create('MGS\Brand\Model\Product');
                            $p->setBrandId($brandCollection->getFirstItem()->getId());
                            $p->setProductId($productId);
                            $p->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }
}
