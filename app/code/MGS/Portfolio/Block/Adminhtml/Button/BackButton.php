<?php


namespace MGS\Portfolio\Block\Adminhtml\Button;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use MGS\Portfolio\Block\Adminhtml\Button\GenericButton;

class BackButton  extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'class' => 'back',
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'mgs_portfolio_form.mgs_portfolio_form',
                                'actionName' => 'back',
                                'params' => [
                                    true,
                                    [
                                        'back' => 'continue'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
}
