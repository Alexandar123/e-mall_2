<?php

namespace MGS\Fbuilder\Block\Widget;

use Magento\Framework\View\Element\Template;

class Form extends Template
{
    public function getFieldHtml($field, $blockId)
    {
        $html = '<label class="label" for="'.$field['identifier'].'_'.$blockId.'"><span>'.$field['label'].'</span></label>';
        $html .= '<div class="control">';
        
        switch ($field['type']) {
        case 'text';
            $html .= $this->getTextField($field, $blockId);
            break;
        case 'textarea';
            $html .= $this->getTextareaField($field, $blockId);
            break;
        case 'file';
            $html .= $this->getUploadField($field, $blockId);
            break;
        case 'select';
            $html .= $this->getDropdownField($field, $blockId);
            break;
        case 'radios';
            $html .= $this->getRadiosField($field, $blockId);
            break;
        case 'checkboxes';
            $html .= $this->getCheckboxField($field, $blockId);
            break;
        case 'multiselect';
            $html .= $this->getMultiselectField($field, $blockId);
            break;
        case 'date';
            $html .= $this->getDateField($field, $blockId);
            break;
        default:
            break;
        }
        $html .= '</div>';
        return $html;
    }
    
    public function getTextField($field, $blockId)
    {
        $html = '<input type="text" title="'.$field['label'].'" id="'.$field['identifier'].'_'.$blockId.'" name="'.$field['identifier'].'"';
        if ($field['required']) {
            $html .= ' data-validate="{required:true}"';
        }
        $html .= ' class="input-text';
        if (isset($field['validate'])) {
            $html .= ' '.implode(' ', $field['validate']);
        }
        $html .= '"/>';
        //
        
        
        return $html;
    }
    
    public function getTextareaField($field, $blockId)
    {
        $html = '<textarea title="'.$field['label'].'" id="'.$field['identifier'].'_'.$blockId.'" name="'.$field['identifier'].'" rows="3"';
        if ($field['required']) {
            $html .= ' data-validate="{required:true}"';
        }
        $html .= ' class="input-text"></textarea>';
        
        return $html;
    }
    
    public function getUploadField($field, $blockId)
    {
        $html = '<input type="file" title="'.$field['label'].'" id="'.$field['identifier'].'_'.$blockId.'" name="'.$field['identifier'].'"';
        if ($field['required']) {
            $html .= ' data-validate="{required:true}"';
        }
        $html .= ' class="input-text"';
        
        if ($field['extension'] != '') {
            $exAccept = str_replace(',', ',.', $field['extension']);
            $html .= ' accept=".'.$exAccept.'"';
        }
        $html .= '/>';
        
        return $html;
    }
    
    public function getDropdownField($field, $blockId)
    {
        $html = '<select id="'.$field['identifier'].'_'.$blockId.'" class="select" title="'.$field['label'].'" name="'.$field['identifier'].'"';
        if ($field['required']) {
            $html .= ' aria-required="true"';
        }
        $html .= '><option value=""></option>';
                
        if (trim($field['options'])!='') {
            $options = explode(',', trim($field['options']));
            foreach ($options as $option) {
                $html .= '<option value="'.$option.'">'.$option.'</option>';
            }
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    public function getRadiosField($field, $blockId)
    {
        $html = '';
        if (trim($field['options'])!='') {
            $options = explode(',', trim($field['options']));
            $i=0; foreach ($options as $option) {
                $i++;
                $html .= '<div class="field choice">';
                $html .= '<input type="radio" name="'.$field['identifier'].'" class="radio" value="'.$option.'" id="'.$field['identifier'].'_'.$blockId.'_'.$i.'"';
                
                if ($field['required'] && $i==1) {
                    $html .= ' checked="checked"';
                }
                
                $html .= '><label class="label" for="'.$field['identifier'].'_'.$blockId.'_'.$i.'"><span>'.$option.'</span></label></div>';
            }
        }
        
        return $html;
    }
    
    public function getCheckboxField($field, $blockId)
    {
        $html = '';
        if (trim($field['options'])!='') {
            $options = explode(',', trim($field['options']));
            $i=0; foreach ($options as $option) {
                $i++;
                $html .= '<div class="field choice">';
                $html .= '<input type="checkbox" name="'.$field['identifier'].'[]" class="checkbox" value="'.$option.'" id="'.$field['identifier'].'_'.$blockId.'_'.$i.'"';
                
                if ($field['required']) {
                    $html .= ' data-validate="{required:true}"';
                }
                
                $html .= '><label class="label" for="'.$field['identifier'].'_'.$blockId.'_'.$i.'"><span>'.$option.'</span></label></div>';
            }
        }
        
        return $html;
    }
    
    public function getMultiselectField($field, $blockId)
    {
        $html = '<select id="'.$field['identifier'].'_'.$blockId.'" multiple="multiple" class="select multiselect" title="'.$field['label'].'" name="'.$field['identifier'].'[]"';
        if ($field['required']) {
            $html .= ' aria-required="true"';
        }
        $html .= '>';
        if (trim($field['options'])!='') {
            $options = explode(',', trim($field['options']));
            foreach ($options as $option) {
                $html .= '<option value="'.$option.'">'.$option.'</option>';
            }
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    public function getDateField($field, $blockId)
    {
        $html = '<input type="text" name="'.$field['identifier'].'" id="'.$field['identifier'].'_'.$blockId.'" value=""';

        if ($field['required']) {
            $html .= ' data-validate="{required:true}"';
        }
        $html .= '/>';
        
        $html .= '<script type="text/javascript">
			require(["jquery", "mage/calendar"], function($){
                    $("#'.$field['identifier'].'_'.$blockId.'").calendar({
                        showsTime: false,
                        dateFormat: "'.$this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT).'",
                        buttonText: "Select Date"
                    })
            });
			</script>';
        
        return $html;
    }
}
