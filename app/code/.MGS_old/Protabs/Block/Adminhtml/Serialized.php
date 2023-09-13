<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Protabs\Block\Adminhtml;

class Serialized extends \Magento\Config\Block\System\Config\Form\Fieldset
{
	protected $_objectManager;
	protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();
	protected $_j =0;
	protected $_fields = array();
	protected $_checkStoreView = false;
	protected $_checkWebsite = false;
	
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
		$this->_objectManager = $objectManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Protabs\Model\Protabs');
	}
	
	public function getWebsiteId(){
		$storeModel = $this->_objectManager->create('Magento\Store\Model\Store');
		$store = $storeModel->load($this->getRequest()->getParam('store'));
		return $store->getWebsiteId();
	}
	
	protected function getFieldsForm(){
		if($storeId = $this->getRequest()->getParam('store')){
			$model  = $this->getModel()->getCollection()->addFieldToFilter('scope', 'stores')->addFieldToFilter('scope_id', $storeId)->setOrder('position', 'ASC');;
			if(count($model)>0){
				$this->_checkStoreView = true;
			}else{
				$model  = $this->getModel()->getCollection()->addFieldToFilter('scope', 'websites')->addFieldToFilter('scope_id', $this->getWebsiteId())->setOrder('position', 'ASC');;
			}
		}
		elseif($websiteId = $this->getRequest()->getParam('website')){
			$model  = $this->getModel()->getCollection()->addFieldToFilter('scope', 'websites')->addFieldToFilter('scope_id', $websiteId)->setOrder('position', 'ASC');;
			if(count($model)>0){
				$this->_checkWebsite = true;
			}
		}else{
			$model  = $this->getModel()->getCollection()->addFieldToFilter('scope', 'default')->setOrder('position', 'ASC');;
		}
		if(count($model)==0){
			$model  = $this->getModel()->getCollection()->addFieldToFilter('scope', 'default')->setOrder('position', 'ASC');;
		}
		$fields 	= array();
		$i=1;
		foreach ($model as $field) {
			$fields[$i++] = $field;
		}
		$this->_fields = $fields;
		return $this->_fields;
		
	}
	
	protected function _getHeaderCommentHtml($element)
    {
		$html = '<table class="form-list" cellspacing="0"><tbody><tr id="row_protabs_items_items"><td class="value" style="width:650px">';
        $html .= '<div id="field-template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';

        $html .= '<ul id="field-items" style="list-style:none">';
        if (count($this->getFieldsForm())) {
			$i = 1;
            foreach ($this->getFieldsForm() as $field) {
                $html .= $this->_getRowTemplateHtml($field->getId());
            }
        }
        $html .= '</ul>';
        $html .= '<div class="button-container"><button type="button" class="button action-configure" id="add-new-tab"><span>' . __('Add Tab Item') . '</span></button></div></td>';
		if($this->getRequest()->getParam('website')){
			$html .= '<td class="use-default"><input type="checkbox" onclick="toggleValueElements(this, Element.previous(this.parentNode))"';
			if($this->_checkWebsite == false){
				$html .= ' checked="checked"';
			}
			
			$html .= ' class="checkbox config-inherit" value="1" id="protabs_items_items_inherit" name="remove_website"> <label class="inherit" for="protabs_items_items_inherit">' . __('Use Default') . '</label></td>';
		}
		if($this->getRequest()->getParam('store')){
			$html .= '<td class="use-default"><input type="checkbox" onclick="toggleValueElements(this, Element.previous(this.parentNode))"';
			
			if($this->_checkStoreView == false){
				$html .= ' checked="checked"';
			}
			$html .= ' class="checkbox config-inherit" value="1" id="protabs_items_items_inherit" name="remove_store_view"> <label class="inherit" for="protabs_items_items_inherit">' . __('Use Website') . '</label></td>';
		}
		$html .= '<td class="scope-label">' . __('[STORE VIEW]') . '</td><td class=""></td></tr></tbody></table>';
        return $html;
    }
	
	protected function _getRowTemplateHtml($i=0)
    {	
        $html = '<li style="border:1px solid #ccc; padding:5px; margin-bottom:5px">';
		
		$html .= '<table cellspacing="0" class="list-item">';
		
		$html .= $this->_getTitle($i);
		$html .= $this->_getType($i);
		$html .= $this->_getPosition($i);
		$html .= $this->_getValue($i);
		
		$html .= '<td class="label">';
		$html .= '<label style="width:78px; padding-right:0"></label>';
		$html .= '</td>';
		
		$html .= '<td class="value" style="padding-top:8px">';
		$html .= $this->_getRemoveRowButtonHtml();
		$html .= '</td>';
        
		$html .= '</table>';
        $html .= '</li>';

        return $html;
    }
	
	protected function _getTitle($i=0){
		$field 	= $this->getModel()->load($i);
		$select = $field->getTitle();

		
		$html = '';
		$html .= '<tr>';
		
		$html .= '<td class="label">';
		$html .= '<label style="margin-right: 8px;">' . __('Label') . '</label>';
		$html .= '</td>';
		
		$html .= '<td class="value" style="padding-top:8px">';
		$html .= '<input type="text" class="input-text" value="'.$field->getTitle().'" name="title[]" />';
		$html .= '</td>';
		
		$html .= '</tr>';
		return $html;
	}
	
	protected function _getPosition($i=0){
		$field 	= $this->getModel()->load($i);
		$select 	= (int)$field->getPosition();
		
		$html = '';
		$html .= '<tr>';
		
		$html .= '<td class="label">';
		$html .= '<label style="margin-right: 8px;">' . __('Position') . '</label>';
		$html .= '</td>';
		
		$html .= '<td class="value" style="padding-top:8px">';
		$html .= '<input type="text" class="input-text" value="'.$field->getPosition().'" name="position[]" />';
		$html .= '</td>';
		
		$html .= '</tr>';
		return $html;
	}
	
	protected function _getType($i=0){
		$field 	= $this->getModel()->load($i);
		$select 	= $field->getTabType();

		
		$html = '';
		$html .= '<tr class="item-type">';
		
		$html .= '<td class="label">';
		$html .= '<label style="margin-right: 8px;">' . __('Type') . '</label>';
		$html .= '</td>';
		
		$html .= '<td class="value" style="padding-top:8px">';
		$html .= '<select name="tab_type[]" class="select-type">';
		$html .= '<option value="attribute"';
		if($select=='attribute'){
			$html .= ' selected="selected"';
		}
		$html .='>' . __('Attribute') . '</option>';
		
		$html .= '<option value="static"';
		if($select=='static'){
			$html .= ' selected="selected"';
		}
		$html .='>' . __('Static Block') . '</option>';
		
		$html .= '<option value="product.attributes"';
		if($select=='product.attributes'){
			$html .= ' selected="selected"';
		}
		$html .='>' . __('Additional Information') . '</option>';
		
		$html .= '<option value="reviews.tab"';
		if($select=='reviews.tab'){
			$html .= ' selected="selected"';
		}
		$html .='>' . __('Reviews') . '</option>';
		
		$html .= '</select>';
		$html .= '</td>';
		
		$html .= '</tr>';
		return $html;
	 }
	
	protected function _getValue($i=0){
		$field 	= $this->getModel()->load($i);
		$select 	= $field->getValue();

		$html = '';
		$html .= '<tr class="item-value">';
		
		$html .= '<td class="label">';
		$html .= '<label style="margin-right: 8px;">' . __('Value') . '</label>';
		$html .= '</td>';
		
		$html .= '<td class="value" style="padding-top:8px">';
		$html .= '<input type="text" class="input-text" value="'.$field->getValue().'" name="value[]"/>';
		$html .= '<p class="note"><span>attribute_code for Attribute type</span><br/><span>Identifier for Static Block type</span><br/><span>Blank for other types</span></p>';

		$html .= '</td>';
		
		$html .= '</tr>';
		return $html;
	}
	
	protected function _getRemoveRowButtonHtml()
    {
        $html = '<button type="button" class="button action-configure remove-tab"><span>' . __('Remove this item') . '</span></button>';
		return $html;
    }

}
