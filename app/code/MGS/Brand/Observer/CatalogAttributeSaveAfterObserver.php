<?php

namespace MGS\Brand\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogAttributeSaveAfterObserver implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        try {
            $attribute = $observer->getEvent()->getAttribute();
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == 'mgs_brand') {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $attributeModel = $objectManager->create('Magento\Catalog\Api\ProductAttributeRepositoryInterface')->get($attributeCode);
                $options = $attributeModel->getOptions();
                foreach ($options as $option) {
                    $value = $option->getValue();
                    if ($value) {
                        $brandCollection = $objectManager->create('MGS\Brand\Model\Brand')->getCollection();
                        $brandCollection->addFieldToFilter('option_id', ['eq' => $value]);
                        if (count($brandCollection)) {
                            $brand = $brandCollection->getFirstItem();
                            $brand->setName($option->getLabel());
                            $brand->setUrlKey($objectManager->get('Magento\Catalog\Model\Product\Url')->formatUrlKey($option->getLabel()));
                            $brand->save();
                        } else {
                            $brand = $objectManager->create('MGS\Brand\Model\Brand');
                            $brand->setName($option->getLabel());
                            $brand->setUrlKey($objectManager->get('Magento\Catalog\Model\Product\Url')->formatUrlKey($option->getLabel()));
                            $brand->setOptionId($value);
                            $brand->save();
                        }
                    }
                }
                $options = $attribute->getOption();
                $deletes = $options['delete'];
                if (count($deletes)) {
                    foreach ($deletes as $optionId => $value) {
                        if ((int)$value == 1) {
                            $brandCollection = $objectManager->create('MGS\Brand\Model\Brand')->getCollection();
                            $brandCollection->addFieldToFilter('option_id', ['eq' => $optionId]);
                            if (count($brandCollection)) {
                                $brand = $brandCollection->getFirstItem();
                                $brand->delete();
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
        return $this;
    }
}
