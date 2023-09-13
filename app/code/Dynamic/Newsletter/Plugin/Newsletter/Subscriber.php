<?php

namespace Dynamic\Newsletter\Plugin\Newsletter;

use Magento\Framework\App\Request\Http;

class Subscriber {
  protected $request;

  public function __construct(
    Http $request
  ) {
    $this->request = $request;
  }

  public function aroundSubscribe($subject, \Closure $proceed, $email) {
    //$result = $proceed($email);
     
    if ($this->request->isPost()) {

      $firstname = $this->request->getPost('c_firstname');
      $surname = $this->request->getPost('c_surname');
      $ip = $this->request->getPost('c_ip');

      $subject->setCFirstname($firstname);
      $subject->setCSurname($surname);
      $subject->setCIp($ip);

      //$this->setCookieFlag();

      try {
        $result = $proceed($email);
        $subject->save();
      }catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      }
    }

    return $result;
  }

  public function setCookieFlag() {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
      $cookieMetadataFactory = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
      $sessionManager = $objectManager->get('Magento\Framework\Session\SessionManagerInterface');

      $metadata = $cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(94608000)
            ->setPath($sessionManager->getCookiePath())
            ->setDomain($sessionManager->getCookieDomain());

      $cookieManager->setPublicCookie('signPopupFlag', '1', $metadata);
  }
}