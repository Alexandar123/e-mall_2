<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use MGS\Mmegamenu\Model\MmegamenuFactory; 
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;



class Save extends \Magento\Backend\App\Action

{
    protected $megamenu;

    protected $megamenuFactory;

    protected $store;

    protected $dataPersistor;

    protected $subcategory;

    protected $temp =[];

    public function __construct(
        Context $context,
        MmegamenuFactory $megamenuFactory, 
        \MGS\Mmegamenu\Model\ResourceModel\Mmegamenu $megamenu,
        Store $store,
        \Magento\Catalog\Model\CategoryFactory $subcategory,
        DataPersistorInterface  $dataPersistor
    )
    {
        parent::__construct($context);
        $this->megamenuFactory = $megamenuFactory->create();
        $this->store = $store;
        $this->megamenu = $megamenu;
        $this->subcategory = $subcategory->create();
        $this->dataPersistor = $dataPersistor;
    }
    public function execute()
    {
        $check = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if(isset($data['general']['menu_type']) && isset($data['menu_type']) && 
                $data['menu_type'] != $data['general']['menu_type']) 
            $check = 1;
            $data['sub_category'] = [];
            try {
                if (isset($data['megamenu_id'])) {
                    $id = $data['megamenu_id'];
                } else {
                    $id = null;
                }
                $data['title'] = $data['general']['title'];
                $data['menu_type'] = $data['general']['menu_type'];
                if (isset($data['general']['url'])) {
                    $data['url'] = $data['general']['url'];
                }
                if (isset($data['general']['position'])) {
                    $data['position']= $data['general']['position'];
                }

                $data['columns']= $data['general']['columns'];

                if (isset($data['general']['special_class'])) {
                    $data['special_class']= $data['general']['special_class'];
                }

                if (isset($data['general']['html_label'])) {
                    $data['html_label']= $data['general']['html_label'];
                }

                $data['status'] = $data['general']['status'];

                $data['stores'] = $data['general']['stores'];
                $data['parent_id'] = $data['general']['parent_id'];
                $data['store_id'] = $data['general']['store_id'];

                if (isset($data['category']['category_id']) && $data['category']['category_id'] != null) {
                    $data['category_id'] = $data['category']['category_id'];
                    $this->categoryLoop($data['category_id']);
                    $data['sub_category'] = $this->temp;
                    $data['sub_category_ids'] = implode(',', $data['sub_category']);
                }
                if (isset($data['static_contents']['top_content']) && $data['static_contents']['top_content'] != null) {
                    $data['top_content']= $data['static_contents']['top_content'];
                }

                if (isset($data['static_contents']['bottom_content']) && $data['static_contents']['bottom_content'] != null) {
                    $data['bottom_content']= $data['static_contents']['bottom_content'];
                }

                if (isset($data['static_contents']['left_content']) && $data['static_contents']['left_content'] != null) {
                    $data['left_content']= $data['static_contents']['left_content'];
                }

                if (isset($data['static_contents']['right_content']) && $data['static_contents']['right_content'] != null) {
                    $data['right_content']= $data['static_contents']['right_content'];
                }
                    
                if (isset($data['static_contentt']['static_content']) && $data['static_contentt']['static_content'] != null) {
                    $data['static_content']= $data['static_contentt']['static_content'];
                }
                $data['left_col']= $data['static_contents']['left_col'];
                $data['right_col']= $data['static_contents']['right_col'];
               

                // constraint

                if ($data['menu_type'] == 2) {
                    $data['category_id'] = '';
                    $data['sub_category'] = $data['top_content'] = $data['bottom_content'] = '';
                } else {
                    $data['static_content'] = '';
                }

                if (isset($data['category_id']) && $data['category_id'] != null) {
                    $data['url'] ='';
                }
              
                if (!$data['store_id']) {
                    $model = $this->_objectManager->create('MGS\Mmegamenu\Model\Mmegamenu')->load($id);
                    
                    if (!$id) {
                        $model->setData($data);
                        $model->save();
                    } else {
                       if($check == 1){
                            $connection = $this->megamenu->getConnection();
                            $connection->query($this->deleteTableUpdate($id));
                        }
                        $model->addData($data);
                        $model->save();
                    }
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(),'store'=> $data['store_id']]);

                } else {
                    $table = $this->megamenu->getTable('mgs_megamenu_update');
                    $connection = $this->megamenu->getConnection();
                    $use_dafault = $data['use_default'];
                    if ($data['status'] == 0) {
                        $connection->query($this->deleteColumns($id, $data['store_id']));
                        if ($this->checkAllStore($connection, $id)) {
                            $connection->query($this->deleteColumns($id,0));
                            $this->afterSavetoStore($id, $data['store_id']);
                        }
                        $connection->query($this->deleteColumns($id,$data['store_id']));
                    }
                    foreach ($use_dafault as $key => $value) {
                        $update=[];
                        if ($value == 0) {
                            $scope_id = $data['store_id'];
                            $sql = $this->deletebeforeSave($table, $id, $scope_id, $key);
                            $connection->query($sql);
                            $update[]= ['megamenu_id' => $id,
                                            'scope_id'  => $data['store_id'],
                                            'field'     => $key,
                                            'value' => $data['general'][$key]
                                            ];
                            $this->megamenu->getConnection()->insertMultiple($table, $update);
                        }

                        if ($value == 1) {
                            $scope_id = $data['store_id'];
                            $sql = $this->deletebeforeSave($table, $id, $scope_id, $key);
                            $connection->query($sql);
                        }
                    }
                }
                $this->dataPersistor->set('megamenu', $data);
                 return $resultRedirect->setPath('*/*/edit', ['id' => $id,'store'=> $data['store_id']]);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('admin/megamenu/edit', ['id' => $id]);
                } else {
                    $this->_redirect('admin/megamenu/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the post data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('admin/megamenu/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
            return $resultRedirect->setPath('*/*/');
        }
    }

    function categoryLoop($id){
        $categories =  $this->subcategory->load($id);
        if($categories->hasChildren()){
            $subcategories = explode(',', $categories->getChildren());
            foreach ($subcategories as $category) {
                $this->temp[] = $category;
                $subcategory = $this->subcategory->load($category);
                if($subcategory->hasChildren()){ $this->categoryLoop($category); }
            }
        }
    }

    function deletebeforeSave($table, $id, $scope_id, $key) {
        $sql = "DELETE FROM $table 
                    WHERE megamenu_id = $id 
                    AND scope_id = $scope_id 
                    AND field = '$key'"; 
        return $sql;
    }

    function afterSavetoStore($megamenu_id, $deleteStore){
        $table = $this->megamenu->getTable('mgs_megamenu_store');
        $storeIds = $this->store->getCollection();
        foreach($storeIds as $items) {
            if($items->getStoreId() == 0 || $items->getStoreId()== $deleteStore) continue;
            $data[] = ['megamenu_id' => (int)$megamenu_id, 'store_id' => (int)$items->getStoreId()];
        }
        $this->megamenu->getConnection()->insertMultiple($table, $data);

    }

    function deleteColumns($megamenu_id, $store_id) {
        $table = $this->megamenu->getTable('mgs_megamenu_store');
        $sql = "DELETE FROM $table 
                    WHERE megamenu_id = $megamenu_id 
                    AND   store_id = $store_id ";
        return $sql;
    }
    
    protected function deleteTableUpdate($megamenu_id) {
        $table = $this->megamenu->getTable('mgs_megamenu_update');
        $sql = "DELETE FROM $table 
                       WHERE megamenu_id = $megamenu_id" ;
        return $sql;
    }

    function checkAllStore($connection,$megamenu_id) {
        $select = $connection->select()->from(
            $this->megamenu->getTable('mgs_megamenu_store'),
            '*',
            )->where(
                'megamenu_id = ?',
                (int)$megamenu_id
            )->where(
                'store_id = ?',
                0
            );
        return $connection->fetchAssoc($select);
    }
}