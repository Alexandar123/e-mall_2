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
class InstagramToken extends \Magento\Framework\Data\Form\Element\AbstractElement
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
        
        $html = '<input id="fbuilder_social_instagram_access_token" name="groups[social][fields][instagram_access_token][value]" data-ui-id="text-groups-social-fields-instagram-access-token-value"  onchange="generateInstagramData()" value="'.$this->getEscapedValue().'" class=" input-text admin__control-text" type="text">';
        
        return $html;
    }
}
