<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * "My Wish List" link
 */
namespace MGS\ThemeSettings\Block\Wishlist;

/**
 * Class Link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'MGS_ThemeSettings::wishlist/link.phtml';

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistHelper;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
	
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_wishlistHelper = $wishlistHelper;
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_wishlistHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
		if (!$this->customerSession->isLoggedIn() && $this->isActiveModule('MGS_Guestwishlist')) {
			return $this->getUrl('guestwishlist');
		}
        
        return $this->getUrl('wishlist');
    }
	
	public function isActiveModule($module){
		if($this->_moduleManager->isOutputEnabled($module) && $this->_moduleManager->isEnabled($module)){
			return true;
		}
		return false;
	}

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Wish List');
    }
}
