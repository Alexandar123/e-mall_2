<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Fbuilder\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Contact base helper
 */
class Builder extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    
    protected $_url;
    
    protected $_request;
    
    protected $_acceptToUsePanel = false;
    
    protected $_customer;
    
    protected $_fullActionName;
    
    protected $scopeConfig;
    
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    
    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        CustomerSession $customerSession,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->customerSession = $customerSession;
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->_pageFactory = $pageFactory;
        $this->_filterProvider = $filterProvider;
        $this->_fullActionName = $this->_request->getFullActionName();
    }
    
    public function getModel($model)
    {
        return $this->_objectManager->create($model);
    }

    public function getUrlBuilder()
    {
        return $this->_url;
    }

    public function getCurrentUrl()
    {
        return $this->_url->getCurrentUrl();
    }
    
    /**
     * Retrieve current url in base64 encoding
     *
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return strtr(base64_encode($this->_url->getCurrentUrl()), '+/=', '-_,');
    }
    
    /**
     * base64_decode() for URLs decoding
     *
     * @param  string $url
     * @return string
     */
    public function decode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->_url->sessionUrlVar($url);
    }

    /**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        $customerInSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        return $customerInSession->getCustomerId();
    }
    
    /* Get current customer */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = $this->getModel('Magento\Customer\Model\Customer')->load($this->getCustomerId());
        }
        return $this->_customer;
    }
    
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
    
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }
    
    /* Get system store config */
    public function getStoreConfig($node, $storeId = null)
    {
        if ($storeId != null) {
            return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }
    
    // Check to accept to use builder panel
    public function acceptToUsePanel()
    {
        if ($this->_acceptToUsePanel) {
            return true;
        } else {
            if ($this->showButton() && ($this->customerSession->getUseFrontendBuilder() == 1)) {
                if ($this->isHomepage() || $this->isCmsPage()) {
                    if ($this->canEditPage()) {
                        $this->_acceptToUsePanel = true;
                        return true;

                    } else {
                        $this->_acceptToUsePanel = false;
                        return false;
                    }
                } elseif ($this->isProductPage()) {
                    if ($this->canEditProductPage()) {
                        $this->_acceptToUsePanel = true;
                        return true;
                    } else {
                        $this->_acceptToUsePanel = false;
                        return false;
                    }
                } elseif ($this->isCategoryPage()) {
                    if ($this->canEditCategorPage()) {
                        $this->_acceptToUsePanel = true;
                        return true;
                    } else {
                        $this->_acceptToUsePanel = false;
                        return false;
                    }
                } else {
                    $this->_acceptToUsePanel = false;
                    return false;
                }
            }
        }
    }

    /* Check to visible panel button */
    public function showButton()
    {
        if ($this->getStoreConfig('fbuilder/general/is_enabled')) {
            $customer = $this->getCustomer();
            if ($customer->getIsFbuilderAccount() == 1) {
                return true;
            }
            return false;
        }

        return false;
    }

    /* Check can edit cms pages or not */
    public function canEditPage()
    {
        $customer = $this->getCustomer();
        $availablePageIds = explode(',', $customer->getFbuilderAvailablePages());

        if ($this->isHomepage()) {
            $pageIdentifier = $this->getStoreConfig('web/default/cms_home_page', $this->_storeManager->getStore()->getId());
            $arrIdentifier = explode('|', $pageIdentifier);

            $page = $this->_pageFactory->create()->setStoreId($this->_storeManager->getStore()->getId())->load($arrIdentifier[0]);

            $currentPageId = $page->getId();
        } else {
            $currentPageId = $this->_request->getParam('page_id');
        }

        if (in_array($currentPageId, $availablePageIds) || in_array('0', $availablePageIds)) {
            return true;
        } else {
            return false;
        }
    }
    
    /* Check can edit product description or not */
    public function canEditProductPage()
    {
        $customer = $this->getCustomer();
        if ($customer->getFbuilderCanEditProduct() == 1) {
            return true;
        }
        return false;
    }
    
    /* Check can edit category description or not */
    public function canEditCategorPage()
    {
        $customer = $this->getCustomer();
        if ($customer->getFbuilderCanEditCategory() == 1) {
            return true;
        }
        return false;
    }
    
    public function getMediaUrl()
    {
        return $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    
    /* Check current page is homepage or not */
    public function isHomepage()
    {
        if ($this->_fullActionName == 'cms_index_index') {
            return true;
        }
        return false;
    }
    
    /* Check current page is homepage or not */
    public function isCmsPage()
    {
        if ($this->_fullActionName == 'cms_page_view') {
            return true;
        }
        return false;
    }
    
    /* Check current page is product details or not */
    public function isProductPage()
    {
        if ($this->_fullActionName == 'catalog_product_view') {
            return true;
        }
        return false;
    }
    
    /* Check current page is category page or not */
    public function isCategoryPage()
    {
        if ($this->_fullActionName == 'catalog_category_view') {
            return true;
        }
        return false;
    }
    
    public function getContentByShortcode($content)
    {
        return $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($content);
    }
    
    public function encodeHtml($html)
    {
        $result = str_replace("<", "&ltchange;", $html);
        $result = str_replace(">", "&gtchange;", $result);
        $result = str_replace('"', '&#34change;', $result);
        $result = str_replace('&#34change;', 'mgs_change_quotation_marks', $result);
        $result = str_replace("'", "&#39change;", $result);
        $result = str_replace("&#39change;", "mgs_apostrophe_change", $result);
        $result = str_replace(",", "&commachange;", $result);
        $result = str_replace("+", "&pluschange;", $result);
        $result = str_replace("{", "&leftcurlybracket;", $result);
        $result = str_replace("}", "&rightcurlybracket;", $result);
        return $result;
    }
    
    public function decodeHtmlTag($content)
    {
        $result = str_replace("&ltchange;", "<", $content);
        $result = str_replace("&lt;change;", "<", $result);
        $result = str_replace("&gtchange;", ">", $result);
        $result = str_replace("&gt;change;", ">", $result);
        $result = str_replace('&#34change;', '"', $result);
        $result = str_replace('mgs_change_quotation_marks', '"', $result);
        $result = str_replace("&#39change;", "'", $result);
        $result = str_replace("mgs_apostrophe_change", "'", $result);
        $result = str_replace("&commachange;", ",", $result);
        $result = str_replace("&pluschange;", "+", $result);
        $result = str_replace("&leftcurlybracket;", "{", $result);
        $result = str_replace("&amp;leftcurlybracket;", "{", $result);
        $result = str_replace("&rightcurlybracket;", "}", $result);
        $result = str_replace("&amp;rightcurlybracket;", "}", $result);
        $result = str_replace("&mgs_space;", " ", $result);
        return $result;
    }
    
    public function getRealUrl($url)
    {
        if ($url && ($url!='')) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            } else {
                return $this->_url->getUrl($url);
            }
        }
        return '#';
    }
    
    public function getDirectUrl($var)
    {
        if (filter_var($var, FILTER_VALIDATE_URL)) {
            return $var;
        } else {
            return $this->_url->getUrl($var);
        }
    }
    
    public function getImageUrl($type, $fileName)
    {
        return $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mgs/fbuilder/'.$type.$fileName;
    }
    
    public function getBackgroundImageUrl($backgroundImageName)
    {
        return $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mgs/fbuilder/backgrounds/'.$backgroundImageName;
    }
    
    public function getPanelUploadSrc($type, $fileName)
    {
        return $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'wysiwyg/'.$type.'/'.$fileName;
    }
    
    public function convertColClass($perrowDefault, $perrowTablet = null, $perrowDefaultMobile = null)
    {
        $class = '';
        switch ($perrowDefault) {
        case 1:
            $class .= 'col-des-12';
            break;
        case 2:
            $class .= 'col-des-6';
            break;
        case 3:
            $class .= 'col-des-4';
            break;
        case 4:
            $class .= 'col-des-3';
            break;
        case 6:
            $class .= 'col-des-2';
            break;
        default:
            $class .= 'col';
            break;
        }
        
        if ($perrowTablet!=null) {
            switch ($perrowTablet) {
            case 1:
                $class .= ' col-tb-12';
                break;
            case 2:
                $class .= ' col-tb-6';
                break;
            case 3:
                $class .= ' col-tb-4';
                break;
            case 4:
                $class .= ' col-tb-3';
                break;
            case 6:
                $class .= ' col-tb-2';
                break;
            default:
                $class .= ' col-tb';
                break;
            }
        }
        
        if ($perrowDefaultMobile!=null) {
            switch ($perrowDefaultMobile) {
            case 1:
                $class .= ' col-mb-12';
                break;
            case 2:
                $class .= ' col-mb-6';
                break;
            case 3:
                $class .= ' col-mb-4';
                break;
            case 4:
                $class .= ' col-mb-3';
                break;
            case 6:
                $class .= ' col-mb-2';
                break;
            default:
                $class .= ' col-mb';
                break;
            }
        }
        
        return $class;
    }
    
    /* Get class clear left */
    public function getClearClass($perrow = null, $nb_item)
    {
        if (!$perrow) {
            $settings = $this->getThemeSettings();
            $perrow = $settings['catalog']['per_row'];
        }
        $clearClass = '';
        switch ($perrow) {
        case 2:
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-row-item first-tb-item first-mb-item";
            }
            return $clearClass;
                break;
        case 3:
            if ($nb_item % 3 == 1) {
                $clearClass.= " first-row-item first-tb-item";
            }
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-mb-item";
            }
            return $clearClass;
                break;
        case 4:
            if ($nb_item % 4 == 1) {
                $clearClass.= " first-row-item";
            }
            if ($nb_item % 3 == 1) {
                $clearClass.= " first-tb-item";
            }
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-mb-item";
            }
            return $clearClass;
                break;
        case 5:
            if ($nb_item % 5 == 1) {
                $clearClass.= " first-row-item";
            }
            if ($nb_item % 3 == 1) {
                $clearClass.= " first-tb-item";
            }
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-mb-item";
            }
            return $clearClass;
                break;
        case 6:
            if ($nb_item % 6 == 1) {
                $clearClass.= " first-row-item";
            }
            if ($nb_item % 3 == 1) {
                $clearClass.= " first-tb-item";
            }
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-mb-item";
            }
            return $clearClass;
                break;
        case 7:
            if ($nb_item % 7 == 1) {
                $clearClass.= " first-row-item";
            }
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-tb-item first-mb-item";
            }
            return $clearClass;
                break;
        case 8:
            if ($nb_item % 8 == 1) {
                $clearClass.= " first-row-item";
            }
            if ($nb_item % 3 == 1) {
                $clearClass.= " first-tb-item";
            }
            if ($nb_item % 2 == 1) {
                $clearClass.= " first-mb-item";
            }
            return $clearClass;
                break;
        }
        return $clearClass;
    }
    
    public function getAdditionalInformationHtml($field, $key)
    {
        $type = $field['type'];
        switch ($type) {
        case 'text':
            $html = '<label class="control-label">'.__('Validate') .'</label><br/><select class="multiselect" name="setting[form]['.$key.'][validate][]" multiple="multiple" style="height:85px">';
                
            $html .= '<option value="validate-number"';
            if (isset($field['validate']) && in_array('validate-number', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Number Only') .'</option>';
                
            $html .= '<option value="validate-alpha"';
            if (isset($field['validate']) && in_array('validate-alpha', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Letter Only') .'</option>';
                
            $html .= '<option value="validate-alphanum"';
            if (isset($field['validate']) && in_array('validate-alphanum', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Number Or Letter') .'</option>';
                
            $html .= '<option value="validate-email"';
            if (isset($field['validate']) && in_array('validate-email', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Email') .'</option>';
                
            $html .= '<option value="validate-url"';
            if (isset($field['validate']) && in_array('validate-url', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Url') .'</option>';
                
            $html .= '<option value="validate-identifier"';
            if (isset($field['validate']) && in_array('validate-identifier', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Identifier') .'</option>';
            $html .= '<option value="validate-zero-or-greater"';
            if (isset($field['validate']) && in_array('validate-zero-or-greater', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('0 Or Greater') .'</option>';
                
            $html .= '<option value="validate-greater-than-zero"';
            if (isset($field['validate']) && in_array('validate-greater-than-zero', $field['validate'])) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.__('Greater Than 0') .'</option>';
            $html .= '</select>';
            break;
        case 'textarea':
            $html = '';
            break;
        case 'file':
            $html = '<label class="control-label">' . __('Allow Extensions') . '</label><br/><input type="text" class="input-text" name="setting[form]['.$key.'][extension]" value="'.$field['extension'].'"/>';
            break;
        case 'date':
            $html = '';
            break;
        default:
            $html = '<label class="control-label">' . __('Options') . '</label><br/><input type="text" class="input-text required-entry" name="setting[form]['.$key.'][options]" placeholder="' . __('Comma-separated.') . '" value="'.str_replace(', ', ',', $field['options']).'"/>';
            break;
        }
        return $html;
    }
    
    /* Convert short code to insert image */
    public function convertImageWidgetCode($type, $image)
    {
        return '&lt;img src="{{media url="wysiwyg/'.$type.'/'.$image.'"}}" alt=""/&gt;';
    }
    
    public function convertToLayoutUpdateXml($child)
    {
        $settings = json_decode($child->getSetting(), true);
        $content = $child->getBlockContent();
        $content = preg_replace('/(fbuilder_address_title="")/i', '', $content);
        $content = preg_replace('/(fbuilder_address_title=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_button_text="")/i', '', $content);
        $content = preg_replace('/(fbuilder_button_text=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_text_content="")/i', '', $content);
        $content = preg_replace('/(fbuilder_text_content=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_title="")/i', '', $content);
        $content = preg_replace('/(fbuilder_title=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_note="")/i', '', $content);
        $content = preg_replace('/(fbuilder_note=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_coundown_date="")/i', '', $content);
        $content = preg_replace('/(fbuilder_coundown_date=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_days="")/i', '', $content);
        $content = preg_replace('/(fbuilder_days=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_hours="")/i', '', $content);
        $content = preg_replace('/(fbuilder_hours=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_minutes="")/i', '', $content);
        $content = preg_replace('/(fbuilder_minutes=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_seconds="")/i', '', $content);
        $content = preg_replace('/(fbuilder_seconds=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_saved_text="")/i', '', $content);
        $content = preg_replace('/(fbuilder_saved_text=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(accordion_content="")/i', '', $content);
        $content = preg_replace('/(accordion_content=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(accordion_label="")/i', '', $content);
        $content = preg_replace('/(accordion_label=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_address="")/i', '', $content);
        $content = preg_replace('/(fbuilder_address=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_line_one="")/i', '', $content);
        $content = preg_replace('/(fbuilder_line_one=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_line_two="")/i', '', $content);
        $content = preg_replace('/(fbuilder_line_two=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_line_three="")/i', '', $content);
        $content = preg_replace('/(fbuilder_line_three=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_line_four="")/i', '', $content);
        $content = preg_replace('/(fbuilder_line_four=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_line_five="")/i', '', $content);
        $content = preg_replace('/(fbuilder_line_five=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_profile_name="")/i', '', $content);
        $content = preg_replace('/(fbuilder_profile_name=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_subtitle="")/i', '', $content);
        $content = preg_replace('/(fbuilder_subtitle=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(fbuilder_icon="")/i', '', $content);
        $content = preg_replace('/(fbuilder_icon=".+?)+(")/i', '', $content);
        
        $content = preg_replace('/(labels=".+?)+(")/i', '', $content);
        
        //return $content;
        $arrContent = explode(' ', $content);
        $arrContent = array_filter($arrContent);
        
        $class = $arrContent[1];
        $class = str_replace('type=', 'class=', $class);
        unset($arrContent[0], $arrContent[1]);
        
        $lastData = end($arrContent);
        array_pop($arrContent);
        
        $arrContent = array_values($arrContent);

        $argumentString = '&nbsp;&nbsp;&nbsp;&nbsp;&lt;arguments&gt;<br/>';
        
        if (isset($settings['address_title']) && ($settings['address_title']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_address_title" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['address_title'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['title']) && ($settings['title']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_title" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['title'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['text_content']) && ($settings['text_content']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_text_content" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['text_content'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['button_text']) && ($settings['button_text']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_button_text" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['button_text'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['additional_content']) && ($settings['additional_content']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_note" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['additional_content'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['coundown_date']) && ($settings['coundown_date']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_coundown_date" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['coundown_date'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['days']) && ($settings['days']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_days" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['days'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['hours']) && ($settings['hours']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_hours" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['hours'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['minutes']) && ($settings['minutes']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_minutes" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['minutes'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['seconds']) && ($settings['seconds']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_seconds" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['seconds'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['saved_text']) && ($settings['saved_text']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_saved_text" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['saved_text'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['address']) && ($settings['address']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_address" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['address'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['line_one']) && ($settings['line_one']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_line_one" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['address'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['line_two']) && ($settings['line_two']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_line_two" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['line_two'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['line_three']) && ($settings['line_three']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_line_three" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['line_three'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['line_four']) && ($settings['line_four']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_line_four" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['line_four'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['line_five']) && ($settings['line_five']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_line_five" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['line_five'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['profile_name']) && ($settings['profile_name']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_profile_name" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['profile_name'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['subtitle']) && ($settings['subtitle']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_subtitle" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['subtitle'])).'&lt;/argument&gt;<br/>';
        }
        
        if (isset($settings['icon']) && ($settings['icon']!='')) {
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="fbuilder_icon" xsi:type="string"&gt;'.htmlspecialchars($this->encodeHtml($settings['icon'])).'&lt;/argument&gt;<br/>';
        }

        
        if (isset($settings['tabs']) && ($settings['tabs']!='')) {
            usort(
                $settings['tabs'], function ($item1, $item2) {
                    if ($item1['position'] == $item2['position']) {
                        return 0;
                    }
                    return $item1['position'] < $item2['position'] ? -1 : 1;
                }
            );
            $tabType = $tabLabel = [];
            foreach ($settings['tabs'] as $tab) {
                $tabLabel[] = $tab['label'];
            }
            $labels = implode(',', $tabLabel);
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="labels" xsi:type="string"&gt;'.$this->_escaper->escapeHtml($labels).'&lt;/argument&gt;<br/>';
        }
        
        
        if (isset($settings['accordion']) && ($settings['accordion']!='')) {
            if (isset($settings['accordion']['position'])) {
                usort(
                    $settings['accordion'], function ($item1, $item2) {
                        if ($item1['position'] == $item2['position']) {
                            return 0;
                        }
                        return $item1['position'] < $item2['position'] ? -1 : 1;
                    }
                );
            }
            
            $accordionContent = $accordionLabel = [];
            foreach ($settings['accordion'] as $accordion) {
                if (isset($accordion['label'])) {
                    $accordionLabel[] = $this->encodeHtml($accordion['label']);
                }
                $accordionContent[] = $this->encodeHtml($accordion['content']);
            }
            
            if (isset($settings['accordion']['label'])) {
                $labels = implode(',', $accordionLabel);
                $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="accordion_label" xsi:type="string"&gt;'.$this->_escaper->escapeHtml($labels).'&lt;/argument&gt;<br/>';
            }
            
            $accordionData = implode(',', $accordionContent);
            $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="accordion_content" xsi:type="string"&gt;'.$this->_escaper->escapeHtml($accordionData).'&lt;/argument&gt;<br/>';
        }
        
        
        $template = '';

        foreach ($arrContent as $argument) {
            $argumentData = explode('=', $argument);
            if ($argumentData[0]!='template' && isset($argumentData[0]) && isset($argumentData[1])) {
                $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="'.$argumentData[0].'" xsi:type="string"&gt;'.str_replace('"', '', $argumentData[1]).'&lt;/argument&gt;<br/>';
            } else {
                $template = $argumentData[1];
            }
            
        }
        
        $html = '&lt;block '.$class;
        
        $lastDataArr = explode('=', $lastData);
        if (isset($lastDataArr[0]) && isset($lastDataArr[1])) {
            if ($lastDataArr[0]=='template') {
                $template = str_replace('}}', '', $lastDataArr[1]);
            } else {
                $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;argument name="'.$lastDataArr[0].'" xsi:type="string"&gt;'.str_replace('"', '', str_replace('}}', '', $lastDataArr[1])).'&lt;/argument&gt;<br/>';
            }
        }
        
        $html .= ' template='.$template;
        $argumentString .= '&nbsp;&nbsp;&nbsp;&nbsp;&lt;/arguments&gt;';
        $html .= '&gt;<br/>';
        $html .= $argumentString;
        $html .= '<br/>&lt;/block&gt;';
        
        return $html;
    }
    
    /* Get Animation Effect */
    public function getAnimationEffect()
    {
        return [
            'bounce' => 'Bounce',
            'flash' => 'Flash',
            'pulse' => 'Pulse',
            'rubberBand' => 'Rubber Band',
            'shake' => 'Shake',
            'swing' => 'Swing',
            'tada' => 'Tada',
            'wobble' => 'Wobble',
            'bounceIn' => 'Bounce In',
            'fadeIn' => 'Fade In',
            'fadeInDown' => 'Fade In Down',
            'fadeInDownBig' => 'Fade In Down Big',
            'fadeInLeft' => 'Fade In Left',
            'fadeInLeftBig' => 'Fade In Left Big',
            'fadeInRight' => 'Fade In Right',
            'fadeInRightBig' => 'Fade In Right Big',
            'fadeInUp' => 'Fade In Up',
            'fadeInUpBig' => 'Fade In Up Big',
            'flip' => 'Flip',
            'flipInX' => 'Flip In X',
            'flipInY' => 'Flip In Y',
            'lightSpeedIn' => 'Light Speed In',
            'rotateIn' => 'Rotate In',
            'rotateInDownLeft' => 'Rotate In Down Left',
            'rotateInDownRight' => 'Rotate In Down Right',
            'rotateInUpLeft' => 'Rotate In Up Left',
            'rotateInUpRight' => 'Rotate In Up Right',
            'rollIn' => 'Roll In',
            'zoomIn' => 'Zoom In',
            'zoomInDown' => 'Zoom In Down',
            'zoomInLeft' => 'Zoom In Left',
            'zoomInRight' => 'Zoom In Right',
            'zoomInUp' => 'Zoom In Up',
        ];
    }
    
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
}
