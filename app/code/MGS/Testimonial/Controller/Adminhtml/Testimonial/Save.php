<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;
use MGS\Testimonial\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use MGS\Mmegamenu\Helper\Data;
use Magento\Framework\View\LayoutFactory;
use MGS\Testimonial\Model\ResourceModel\Testimonial;
use \Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Store\Model\Store;



class Save extends \MGS\Testimonial\Controller\Adminhtml\Testimonial
{
      protected $imageUploader;
      protected $resource;
      protected $store;
      protected $dataPersistor;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Data $viewHelper,
        LayoutFactory $layoutFactory,
        ResultLayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        ImageUploader $imageUploader,
        Testimonial $resource,
        Store $store
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->imageUploader = $imageUploader;
        $this->resource = $resource;
        $this->store = $store;
        parent::__construct($context,$coreRegistry,$fileFactory, $viewHelper, $layoutFactory, $resultLayoutFactory, $resultPageFactory);
    }
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('MGS\Testimonial\Model\Testimonial')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
			if(isset($data['avatar'][0]['name'])){
				$data['avatar'] = $data['avatar'][0]['name'];
			}

            $model->setData($data);
            try {
                if (!$data['stores']) {
                    $model->save();
                    $this->messageManager->addSuccess(__('You saved the item.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(),'store'=> $data['store_id']]);
                }
                else {
                    $connection = $this->resource->getConnection();
                    $table = $this->resource->getTable('mgs_testimonial_update');
                    $use_dafault = $data['use_default'];
                    $id = $data['testimonial_id'];
                    $scope_id = $data['stores'];
                    $update = [];
                    if($data['status']== 0) {
                        $connection->query($this->deleteColumns($id, $scope_id));
                        if ($this->checkAllStore($connection, $id)) {
                            $connection->query($this->deleteColumns($id, 0));
                            $this->afterSavetoStore($id, $scope_id);
                        }
                    }
                    foreach($use_dafault as $key => $value) {
                        if($value == 0) {
                            $sql = $this->deletebeforeSave($table, $id, $scope_id,$key);
                            $connection->query($sql);
                            $update[]= ['testimonial_id' => $id,
                                        'scope_id'  =>  $scope_id,
                                        'field'     => $key,
                                        'value' => $data[$key]
                                        ];
                        }
                        if($value == 1) {
                            $sql = $this->deletebeforeSave($table, $id, $scope_id,$key);
                            $connection->query($sql);
                        }
                    }
                    $this->resource->getConnection()->insertMultiple($table, $update);
                    $this->messageManager->addSuccess(__('You saved the item.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id,'store'=> $data['store_id']]);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                return $resultRedirect->setPath('*/*/index', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
    function deletebeforeSave($table, $id, $scope_id, $key) {
        $sql = "DELETE FROM $table
                       WHERE testimonial_id = $id
                       AND scope_id = $scope_id
                       AND field = '$key'";
        return $sql;
    }
    function deleteColumns($tesimomial_id, $scope_id) {
        $table = $this->resource->getTable('mgs_testimonial_store');
        $sql = "DELETE FROM $table
                       WHERE testimonial_id = $tesimomial_id
                       AND   store_id = $scope_id ";
        return $sql;
    }

    function afterSavetoStore($tesimomial_id, $deleteStore){
        $table = $this->resource->getTable('mgs_testimonial_store');
        $storeIds = $this->store->getCollection();
        foreach($storeIds as $items) {
            if($items->getStoreId() == 0 || $items->getStoreId()== $deleteStore) continue;
            $data[] = ['testimonial_id' => (int)$tesimomial_id, 'store_id' => (int)$items->getStoreId()];
        }
        $this->resource->getConnection()->insertMultiple($table, $data);
    }

    function checkAllStore($connection, $tesimomial_id)
    {

        $select = $connection->select()->from(
            $this->resource->getTable('mgs_testimonial_store'),
            '*',
        )->where(
                'testimonial_id = ?',
                (int)$tesimomial_id
            )->where(
                'store_id = ?',
                0
            );
        return $connection->fetchAssoc($select);
    }
}
