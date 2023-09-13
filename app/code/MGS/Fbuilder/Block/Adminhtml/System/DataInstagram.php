<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Adminhtml\System;

/**
 * Export CSV button for shipping table rates
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class DataInstagram extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;
    
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory           $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper                             $escaper
     * @param \Magento\Backend\Helper\Data                           $helper
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $url = $this->_backendUrl->getUrl("adminhtml/fbuilder/getdatainstagram");
        $html = '<button type="button" style="display: block;margin-bottom: 10px;" class="action-default scalable" onclick="generateInstagramData()" data-ui-id="widget-button-0"><span>'.__('Refresh Data Images').'</span></button>';
        
        $html .= '<textarea style="background-color: #e9e9e9;border-color: #adadad;color: #303030;opacity: .5;cursor: not-allowed;" id="fbuilder_social_instagram_data" name="groups[social][fields][instagram_data][value]" class=" textarea admin__control-textarea" rows="2" cols="15" data-ui-id="textarea-groups-social-fields-instagram-data-value">'.$this->getEscapedValue().'</textarea>';
        
        return $html;
    }
}
