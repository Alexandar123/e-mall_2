<?php

namespace MGS\Brand\Controller\Adminhtml\Brand;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \MGS\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $jsHelper = $this->_objectManager->create('Magento\Backend\Helper\Js');
                $model = $this->_objectManager->create('MGS\Brand\Model\Brand');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Magento\Framework\Filter\FilterInput(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('brand_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong brand is specified.'));
                    }
                }
                if (isset($_FILES['small_image']['name']) && $_FILES['small_image']['name'] != '') {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'small_image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $ext = pathinfo($_FILES['small_image']['name'], PATHINFO_EXTENSION);
                    if ($uploader->checkAllowedExtension($ext)) {
                        $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('mgs_brand/');
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        if ($fileName) {
                            $data['brand']['small_image'] = 'mgs_brand' . $fileName;
                        }
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        return $this->redirectToEdit($model, $data);
                    }
                } else {
                    if (isset($data['small_image']['delete']) && $data['small_image']['delete'] == 1) {
                        $data['brand']['small_image'] = '';
                    } else {
                        unset($data['small_image']);
                    }
                }
                if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    if ($uploader->checkAllowedExtension($ext)) {
                        $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('mgs_brand/');
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        if ($fileName) {
                            $data['brand']['image'] = 'mgs_brand' . $fileName;
                        }
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        return $this->redirectToEdit($model, $data);
                    }
                } else {
                    if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                        $data['brand']['image'] = '';
                    } else {
                        unset($data['image']);
                    }
                }
                if (!isset($data['brand']['url_key']) || $data['brand']['url_key'] == '') {
                    $data['brand']['url_key'] = $this->_objectManager->get('Magento\Catalog\Model\Product\Url')->formatUrlKey($data['brand']['name']);
                }
                $model->setData($data['brand'])
                    ->setId($this->getRequest()->getParam('brand_id'));
                $model->setStores($data['stores']);
                if (isset($data['product_ids']) && ($data['product_ids'] != '' || $data['product_ids'] != null)) {
                    $productIds = $jsHelper->decodeGridSerializedInput($data['product_ids']);
                    $this->removeBrandProduct($model->getId(),$productIds);
                    $model->setProductIds($productIds);
                } else {
                    if (isset($data['product_ids']) && ($data['product_ids'] == '' || $data['product_ids'] == null)) {
                        $model->setProductIds(array());
                    }
                }
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $brand = $this->_objectManager->create('MGS\Brand\Model\Brand')->load($model->getId());
                $optionId = $this->saveOption('mgs_brand', $brand->getName(), (int)$brand->getOptionId());
                $brand->setOptionId($optionId);
                $brand->save();
                if (isset($data['product_ids']) && ($data['product_ids'] != '' || $data['product_ids'] != null)) {
                    $productIdsInBrand = array();
                    $productCollection = $this->_objectManager->create('MGS\Brand\Model\Product')->getCollection();
                    $productCollection->addFieldToFilter('brand_id', ['eq' => $brand->getId()]);
                    foreach ($productCollection as $product) {
                        $productIdsInBrand[] = (int)$product->getProductId();
                        $product->delete();
                    }
                    $arrAttributeEmpty = ['mgs_brand'=>''];
                    //print_r($productIdsInBrand); die();
                    if(count($productIdsInBrand)){
                        $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                            ->updateAttributes($productIdsInBrand,  $arrAttributeEmpty, 0);
                    }



                    $productIds = $jsHelper->decodeGridSerializedInput($data['product_ids']);
                    $productIdsInput = array();
                    foreach ($productIds as $key => $value) {
                        $productIdsInput[] = (int)$key;
                    }

                    if(count($productIdsInput)>0){

                        $arrAttributeData = ['mgs_brand'=>$optionId];
                        foreach($productIdsInput as $productId){
                            $brandProduct = $this->_objectManager->create('MGS\Brand\Model\Product');
                            $brandProduct->setBrandId($brand->getId())->setProductId($productId)->save();
                        }



                        $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                            ->updateAttributes($productIdsInput,  $arrAttributeData, 0);
                    }

                }

                if (isset($data['product_ids']) && ($data['product_ids'] == '' || $data['product_ids'] == null)) {
                    $arrProductIds = [];
                    $productCollection = $this->_objectManager->create('MGS\Brand\Model\Product')->getCollection();
                    $productCollection->addFieldToFilter('brand_id', ['eq' => $brand->getId()]);
                    foreach ($productCollection as $product) {
                        $arrProductIds[] = $product->getProductId();
                        $product->delete();
                    }
                    $arrAttributeEmpty = ['mgs_brand'=>''];
                    $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                        ->updateAttributes($arrProductIds,  $arrAttributeEmpty, 0);
                }


                $this->messageManager->addSuccess(__('You saved the brand.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('brand/*/edit', ['brand_id' => $model->getId()]);
                    return;
                }
                $this->_redirect('brand/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('brand_id');
                if (!empty($id)) {
                    $this->_redirect('brand/*/edit', ['brand_id' => $id]);
                } else {
                    $this->_redirect('brand/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the brand data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('brand/*/edit', ['brand_id' => $this->getRequest()->getParam('brand_id')]);
                return;
            }
        }
        $this->_redirect('brand/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::save_brand');
    }

    protected function saveOption($attributeCode, $label, $value)
    {
        $attribute = $this->_objectManager->create('Magento\Catalog\Api\ProductAttributeRepositoryInterface')->get($attributeCode);
        $options = $attribute->getOptions();
        $values = array();
        foreach ($options as $option) {
            if ($option->getValue() != '') {
                $values[] = (int)$option->getValue();
            }
        }
        if (!in_array($value, $values)) {
            return $this->addAttributeOption(
                [
                    'attribute_id' => $attribute->getAttributeId(),
                    'order' => [0],
                    'value' => [
                        [
                            0 => $label,
                        ],
                    ],
                ]
            );
        } else {
            return $this->updateAttributeOption($value, $label);
        }
    }

    protected function addAttributeOption($option)
    {
        $oId = 0;
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
        $optionTable = $setup->getTable('eav_attribute_option');
        $optionValueTable = $setup->getTable('eav_attribute_option_value');
        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = (int)$optionId;
                if (!$intOptionId) {
                    $data = [
                        'attribute_id' => $option['attribute_id'],
                        'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    ];
                    $setup->getConnection()->insert($optionTable, $data);
                    $intOptionId = $setup->getConnection()->lastInsertId($optionTable);
                    $oId = $intOptionId;
                }
                $condition = ['option_id =?' => $intOptionId];
                $setup->getConnection()->delete($optionValueTable, $condition);
                foreach ($values as $storeId => $value) {
                    $data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                    $setup->getConnection()->insert($optionValueTable, $data);
                }
            }
        }
        return $oId;
    }

    protected function updateAttributeOption($optionId, $value)
    {
        $oId = $optionId;
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
        $optionValueTable = $setup->getTable('eav_attribute_option_value');
        $data = ['value' => $value];
        $setup->getConnection()->update($optionValueTable, $data, ['option_id=?' => $optionId]);
        return $oId;
    }

    protected function removeBrandProduct($brandID, $productIds){
        $productCollection = $this->_objectManager->create('MGS\Brand\Model\Product')->getCollection();
        $productCollection->addFieldToFilter('brand_id', ['eq' => $brandID]);
        foreach ($productCollection as $product) {
            $_check = 'false';
            foreach($productIds as $key => $value){
                if($product->getProductId() == $key){
                    $_check = 'true';
                    break;
                }
            }
            if($_check == 'false'){
                $_listStore = $this->_objectManager->create('Magento\Store\Api\StoreRepositoryInterface');
                $stores = $_listStore->getList();
                $_action = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Action');
                foreach ($stores as $store) {
                    $_action->updateAttributes([$product->getProductId()], ['mgs_brand' => ''], $store->getId());
                }
            }
        }
        return true;
    }
}
