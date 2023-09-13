<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dynamic\Newsletter\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{  
    protected $customerAccountManagement;
    protected $_urlInterface;

    const XML_PATH_SUCCESS_EMAIL_IDENTITY = 'trans_email/ident_custom1/email';

    const XML_PATH_SUCCESS_EMAIL_TEMPLATE = 'newsletter/subscription/success_email_template';

    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        \Magento\Framework\UrlInterface $urlInterface,
        SubscriptionManagerInterface $subscriptionManager
    ) {
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $subscriberFactory, $customerSession, $storeManager, $customerUrl, $customerAccountManagement, $subscriptionManager);

    }

    public function execute()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            $name = (string)$this->getRequest()->getPost('c_firstname');

            $_scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

            $adminEmail = $_scopeConfig->getValue(self::XML_PATH_SUCCESS_EMAIL_IDENTITY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $ip = $this->getRequest()->getPost('c_ip');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && (int) $subscriber->getSubscriberStatus() === \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    if($subscriber->getCIp() && $subscriber->getCIp() !== $ip) {

                        $subscriber->setCIp($subscriber->getCIp().','.$ip);
                        $subscriber->save(); 

                        $model = $this->_objectManager->create('Magento\Newsletter\Model\Subscriber');
                        $model->setEmail($email);
                        $model->sendConfirmationSuccessEmail();

                        if($adminEmail) {
                            $this->sendAdminMail($adminEmail,$email,$name);
                        }

                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('This email address is already subscribed.')
                        );
                    }
                    
                }

                if($adminEmail) {
                    $this->sendAdminMail($adminEmail,$email,$name);
                }

                $status = (int) $this->_subscriberFactory->create()->subscribe($email);
                $this->messageManager->addSuccessMessage('Thank you for your subscription.');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong with the subscription.'));
            }
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl();

        $redirectCustomUrl = $this->getRequest()->getPost('redirect-url');
        
        if($redirectCustomUrl) {
            return $redirect->setUrl($redirectCustomUrl);
        } else {
            return $redirect->setUrl($redirectUrl);
        }
        
    }

    public function sendAdminMail($adminEmail, $email, $name) {

        $_scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
        $_transportBuilder = $this->_objectManager->create('Magento\Framework\Mail\Template\TransportBuilder');
        $_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');

        $emailTemplate = $_scopeConfig->getValue(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $senderInfo = array(
            'name' => $name,
            'email' => $email
        );

        $inlineTranslation->suspend();

        try {
            
            $transport = $_transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars(['data' => $this])
                ->setFrom($senderInfo)
                //->addCc($adminEmail)
                ->addTo($adminEmail)
                ->getTransport();

            $transport->sendMessage();
            
            $inlineTranslation->resume();
            
        } catch (\Exception $e) {
            $inlineTranslation->resume();
            $this->messageManager->addError($e->getMessage());
        }

    }
    
}
