<?php
/**
 * CheckoutLink
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Plugin;

use Magento\Framework\App\RequestInterface;
use MGS\OSCheckout\Helper\Data;

class CheckoutLink
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Link constructor.
     * @param RequestInterface $httpRequest
     * @param Data $dataHelper
     */
    public function __construct(
        RequestInterface $httpRequest,
        Data             $dataHelper
    )
    {
        $this->_request = $httpRequest;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Framework\Url $subject
     * @param $routePath
     * @param $routeParams
     * @return array|null
     */
    public function beforeGetUrl(\Magento\Framework\Url $subject, $routePath = null, $routeParams = null)
    {
        if ($this->dataHelper->isEnabled() && $routePath == 'checkout' && $this->_request->getFullActionName() != 'checkout_index_index') {
            return ['onestepcheckout', $routeParams];
        }

        return null;
    }
}
