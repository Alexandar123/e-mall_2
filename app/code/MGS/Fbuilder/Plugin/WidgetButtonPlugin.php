<?php

namespace MGS\Fbuilder\Plugin;

class WidgetButtonPlugin
{
    public function aftergetTemplate(
        \Magento\Backend\Block\Widget\Button $subject,
        $result
    ) {

        return 'MGS_Fbuilder::widget/button.phtml';
    }
}
