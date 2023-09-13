<?php
/**
 * Data
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Plugin\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\AttributeDataFactory;
use MGS\OSCheckout\Helper\Data as HelperData;

class Data extends \Magento\Eav\Model\Validator\Attribute\Data
{
    /**
     * @var HelperData
     */
    protected $dataHelper;

    /**
     * Data constructor.
     * @param AttributeDataFactory $attrDataFactory
     * @param HelperData $dataHelper
     */
    public function __construct(
        AttributeDataFactory $attrDataFactory,
        HelperData           $dataHelper
    )
    {
        parent::__construct($attrDataFactory);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Eav\Model\Validator\Attribute\Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsValid(\Magento\Eav\Model\Validator\Attribute\Data $subject, $result)
    {
        if ($this->dataHelper->isFlagMethodRegister()) {
            $subject->_messages = [];

            return count($subject->_messages) == 0;
        }

        return $result;
    }
}
