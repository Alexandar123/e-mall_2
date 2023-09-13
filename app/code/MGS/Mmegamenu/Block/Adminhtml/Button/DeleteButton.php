<?php 

namespace MGS\Mmegamenu\Block\Adminhtml\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface {
     /**
     * @return array
     */
    public function getButtonData(){ 
        return [ 
            'label' => __('Delete'), 
            'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to delete?') . '\', \'' . $this->getDeleteUrl() . '\')', 
            'class' => 'delete', 
            'sort_order' => 20 
            ]; 
        } 
    public function getDeleteUrl() { 
        $id = $this->getBlockId();
        return $this->getUrl('*/*/delete', ['id' => $id]); 
    } 
}