<?php
/**
 * AgreementsValidator
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use MGS\OSCheckout\Helper\Data;

class AgreementsValidator extends \Magento\CheckoutAgreements\Model\AgreementsValidator
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * AgreementsValidator constructor.
     * @param Data $dataHelper
     * @param null $list
     */
    public function __construct(
        Data $dataHelper,
             $list = null
    )
    {
        parent::__construct($list);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param array $agreementIds
     * @return bool
     */
    public function isValid($agreementIds = [])
    {
        if (!$this->dataHelper->isEnabledTOC()) {
            return true;
        }

        return parent::isValid($agreementIds);
    }
}
