<?php

namespace MGS\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    protected $cate;
    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ImageUploader $imageUploader
     */

    protected $categoryFactory;
    protected $store;
    public function __construct(

        Context $context,
        \MGS\Blog\Model\Resource\Category $test,
        \MGS\Blog\Model\CategoryFactory $category,
        DataPersistorInterface $dataPersistor,
        Store $store
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->store = $store;
        $this->cate = $test;
        $this->categoryFactory = $category->create();
        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
             try {
                if (isset($data['general']['category_id'])) {
                        $id= $data['general']['category_id'];
                    } 
                else $id = null;  
                $data['title']=$data['general']['title'];
                $data['url_key']=$data['general']['url_key'];
                $data['sort_order']=$data['general']['sort_order'];
                $data['status']=$data['general']['status'];
                $data['meta_keywords'] = $data['meta']['meta_keywords'];
                $data['meta_description'] = $data['meta']['meta_description'];
                $data['store_id'] = $data['general']['store_id'];
                        
                if ($data['store_id'] == null) {
                    $model = $this->categoryFactory->load($id);
                    if (!$id) {
                        $model->setData($data);
                        $model->save();
                        $idCate=  $model->getId();
                        $this->saveCategoryStore($idCate);
                    } else {
                        $model->addData($data);
                        $model->save();
                    }
                    $this->messageManager->addSuccess(__('You saved the Category.'));
                    $this->dataPersistor->set('category', $data);
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId()] );
                }
                else {

                    $table = $this->cate->getTable('mgs_blog_category_update');
                    $connection = $this->cate->getConnection();
                    $use_dafault = $data['use_default'];

                    foreach( $use_dafault as $key => $value) {
                        $update=[];
                        $scope_id = $data['general']['store_id'];
                        $sql = "DELETE FROM $table 
                                       WHERE category_id= $id 
                                       AND scope_id= $scope_id 
                                       AND field='$key'";  
                        $connection->query($sql);   
                                
                        if ($value == 0) {  
                            if($key != 'meta_description' && $key != 'meta_keywords')
                                $update[]= ['category_id' => $id,
                                            'scope_id'  => $data['general']['store_id'],
                                            'field'     => $key,
                                            'value'     => $data['general'][$key]
                                            ];
                            else 
                                $update[]= ['category_id' => $id,
                                            'scope_id'  => $data['general']['store_id'],
                                            'field'     => $key,
                                            'value'     => $data['meta'][$key]
                                            ];                 
                            $this->cate->getConnection()->insertMultiple($table, $update);
                        }
                        if($value == 1 && $key == 'url_key') {
                            $model = $this->categoryFactory->load($id);
                            $update[]= ['category_id' => $id,
                                            'scope_id'  => $data['general']['store_id'],
                                            'field'     => 'url_key',
                                            'value'     => $model->getUrlKey()
                                            ];   
                            $this->cate->getConnection()->insertMultiple($table, $update);
                        }
                    }
                    $this->saveCategoryStoreUpdate($id, $data);
                }
                $this->dataPersistor->set('category', $data);
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $id,'store'=> $data['store_id']] );
               
            } catch (LocalizedException $e) {
                 $this->messageManager->addError($e->getMessage());
                 $id = (int)$this->getRequest()->getParam('category_id');
                 if (!empty($id)) {
                    $this->_redirect('blog/category/new', ['category_id' => $id]);
                 } else {
                     $this->_redirect('blog/category/new');
                 }
                return;
              } catch (\Exception $e) {
                 $this->messageManager->addError(
                     __('Something went wrong while saving the category data. Please review the error log.')
                 );
                 $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                 $this->_redirect('blog/category/edit', ['category_id' => $this->getRequest()->getParam('category_id')]);
                 return;
            }
            return $resultRedirect->setPath('*/*/');
        }
    }

    public function getStoreIds($categoryId)
    {
        $connection = $this->cate->getConnection();
        $select = $connection->select()->from(
            $this->cate->getTable('mgs_blog_category_store'),
            'store_id'
        )->where(
            'category_id = ?',
            (int)$categoryId
        );
        return $connection->fetchCol($select);
    }

    public function saveCategoryStore($id) {
        $table = $this->cate->getTable('mgs_blog_category_store');
        $storeIds = $this->store->getCollection();
        foreach($storeIds as $items) {
            if($items->getStoreId() != 0) {
            $data[] = ['category_id' => $id, 'store_id' => (int)$items->getStoreId()];
            }
        }
        $this->cate->getConnection()->insertMultiple($table, $data);
    }

    public function saveCategoryStoreUpdate($id, $data) {
        $table2 = $this->cate->getTable('mgs_blog_category_store');
        $oldStores = $this->getStoreIds($id);
        foreach($oldStores as $key => $value) {
            if($value == 0) {
                $this->saveCategoryStore($id);
                $dele = ['category_id = ?' => $id, 'store_id IN (?)' => 0];
                $this->cate->getConnection()->delete($table2, $dele);
                if($data['status'] == 0) {
                    $deleteCate = ['category_id = ?' => $id, 'store_id IN (?)' => $data['store_id']];
                    $this->cate->getConnection()->delete($table2, $deleteCate);
                }
                $oldStores = $this->getStoreIds($id);
                break;
            }
        }
        if($data['status'] == 0) {
            $catedelete = (array) $data['store_id'];
            $delete = array_intersect($catedelete, $oldStores);
        }
        if($data['status'] == 1) {
            $newStores=(array)$data['store_id'];
            $insert = array_diff($newStores, $oldStores);
        }
        
        $cateId = $data['store_id'];
   
        if (isset($insert) && $insert) {
            $dataupdate = ['category_id' => $id, 'store_id' => $cateId];
            $this->cate->getConnection()->insertMultiple($table2, $dataupdate);  
        }
      
        if(isset($delete) && $delete){
            $where = ['category_id = ?' => $id, 'store_id IN (?)' => $cateId];
            $this->cate->getConnection()->delete($table2, $where);
        }
    }

    public function lookupStoreIds($postId)
    {
        $connection = $this->cate->getConnection();
        $select = $connection->select()->from(
            $this->cate->getTable('mgs_blog_category_update'),
            'scope',
        )->where(
            'category_id = ?',
            (int)$postId
        );
        return $connection->fetchRow($select);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::save_category');
    }
}
