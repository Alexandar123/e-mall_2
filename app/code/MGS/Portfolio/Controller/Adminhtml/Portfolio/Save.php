<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Portfolio\Controller\Adminhtml\Portfolio;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use MGS\Portfolio\Model\Portfolio\ImageUploader;
use MGS\Portfolio\Model\PortfolioFactory;
use MGS\Portfolio\Controller\Adminhtml\Portfolio;
use function PHPUnit\Framework\throwException;

class Save extends \MGS\Portfolio\Controller\Adminhtml\Portfolio
{
    protected $portfolio;

    protected $imageUploader;

    protected $portfolioFactory;

    protected $store;

    protected $dataPersistor;

    protected $subcategory;

    public function __construct(
        Context $context,
        \MGS\Portfolio\Model\ResourceModel\Portfolio $portfolio,
        PortfolioFactory $portfolioFactory,
        ImageUploader $imageUploader,
        Store $store,
        DataPersistorInterface $dataPersistor
    )
    {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->portfolio = $portfolio;
        $this->store = $store;
        $this->portfolioFactory = $portfolioFactory->create();
        $this->dataPersistor = $dataPersistor;
    }


    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                if (isset($data['portfolio_id'])) {
                    $id = $data['portfolio_id'];
                } else {
                    $id = null;
                }

                $base_image = [];

                for ($i = 0 ; $i < count($data['base_image']) ; $i++) {
                    $base_image[$i] = $data['base_image'][$i]['name'];
                }

                $portfolio = [
                    'name' => $data['name'],
                    'identifier' => $data['identifier'],
                    'thumbnail_image' => $data['thumbnail_image'][0]['name'],
                    'base_image' => implode(' ', $base_image),
                    'services' => $data['services'],
                    'skills' => $data['skills'],
                    'project_url' => $data['project_url'],
                    'client' => $data['client'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'portfolio_date' => $data['portfolio_date'],
                    'category_id' => $this->getCategory($data['category_id'])
                ];

                if (!$data['store_id']) {
                    $model = $this->portfolioFactory->load($id);
                    $userData = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getData();
                    if (isset($data['user']) && $data['user']) {
                        $data['updated_by_user']= $userData['username'];
                    } else {
                        $data['user']= $userData['username'];
                    }

                    if (!$id) {
                        $model->setData($portfolio);
                        $model->save();
                        $idPortfolio = $model->getId();
                        $this->savePostStore($idPortfolio);
                    } else {
                        $model->addData($portfolio);
                        $model->save();
                    }
                    $id = $model->getId();
                    $this->messageManager->addSuccess(__('You saved the portfolio.'));
                    $this->dataPersistor->set('portfolio', $data);
                    // return $resultRedirect->setPath('*/*');
                }
                else {
                    $table = $this->portfolio->getTable('mgs_portfolio_items_update');
                    $connection = $this->portfolio->getConnection();
                    $use_dafault = $data['use_default'];

                    try {
                        foreach($use_dafault as $key => $value) {
                            $update=[];
                            if ($value == 0) {
                                $scope_id = $data['store_id'];
                                $sql = $this->deletebeforeSave($table, $id, $scope_id, $key);
                                $connection->query($sql);
                                $update[]= ['portfolio_id' => $id,
                                    'scope_id'  => $data['store_id'],
                                    'field'     => $key,
                                    'value' => $data[$key]
                                ];
                                $this->portfolio->getConnection()->insertMultiple($table, $update);
                            }
                        }
                        $this->messageManager->addSuccessMessage(
                            __('Save portfolio successfully!')
                        );
                    } catch (\Exception $exception) {
                        throwException($exception);
                    }
                    $this->savePostStoreUpdate($id, $data);
                }
                $this->dataPersistor->set('portfolio', $data);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }

                return $resultRedirect->setPath('*/*/');
            }
            catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('portfolio_id');
                if (!empty($id)) {
                    $this->_redirect('adminhtml/portfolio/edit', ['portfolio_id' => $id]);
                } else {
                    $this->_redirect('adminhtml/portfolio/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the portfolio data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('*/*/');
                return;
            }
            return $resultRedirect->setPath('*/*/');
        }
    }

    public function savePostStore($id)
    {
        $table = $this->portfolio->getTable('mgs_portfolio_item_store');
        $storeIds = $this->store->getCollection();
        foreach($storeIds as $items) {
            if($items->getStoreId() == 0) continue;
            $data[] = ['portfolio_id' => (int)$id, 'store_id' => (int)$items->getStoreId()];
        }
        $this->portfolio->getConnection()->insertMultiple($table, $data);
    }

    public function savePostStoreUpdate($id, $data) {
        $table2 = $this->portfolio->getTable('mgs_portfolio_item_store');
        $oldStores = $this->getStoreIds($id);
        foreach($oldStores as $key => $value) {
            if($value == 0) {
                $this->savePostStore($id);
                $dele = ['portfolio_id = ?' => $id, 'store_id IN (?)' => 0];
                $this->portfolio->getConnection()->delete($table2, $dele);
                if($data['status'] == 0) {
                    $deleteCate = ['portfolio_id = ?' => $id, 'store_id IN (?)' => $data['store_id']];
                    $this->portfolio->getConnection()->delete($table2, $deleteCate);
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
            $dataupdate = ['portfolio_id' => $id, 'store_id' => $cateId];
            $this->portfolio->getConnection()->insertMultiple($table2, $dataupdate);
        }

        if(isset($delete) && $delete){
            $where = ['portfolio_id = ?' => $id, 'store_id IN (?)' => $cateId];
            $this->portfolio->getConnection()->delete($table2, $where);
        }
    }

    public function getCategory($data) {
        $temp =[];
        foreach($data as $key => $value) {
            $temp[] = $value;
        }
        return $temp;
    }

    public function getStoreIds($portfolioId)
    {
        $connection = $this->portfolio->getConnection();
        $select = $connection->select()->from(
            $this->portfolio->getTable('mgs_portfolio_item_store'),
            'store_id'
        )->where(
            'portfolio_id = ?',
            (int)$portfolioId
        );
        return $connection->fetchCol($select);
    }

    public function deletebeforeSave($table, $id, $scope_id, $key) {
        $sql = "DELETE FROM $table
                       WHERE portfolio_id= $id
                       AND scope_id= $scope_id
                       AND field='$key'";
        return $sql;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Portfolio::save_portfolio');
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

}
