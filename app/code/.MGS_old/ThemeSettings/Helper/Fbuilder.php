<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\ThemeSettings\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Contact base helper
 */
class Fbuilder extends \MGS\Fbuilder\Helper\Data
{
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
		\Magento\Framework\Xml\Parser $parser,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider
	) {
		parent::__construct($storeManager, $url, $date, $filesystem, $request, $context, $objectManager, $customerSession, $pageFactory, $blockFactory, $file, $parser, $filterProvider );
	}
	// Check to accept to use builder panel
    public function acceptToUsePanel() {
		if($this->_acceptToUsePanel){
			return true;
		}else{
			if ($this->showButton() && ($this->customerSession->getUseFrontendBuilder() == 1)) {
				$this->_acceptToUsePanel = true;
				return true;
			}
			$this->_acceptToUsePanel = false;
			return false;
		}

    }

	/* Check to visible panel button */
    public function showButton() {
        if ($this->getStoreConfig('fbuilder/general/is_enabled')) {
            $customer = $this->getCustomer();
			if($customer->getIsFbuilderAccount() == 1){
				return true;
			}
			return false;
        }
        return false;
    }
	 public function _getCurrentUrl()
    {
        $urlinterface = $this->_objectManager->get('\Magento\Framework\UrlInterface');
        return $urlinterface->getCurrentUrl();
    }

}
