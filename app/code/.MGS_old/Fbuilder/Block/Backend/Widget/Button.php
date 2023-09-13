<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Backend\Widget;

/**
 * Button widget
 *
 * @api
 * @author Magento Core Team <core@magentocommerce.com>
 * @api
 * @since  100.0.2
 */
class Button extends \Magento\Backend\Block\Widget\Button
{

    
    public function getFullActionName()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        return $requestInterface->getFullActionName();
    }
}
