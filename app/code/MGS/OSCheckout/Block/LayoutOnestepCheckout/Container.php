<?php
/**
 * Container
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Block\LayoutOnestepCheckout;

use Magento\Framework\View\Element\Template;
use MGS\OSCheckout\Helper\Data;

class Container extends Template
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Container constructor.
     * @param Template\Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $dataHelper,
        array            $data = []
    ) {
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCheckoutDescription()
    {
        return $this->dataHelper->getConfigGeneral('description');
    }

    public function getCheckoutTitle()
    {
        return $this->dataHelper->getConfigGeneral('title');
    }
}
