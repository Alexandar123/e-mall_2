<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Controller;

use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use \MGS\InstantSearch\Model\Search;
use Magento\Framework\View\Result\PageFactory;
use \MGS\InstantSearch\Helper\Data;
use \Magento\Framework\Exception\NotFoundException;
abstract class AbstractSearch extends \Magento\Framework\App\Action\Action
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
    protected $_queryFactory;

    /**
     * @var Search
     */
    protected $_search;

    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param PageFactory $resultPageFactory
     * @param Data $inSearchHelper
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Search $search,
        PageFactory $resultPageFactory,
        Data $inSearchHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->_search = $search;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_inSearchHelper = $inSearchHelper;
        parent::__construct($context);
        $this->_isAllowed();
        $this->_isAllowedType();
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
    }
}