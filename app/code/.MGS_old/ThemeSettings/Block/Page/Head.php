<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Block\Page;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Main contact form block
 */
class Head extends \MGS\Fbuilder\Block\Page\Head
{	
	protected $_generateHelper;
	protected $_storeManager;
	protected $session;
	protected $themeSetting;
	protected $request;
	public function __construct(
        Context $context,
		\MGS\Fbuilder\Helper\Generate $_generateHelper,
		\Magento\Framework\Session\SessionManagerInterface $session,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\MGS\ThemeSettings\Helper\Config $themeSettingConfig,
		\Magento\Framework\App\RequestInterface $request
    )
    {
		$this->session = $session;
		$this->themeSetting = $themeSettingConfig;
        parent::__construct($context, $_generateHelper);
		$this->request = $request;

		if(
			$this->request->getModuleName()!='mgsthemesetting' && 
			$this->request->getModuleName()!='fbuilder' &&
			$this->request->getModuleName()!='ajaxcart' &&
			$this->request->getModuleName()!='aquickview' &&
			$this->request->getModuleName()!='guestwishlist' &&
			$this->request->getModuleName()!='instantsearch'
		){
			$this->session->setFrameFullActionName($this->_request->getFullActionName());
		}
		
    }
	
	public function getStyleInline(){
		if($this->session->getStyleInline()){
			return $this->session->getStyleInline();
		}else{
			return $this->themeSetting->getStyleInline($this->_storeManager->getStore()->getId());
		}
	}
}