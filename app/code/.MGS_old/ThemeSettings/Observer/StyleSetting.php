<?php
/**
 * Copyright © 2017 Sam Granger. All rights reserved.
 *
 * @author Sam Granger <sam.granger@gmail.com>
 */

namespace MGS\ThemeSettings\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class StyleSetting implements ObserverInterface
{
	public function __construct(
		\MGS\ThemeSettings\Helper\Config $helper
    ) {
		$this->_helper = $helper;
    }
	
    public function execute(Observer $observer){
        $data = $observer->getData('content');
		
		$html = $this->_helper->getThemeSettingStyle($data['store_id']);
		
		$data->setContent($html);

        return $this;
    }
}