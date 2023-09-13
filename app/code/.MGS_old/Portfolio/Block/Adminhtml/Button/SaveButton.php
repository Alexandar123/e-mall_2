<?php


namespace MGS\Portfolio\Block\Adminhtml\Button;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use MGS\Portfolio\Block\Adminhtml\Button\GenericButton;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'mgs_portfolio_form.mgs_portfolio_form',
                                'actionName' => 'save'
                            ]
                        ]
                    ]
                ]
            ],

        ];
    }
}
