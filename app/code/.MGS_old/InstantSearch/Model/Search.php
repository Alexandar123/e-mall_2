<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model;
use \MGS\InstantSearch\Model\SearchFactory;
use \MGS\InstantSearch\Helper\Data;
/**
 * Search class returns needed search data
 */
class Search
{
	/**
     * @var SearchFactory
     */
    protected $_searchFactory;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * Search constructor.
     * @param SearchFactory $searchFactory
     */
    public function __construct(
        SearchFactory $searchFactory,
        Data $inSearchHelper
    ) {
        $this->_searchFactory = $searchFactory;
        $this->_inSearchHelper = $inSearchHelper;
    }
	/**
     * Retrieve suggested, product data
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        $searchType = $this->_inSearchHelper->getSearchType();
        if(count($searchType)){
        	foreach ($searchType as $key => $type) {
	        	$data[$type['type']] = $this->_searchFactory->create($type['type'])->getResponseData();
	        }
        }
        
        return $data;
    }
}