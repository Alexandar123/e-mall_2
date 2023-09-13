<?php

namespace MGS\Blog\Controller\Adminhtml\Post;

use Magento\Framework\App\Filesystem\DirectoryList;
use MGS\Blog\Controller\Adminhtml\Blog;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use MGS\Blog\Model\ImageUploader;
use MGS\Blog\Model\PostFactory; 
use MGS\Blog\Model\Resource\Post;
use Magento\Store\Model\Store;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action
{
    protected $post;

    protected $imageUploader;

    protected $postFactory;

    protected $store;

    protected $dataPersistor;

    public function __construct(
        Context $context,
        \MGS\Blog\Model\Resource\Post $post,
        PostFactory $postFactory,
        ImageUploader $imageUploader,
        Store $store,
        DataPersistorInterface $dataPersistor
    )
    {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->post = $post; 
        $this->store = $store;
        $this->postFactory = $postFactory->create();
        $this->dataPersistor = $dataPersistor;

    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                if (isset($data['general']['post_id'])) {
                    $id = $data['general']['post_id'];
                } else {
                    $id = null;
                }
                $data['title'] = $data['general']['title'];
                $data['url_key'] = $data['general']['url_key'];
                $data['short_content'] = $data['general']['short_content'];
                $data['content'] = $data['general']['content'];
                $data['tags'] = $data['general']['tags'];
                $data['status'] = $data['general']['status'];
                $data['video_thumb_id'] = $data['general']['video_thumb_id'];
                $data['video_big_id'] = $data['general']['video_big_id'];
                $data['thumb_type']  = $data['general']['thumb_type'];
                $data['video_thumb_type'] = $data['general']['video_thumb_type'];
                $data['video_big_type'] = $data['general']['video_big_type'];
                $data['image_type'] = $data['general']['image_type'];
                $data['meta_keywords'] = $data['meta']['meta_keywords'];
                $data['meta_description'] = $data['meta']['meta_description'];
                $data['store_id'] = $data['general']['store_id'];
                $data['updated_by_user'] = $data['general']['updated_by_user'];

                if(isset($data['general']['updated_by_user']) && $data['general']['updated_by_user'] != null )
                {
                    $data['updated_by_user'] = $data['general']['updated_by_user'];
                }

                if(isset($data['general']['published_at']) && $data['general']['published_at'] != null )
                {
                    $data['published_at'] = $data['general']['published_at'];
                }

                if(isset($data['thumb_type']) && $data['thumb_type']=='image')
                {
                    $data['video_thumb_id'] = null;
                    $data['video_thumb_type'] = null;
                }
                if(isset($data['thumb_type']) && $data['thumb_type']=='video')
                {
                    $data['general']['thumbnail'] = null;
                }

                if(isset($data['image_type']) && $data['image_type']=='image')
                {
                    $data['video_big_type'] = null;
                    $data['video_big_id'] = null;
                }
                if(isset($data['image_type']) && $data['image_type']=='video')
                {
                    $data['general']['image'] = null;
                }
                if (isset($data['general']['thumbnail']) && isset($data['general']['thumbnail']['0']['name'])) {
                    $data['thumbnail'] = $data['general']['thumbnail']['0']['name'];
                }
                if (isset($data['general']['image'])&& isset($data['general']['image']['0']['name'])) {
                    $data['image']= $data['general']['image']['0']['name'];
                }
                if (isset($data['general']['categories']) && $data['general']['categories'] != null) {
                    $data['categories'] = $this->getCategory($data['general']['categories']);
                }
                if (!$data['store_id']) {
                    $model = $this->postFactory->load($id);
                    $userData = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getData();
                    if (isset($data['user']) && $data['user']) {
                        $data['updated_by_user']= $userData['username'];
                    } else {
                        $data['user']= $userData['username'];
                    }
                   
                    if (!$id) {
                        if(isset($data['general']['published_at']) && $data['general']['published_at'] != null )
                        {
                            $data['published_at'] = $data['general']['published_at'];
                        } else {
                            $data['published_at'] = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate();
                        }
                        $model->setData($data);
                        $model->save();
                        $idPost = $model->getId();
                        $this->savePostStore($idPost);
                    } else {
                        $model->addData($data);
                        $model->save();
                    }

                    $this->messageManager->addSuccess(__('You saved the post.'));
                    $this->dataPersistor->set('post', $data);
                    return $resultRedirect->setPath('*/*/edit', ['post_id' => $model->getId()] );
                }
                else {
                    $table = $this->post->getTable('mgs_blog_post_update');
                    $connection = $this->post->getConnection();
                    $use_dafault = $data['use_default'];

                    foreach($use_dafault as $key => $value) {
                        $update=[];
                        if ($value == 0) {
                            $scope_id = $data['store_id'];
                            $sql = $this->deletebeforeSave($table, $id, $scope_id, $key);
                            $connection->query($sql);
                            $update[]= ['post_id' => $id,
                                        'scope_id'  => $data['store_id'],
                                        'field'     => $key,
                                         'value' => $data['general'][$key]
                                        ];
                            $this->post->getConnection()->insertMultiple($table, $update);
                        }

                        if($value == 1 && $key != 'url_key') {
                            $scope_id = $data['store_id'];
                            $sql = $this->deletebeforeSave($table, $id, $scope_id, $key); 
                            $connection->query($sql);
                        }

                        if($value == 1 && $key == 'url_key') {
                            $model = $this->postFactory->load($id);
                            $updateurl[]= ['post_id' => $id,
                                    'scope_id'  => $data['store_id'],
                                    'field'     => 'url_key',
                                    'value' => $model->getUrlKey()
                                ];
                            $this->post->getConnection()->insertMultiple($table, $updateurl);
                        }
                    }
                    $this->savePostStoreUpdate($id, $data);
                }
                $this->dataPersistor->set('post', $data);
                return $resultRedirect->setPath('*/*/edit', ['post_id' => $id,'store'=> $data['store_id']] );
            }
            catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('post_id');
                if (!empty($id)) {
                    $this->_redirect('blog/post/edit', ['post_id' => $id]);
                } else {
                    $this->_redirect('blog/post/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the post data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('blog/post/edit', ['post_id' => $this->getRequest()->getParam('post_id')]);
                return;
            }
            return $resultRedirect->setPath('*/*/');
        }
    }

    public function savePostStore($id)
    {
        $table = $this->post->getTable('mgs_blog_post_store');
        $storeIds = $this->store->getCollection();
        foreach($storeIds as $items) {
            if($items->getStoreId() == 0) continue;
            $data[] = ['post_id' => (int)$id, 'store_id' => (int)$items->getStoreId()];
        }
        $this->post->getConnection()->insertMultiple($table, $data);
    }

    public function savePostStoreUpdate($id, $data) {
        $table2 = $this->post->getTable('mgs_blog_post_store');
        $oldStores = $this->getStoreIds($id);
        foreach($oldStores as $key => $value) {
            if($value == 0) {
                $this->savePostStore($id);
                $dele = ['post_id = ?' => $id, 'store_id IN (?)' => 0];
                $this->post->getConnection()->delete($table2, $dele);
                if($data['status'] == 0) {
                    $deleteCate = ['post_id = ?' => $id, 'store_id IN (?)' => $data['store_id']];
                    $this->post->getConnection()->delete($table2, $deleteCate);
                }
                $oldStores = $this->getStoreIds($id);
                break;
            }
        }
        if($data['status'] == 0) {
            $catedelete = (array) $data['store_id'];
            $delete = array_intersect($catedelete, $oldStores);
        }
        else {
            $newStores = (array)$data['store_id'];
            $insert = array_diff($newStores, $oldStores);
        }
        $cateId = $data['store_id'];
       
        if (isset($insert) && $insert) {
            $dataupdate = ['post_id' => $id, 'store_id' => $cateId];
            $this->post->getConnection()->insertMultiple($table2, $dataupdate);  
        }

        if(isset($delete) && $delete){
            $where = ['post_id = ?' => $id, 'store_id IN (?)' => $cateId];
        $this->post->getConnection()->delete($table2, $where);
        }
    }

    public function getCategory($data) {
        $temp =[];
        foreach($data as $key => $value) {
            $temp[] = $value;
        }
        return $temp;
    }

    public function getStoreIds($postId)
    {
        $connection = $this->post->getConnection();
        $select = $connection->select()->from(
            $this->post->getTable('mgs_blog_post_store'),
            'store_id'
        )->where(
            'post_id = ?',
            (int)$postId
        );
        return $connection->fetchCol($select);
    }

    public function deletebeforeSave($table, $id, $scope_id, $key) {
        $sql = "DELETE FROM $table 
                       WHERE post_id= $id 
                       AND scope_id= $scope_id 
                       AND field='$key'"; 
        return $sql;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::save_post');
    }
}
