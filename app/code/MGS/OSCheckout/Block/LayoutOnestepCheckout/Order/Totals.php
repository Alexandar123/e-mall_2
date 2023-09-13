<?php
/**
 * Totals
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Block\LayoutOnestepCheckout\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

class Totals extends Template
{

    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $source = $totalsBlock->getSource();
        if ($source && !empty($source->getGiftWrapAmount())) {
            $totalsBlock->addTotal(new DataObject([
                'code' => 'gift_wrap',
                'field' => 'onestepcheckout_gift_wrap_amount',
                'label' => __('Gift Wrap'),
                'value' => $source->getGiftWrapAmount(),
            ]));
        }
    }
}
