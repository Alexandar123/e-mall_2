<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Controller\Ajax;

use MGS\InstantSearch\Controller\AbstractSearch;
use \Magento\Framework\Controller\ResultFactory;
class Result extends AbstractSearch
{
    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
    	$responseData = [];
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());
        if ($query->getQueryText() != '') {
        	$query->setId(0)->setIsActive(1)->setIsProcessed(1);
            $responseData['result'] = $this->_search->getData();
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
    /**
     *
     * @return void
     */
    public function _isAllowedType(){}
}