<?php
/**
 * Comment
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Block\Adminhtml\Order\View;

use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class Comment extends \Magento\Backend\Block\Template
{
    protected $order;

    public function __construct(
        \Magento\Sales\Model\Order              $order,
        \Magento\Backend\Block\Template\Context $context,
        array                                   $data = []
    ) {

        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * get Order comment
     */
    public function getOrderComment()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($order_id)->getOnestepcheckoutOrderComment();
        return $order;
    }

    /**
     * get Order Date Time
     */
    public function getOrderDateTime()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($order_id)->getOnestepcheckoutDeliveryTime();
        return $order;
    }
}
