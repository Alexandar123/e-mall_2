<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ClaueTheme\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\Manager as CacheManager;
class ActivateTheme implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\ProductMetadataInterface $metaData,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		CacheManager $cacheManager
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->metaData = $metaData;
        $this->messageManager = $messageManager;
		$this->cacheManager = $cacheManager;
    }
	
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $keyParams = $this->request->getParam('groups');
        $keyValue = $keyParams['activate']['fields']['claue']['value'];
		if($keyValue!=''){
			$keyValue = trim($keyValue);
			$baseUrl = $this->scopeConfig->getValue('web/unsecure/base_url');
			$domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $baseUrl));
			if(strpos($domain, "/")){
				$domain = substr($domain, 0, strpos($domain, "/"));
			}
			$magentoVersion =  $this->metaData->getVersion() . ' - Claue V2.0.0';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://shop.magesolution.com/licensekey/index/activate/item/20155150/theme/Claue/key/$keyValue/domain/$domain/version/$magentoVersion");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'ACTIVATE-THEMEFOREST-THEME');

			$result = curl_exec($ch);
			if($result=='Activated'){
				$this->messageManager->addSuccess(__('Claue theme has been successfully activated.'));
			}else{
				$this->configWriter->save('active_theme/activate/claue', NULL);
				$this->cacheManager->clean(['config']);
				$this->messageManager->addError($result);
			}
			curl_close($ch);
		}else{
			$this->messageManager->addError(__('Claue theme has not been activated'));
		}
        return $this;
    }
}
