<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model\SearchList;

/**
 * Class Toolbar
 */
class Toolbar
{
    /**
     * GET parameter page variable name
     */
    const PAGE_PARM_NAME = 'p';

    /**
     * Searchs per page limit order cookie name
     */
    const LIMIT_PARAM_NAME = 'list_limit';

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     */
    public function getLimit()
    {
        return $this->request->getParam(self::LIMIT_PARAM_NAME);
    }
    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = (int) $this->request->getParam(self::PAGE_PARM_NAME);
        return $page ? $page : 1;
    }
}
