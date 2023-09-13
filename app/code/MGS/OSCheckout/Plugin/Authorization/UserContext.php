<?php
/**
 * UserContext
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Plugin\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\Session;
use MGS\OSCheckout\Helper\Data;

class UserContext
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * UserContext constructor.
     * @param Data $dataHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        Data    $dataHelper,
        Session $checkoutSession
    )
    {
        $this->dataHelper = $dataHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param UserContextInterface $userContext
     * @param $result
     * @return int
     */
    public function afterGetUserType(UserContextInterface $userContext, $result)
    {
        if ($this->dataHelper->isFlagMethodRegister()) {
            return UserContextInterface::USER_TYPE_CUSTOMER;
        }

        return $result;
    }

    /**
     * @param UserContextInterface $userContext
     * @param $result
     * @return int
     */
    public function afterGetUserId(UserContextInterface $userContext, $result)
    {
        if ($this->dataHelper->isFlagMethodRegister()) {
            return $this->_checkoutSession->getQuote()->getCustomerId();
        }

        return $result;
    }
}
