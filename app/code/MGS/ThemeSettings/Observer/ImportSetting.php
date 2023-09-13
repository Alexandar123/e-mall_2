<?php
/**
 * Copyright © 2017 Sam Granger. All rights reserved.
 *
 * @author Sam Granger <sam.granger@gmail.com>
 */

namespace MGS\ThemeSettings\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class ImportSetting implements ObserverInterface
{
	protected $_configFactory;
	protected $_request;
	protected $_config;

	public function __construct(
        \Magento\Config\Model\Config\Factory $configFactory,
		\Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $config,
		\Magento\Framework\App\Request\Http $request
    ){
        $this->_configFactory = $configFactory;
		$this->_request = $request;
		$this->_config = $config;
    }

    public function execute(Observer $observer){
		$parsedArray = $observer->getContent();
        $this->imporSetting('fbuilder', $parsedArray);
        $this->imporSetting('themesettings', $parsedArray);
        $this->imporSetting('themestyle', $parsedArray);
        $this->imporSetting('fcolor', $parsedArray);
        return $this;
    }

	public function imporSetting($xmlNode, $parsedArray){

		if(isset($parsedArray['page'][$xmlNode])){
			if($this->_request->getParam('store')){
				$storeId = $this->_request->getParam('store');
				$scope = 'stores';
			}elseif($this->_request->getParam('website')){
				$storeId = $this->_request->getParam('website');
				$scope = 'websites';
			}else{
				$storeId = 0;
				$scope = 'default';
			}

			$setting = [];

			if($xmlNode=='fbuilder'){
				$setting = $this->_config->create()
					->addFieldToFilter('scope', $scope)
					->addFieldToFilter('scope_id', $storeId);

				$setting->getSelect()->where("path like 'fbuilder/general/%' or path like 'fbuilder/font_style/%'");
			}elseif($xmlNode=='themesettings'){
				$setting = $this->_config->create()
					->addFieldToFilter('scope', $scope)
					->addFieldToFilter('scope_id', $storeId);

				$setting->getSelect()->where("path like 'themesettings/general/%' or path like 'themesettings/header/%' or path like 'themesettings/main/%' or path like 'themesettings/footer/%' or path like 'themesettings/product_image_dimention/%'");
			}elseif($xmlNode=='fcolor'){
				$setting = $this->_config->create()
					->addFieldToFilter('scope', $scope)
					->addFieldToFilter('scope_id', $storeId);

				$setting->getSelect()->where("path like 'fcolor/main/%' or path like 'fcolor/header_fcolor/%' or path like 'fcolor/menu_fcolor/%' or path like 'fcolor/footer_fcolor/%'");
			}else{
				$setting = $this->_config->create()
					->addFieldToFilter('scope', $scope)
					->addFieldToFilter('scope_id', $storeId);
				$setting->getSelect()->where("path like 'themestyle/custom_font/%' or path like 'themestyle/font/%'");
			}

			if(count($setting)>0){
				foreach($setting as $_setting){
					$_setting->delete();
				}
			}


			$website = $this->_request->getParam('website');
			$store = $this->_request->getParam('store');
			$groups = [];
			if(count($parsedArray['page'][$xmlNode])>0){
				foreach($parsedArray['page'][$xmlNode] as $groupName=>$_group){
					$fields = [];
					foreach($_group as $field=>$value){
						$fields[$field] = ['value'=>$value];
					}

					$groups[$groupName] = [
						'fields' => $fields
					];
				}
			}

			$configData = [
				'section' => $xmlNode,
				'website' => $website,
				'store' => $store,
				'groups' => $groups
			];

			/** @var \Magento\Config\Model\Config $configModel  */
			$configModel = $this->_configFactory->create(['data' => $configData]);
			$configModel->save();
		}
		return;
	}
}
