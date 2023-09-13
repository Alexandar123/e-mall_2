<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Block\Adminhtml\Locator;

use MGS\StoreLocator\Model\StoreFactory;
/**
 * User edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_storeFactory = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        StoreFactory $storeFactory,
        array $data = []
    ) {
        $this->_storeFactory = $storeFactory;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_locator';
        $this->_blockGroup = 'MGS_StoreLocator';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Store'));
        $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $locator = $this->_storeFactory->load($this->getRequest()->getParam('user_id'));
        if ($locator) {
            return __("Edit Store '%1'", $locator->getName());
        } else {
            return __('New Store');
        }
    }
}
