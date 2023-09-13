<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Controller\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use \MGS\InstantSearch\Helper\Data;
use \Magento\Framework\Exception\NotFoundException;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param Data $inSearchHelper
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        Data $inSearchHelper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->_inSearchHelper = $inSearchHelper;
        $this->_isAllowed();
    }

    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
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
    public function _isAllowed()
    {
        if(!$this->_inSearchHelper->isEnableFrontend()){
            throw new NotFoundException(__('Parameter is incorrect.'));
        }
        if($this->_inSearchHelper->isModuleEnabled('MGS_Blog') && $this->_inSearchHelper->isOutputEnabled('MGS_Blog')){
            if(!$this->_inSearchHelper->isBlogSearch() && !$this->_inSearchHelper->isCategorySearch() 
                && !$this->_inSearchHelper->isCmsPageSearch() && !$this->_inSearchHelper->isProductSearch())
            {
                throw new NotFoundException(__('Parameter is incorrect.'));
            }
        }else{
            if(!$this->_inSearchHelper->isCategorySearch() && !$this->_inSearchHelper->isCmsPageSearch() 
                && !$this->_inSearchHelper->isProductSearch()){
                throw new NotFoundException(__('Parameter is incorrect.'));
            }
        }
    }
}