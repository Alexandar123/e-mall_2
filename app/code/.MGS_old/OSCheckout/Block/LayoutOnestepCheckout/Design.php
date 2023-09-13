<?php
/**
 * Design
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Block\LayoutOnestepCheckout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MGS\OSCheckout\Helper\Data;

class Design extends Template
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ThemeProviderInterface
     */
    protected $_themeProviderInterface;

    /**
     * @type \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Design constructor.
     * @param Context $context
     * @param Data $dataHelper
     * @param ThemeProviderInterface $themeProviderInterface
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context                $context,
        Data                   $dataHelper,
        ThemeProviderInterface $themeProviderInterface,
        CheckoutSession        $checkoutSession,
        array                  $data = []
    ) {
        parent::__construct($context, $data);

        $this->dataHelper = $dataHelper;
        $this->_themeProviderInterface = $themeProviderInterface;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return bool
     */
    public function isEnableGoogleApi()
    {
        return $this->getHelperConfig()->getAutoDetectedAddress() == 'google';
    }

    public function getHelperConfig()
    {
        return $this->dataHelper;
    }

    /**
     * @return mixed
     */
    public function getGoogleApiKey()
    {
        return $this->getHelperConfig()->getGoogleApiKey();
    }

    /**
     * @return array
     */
    public function getDesignConfiguration()
    {
        return $this->getHelperConfig()->getDesignConfig();
    }

    /**
     * @return string
     */
    public function getCurrentTheme()
    {
        return $this->_themeProviderInterface->getThemeById($this->getHelperConfig()->getCurrentThemeId())->getCode();
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        return $this->checkoutSession->getQuote()->isVirtual();
    }
}
