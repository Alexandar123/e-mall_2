<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Controller\Adminhtml\ThemeSettings;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Importstatic extends \MGS\ThemeSettings\Controller\Adminhtml\ThemeSettings
{
	protected $_filesystem;

	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Xml\Parser $parser,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter
	)
    {
        parent::__construct($context);
		$this->_filesystem = $filesystem;
		$this->_parser = $parser;
		$this->configWriter = $configWriter;
	}

	public function isLocalhost() {
        $whitelist = array(
            '127.0.0.1',
			'localhost',
			'127.0.0.1:8080',
			'localhost:8080',
            '::1'
        );

        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }

    /**
     * Edit sitemap
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
		if($theme = $this->getRequest()->getParam('theme')){
			if($item = $this->getRequest()->getParam('activate')){
				$activeKey = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('active_theme/activate/'.$theme);
				if(!$this->isLocalhost()) {
					if($activeKey==''){
						$this->messageManager->addError(__('Please activate the theme first.'));
						$this->_redirect($this->_redirect->getRefererUrl());
						return;
					}else{
						$keyValue = trim($activeKey);
						$baseUrl = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('web/unsecure/base_url');
						$domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $baseUrl));
						if(strpos($domain, "/")){
							$domain = substr($domain, 0, strpos($domain, "/"));
						}
						$magentoVersion =  $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
						$themeName = ucfirst($theme);
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, "https://www.magesolution.com/licensekey/index/activate/item/$item/theme/$themeName/key/$keyValue/domain/$domain/version/$magentoVersion");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_USERAGENT, 'ACTIVATE-THEMEFOREST-THEME');

						$result = curl_exec($ch);
						curl_close($ch);
						if($result!='Activated'){
							$this->configWriter->save('active_theme/activate/'.$theme, NULL);
							$this->messageManager->addError(__('The theme has not been activated or your purchase code has been used for another domain.'));
							$this->_redirect($this->_redirect->getRefererUrl());
							return;
						}
					}
				}
			}
			$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/static_blocks/'.$theme.'.xml');
			try {
				if (is_readable($filePath)){
					$parsedArray = $this->_parser->load($filePath)->xmlToArray();
					if(isset($parsedArray['static_block']['item']) && (count($parsedArray['static_block']['item'])>0)){
						foreach($parsedArray['static_block']['item'] as $staticBlock){
							if(is_array($staticBlock)){
								$identifier = $staticBlock['identifier'];
                                $stores = isset($staticBlock['stores']) ? explode(',', $staticBlock['stores']) : array(0);
								$staticBlockData = $staticBlock;
							}else{
								$identifier = $parsedArray['static_block']['item']['identifier'];
                                $stores =isset($parsedArray['static_block']['item']['stores']) ? explode(',', $parsedArray['static_block']['item']['stores']) : array(0);
								$staticBlockData = $parsedArray['static_block']['item'];
							}

							$staticBlocksCollection = $this->_objectManager->create('Magento\Cms\Model\Block')
								->getCollection()
								->addFieldToFilter('identifier', $identifier)
								->load();
							if (count($staticBlocksCollection) > 0){
								foreach ($staticBlocksCollection as $_item){
									$_item->delete();
								}
							}

							$this->_objectManager->create('Magento\Cms\Model\Block')->setData($staticBlockData)->setIsActive(1)->setStores($stores)->save();

						}
						$this->messageManager->addSuccess(__('Static blocks was successfully imported.'));
					}else{
						$this->messageManager->addError(__('The file is corrupted!'));
					}
				}
			}catch (\Exception $e) {
				// display error message
				$this->messageManager->addError($e->getMessage());
			}
		}else{
			$this->messageManager->addError(__('The file to import no longer exists.'));
		}
		$this->_redirect($this->_redirect->getRefererUrl());
		return;
    }
}
