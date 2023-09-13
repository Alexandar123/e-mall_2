<?php
/**
 * Copyright © 2017 Sam Granger. All rights reserved.
 *
 * @author Sam Granger <sam.granger@gmail.com>
 */

namespace MGS\ThemeSettings\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class ExportSetting implements ObserverInterface
{
    protected $_config;
	protected $_request;
	
	public function __construct(
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $config,
		\Magento\Framework\App\Request\Http $request
    ){
        $this->_config = $config;
		$this->_request = $request;
    }
	
    public function execute(Observer $observer){
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
		$data = $observer->getData('content');
		
		$html = '';
		$generalBuilderSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'fbuilder/general/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$styleBuilderSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'fbuilder/font_style/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
			
		if((count($generalBuilderSetting)>0) || (count($styleBuilderSetting)>0)){
			$html .= "\t<fbuilder>\n";
			if(count($generalBuilderSetting)>0){
				$html .= "\t\t<general>\n";
				foreach($generalBuilderSetting as $general){
					$field = str_replace('fbuilder/general/','',$general->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$general->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</general>\n";
			}
			
			if(count($styleBuilderSetting)>0){
				$html .= "\t\t<font_style>\n";
				foreach($styleBuilderSetting as $style){
					$field = str_replace('fbuilder/font_style/','',$style->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$style->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</font_style>\n";
			}
			
			$html .= "\t</fbuilder>\n";
		}
		
		$generalThemeSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themesettings/general/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$headerThemeSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themesettings/header/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$mainThemeSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themesettings/main/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$footerThemeSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themesettings/footer/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$imageThemeSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themesettings/product_image_dimention/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		if((count($generalThemeSetting)>0) || (count($headerThemeSetting)>0) || (count($mainThemeSetting)>0) || (count($footerThemeSetting)>0) || (count($imageThemeSetting)>0)){
			$html .= "\t<themesettings>\n";
			if(count($generalThemeSetting)>0){
				$html .= "\t\t<general>\n";
				foreach($generalThemeSetting as $general){
					$field = str_replace('themesettings/general/','',$general->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$general->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</general>\n";
			}
			
			if(count($headerThemeSetting)>0){
				$html .= "\t\t<header>\n";
				foreach($headerThemeSetting as $header){
					$field = str_replace('themesettings/header/','',$header->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$header->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</header>\n";
			}
			
			if(count($mainThemeSetting)>0){
				$html .= "\t\t<main>\n";
				foreach($mainThemeSetting as $main){
					$field = str_replace('themesettings/main/','',$main->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$main->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</main>\n";
			}
			
			if(count($footerThemeSetting)>0){
				$html .= "\t\t<footer>\n";
				foreach($footerThemeSetting as $footer){
					$field = str_replace('themesettings/footer/','',$footer->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$footer->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</footer>\n";
			}
			
			if(count($imageThemeSetting)>0){
				$html .= "\t\t<product_image_dimention>\n";
				foreach($imageThemeSetting as $image){
					$field = str_replace('themesettings/product_image_dimention/','',$image->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$image->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</product_image_dimention>\n";
			}
			
			$html .= "\t</themesettings>\n";
		}
		
		$customFont = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themestyle/custom_font/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$fontSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'themestyle/font/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
			
			
		if((count($customFont)>0) || (count($fontSetting)>0)){
			$html .= "\t<themestyle>\n";
			if(count($customFont)>0){
				$html .= "\t\t<custom_font>\n";
				foreach($customFont as $custom){
					$field = str_replace('themestyle/custom_font/','',$custom->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$custom->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</custom_font>\n";
			}
			
			if(count($fontSetting)>0){
				$html .= "\t\t<font>\n";
				foreach($fontSetting as $font){
					$field = str_replace('themestyle/font/','',$font->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$font->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</font>\n";
			}
			
			$html .= "\t</themestyle>\n";
		}
		$colorSetting = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'fcolor/main/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$headerColor = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'fcolor/header_fcolor/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$menuColor = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'fcolor/menu_fcolor/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		$footerColor = $this->_config->create()
			->addFieldToFilter('path', ['like'=>'fcolor/footer_fcolor/%'])
			->addFieldToFilter('scope', $scope)
			->addFieldToFilter('scope_id', $storeId);
		
		
		if((count($colorSetting)>0) || (count($headerColor)>0) || (count($menuColor)>0) || (count($footerColor)>0) ){
			$html .= "\t<fcolor>\n";
			if(count($colorSetting)>0){
				$html .= "\t\t<main>\n";
				foreach($colorSetting as $general){
					$field = str_replace('fcolor/main/','',$general->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$general->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</main>\n";
			}
			
			if(count($headerColor)>0){
				$html .= "\t\t<header_fcolor>\n";
				foreach($headerColor as $header){
					$field = str_replace('fcolor/header_fcolor/','',$header->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$header->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</header_fcolor>\n";
			}
			
			if(count($menuColor)>0){
				$html .= "\t\t<menu_fcolor>\n";
				foreach($menuColor as $menu){
					$field = str_replace('fcolor/menu_fcolor/','',$menu->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$menu->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</menu_fcolor>\n";
			}
			
			if(count($footerColor)>0){
				$html .= "\t\t<footer_fcolor>\n";
				foreach($footerColor as $footer){
					$field = str_replace('fcolor/footer_fcolor/','',$footer->getPath());
					$html .= "\t\t\t<".$field."><![CDATA[".$footer->getValue()."]]></".$field.">\n";
				}
				$html .= "\t\t</footer_fcolor>\n";
			}
			
			$html .= "\t</fcolor>\n";
		}
		$data->setContent($html);
		

        return $this;
    }
}