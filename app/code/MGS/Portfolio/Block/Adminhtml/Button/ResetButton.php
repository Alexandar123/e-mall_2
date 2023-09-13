<?php


namespace MGS\Portfolio\Block\Adminhtml\Button;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use MGS\Portfolio\Block\Adminhtml\Button\GenericButton;

class ResetButton  extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData() {
        return [
            'label' => __('Reset'),
            'on_click' => 'javascript: location.reload();', 'class' => 'reset', 'sort_order' => 30
        ];
    }
}
