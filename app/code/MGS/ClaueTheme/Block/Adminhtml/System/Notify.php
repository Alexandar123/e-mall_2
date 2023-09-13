<?php

namespace MGS\ClaueTheme\Block\Adminhtml\System;

class Notify extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$activeKey = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('active_theme/activate/claue');
		$url = $objectManager->get('Magento\Backend\Model\UrlInterface')->getUrl("adminhtml/system_config/edit/section/active_theme");
		if($activeKey==''){
			$html = '<span style="color:#ff0000; font-size:14px">Please <a href="'.$url.'">activate</a> for Claue theme.</span>';
		}

        return $html;
    }
}
