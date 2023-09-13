<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\GDPR\Observer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserLoginObserver implements ObserverInterface
{
    /**
     * @var \MGS\GDPR\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @param \MGS\GDPR\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $customerSession
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        \MGS\GDPR\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $customerSession,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->_session = $customerSession;
        $this->_customerUrl = $customerUrl;
    }

    /**
     * Check captcha on user login page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws NoSuchEntityException
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $loginParams = $controller->getRequest()->getPost('login');

		if ($this->_helper->getStoreConfig('gdpr/general/active') 
			&& $this->_helper->getStoreConfig('gdpr/login/active') 
			&& !isset($loginParams['accept_gdpr'])) {
			
			$this->messageManager->addError(__('You do not agree with the storage and handling of your data by this website.'));
			$this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
			$beforeUrl = $this->_session->getBeforeAuthUrl();
			$url = $beforeUrl ? $beforeUrl : $this->_customerUrl->getLoginUrl();
			$controller->getResponse()->setRedirect($url);
		}

        return;
    }
}
