<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Dynamic\FTPImportExport\Block\Adminhtml\System\Config;

class Regularproducts extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Set Validate VAT Button Label
     *
     * @param string $vatButtonLabel
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     */
    public function setVatButtonLabel($vatButtonLabel)
    {
        $this->_vatButtonLabel = $vatButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/Regularproducts.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_vatButtonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('dynamic_ftpimportexport/run/regularProducts'),
            ]
        );
        return $this->_toHtml();
    }
}
