<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Controller\Blog;

use MGS\InstantSearch\Controller\AbstractSearch;
use \Magento\Framework\Exception\NotFoundException;

class Result extends AbstractSearch
{

    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();

                if ($query->getRedirect()) {
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                }
            }

            $this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->checkNotes();

            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }

    /**
     *
     * @return void
     */
    public function _isAllowedType()
    {
        if($this->_inSearchHelper->isModuleEnabled('MGS_Blog') && $this->_inSearchHelper->isOutputEnabled('MGS_Blog')){
            if(!$this->_inSearchHelper->isBlogSearch())
            {
                throw new NotFoundException(__('Parameter is incorrect.'));
            }
        }else{
            throw new NotFoundException(__('Parameter is incorrect.'));
        }
    }
}