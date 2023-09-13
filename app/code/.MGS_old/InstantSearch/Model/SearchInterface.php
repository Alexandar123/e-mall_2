<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model;
/**
 * @api
 */
interface SearchInterface
{
	/**
     * Retrieve selected in config data
     *
     * @return array
     */
    public function getResponseData();
}