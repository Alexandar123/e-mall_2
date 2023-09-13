<?php
/**
 * Comment
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Block\LayoutOnestepCheckout\Order\View;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Comment extends Template
{
    /**
     * @type Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context  $context,
        Registry $registry,
        array    $data = []
    ) {
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getOrderComment()
    {
        if ($order = $this->getOrder()) {
            return $order->getOnestepcheckoutOrderComment();
        }
        return '';
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return string
     */
    public function getOrderDateTime()
    {
        if ($order = $this->getOrder()) {
            return $order->getOnestepcheckoutDeliveryTime();
        }
        return '';
    }
}
