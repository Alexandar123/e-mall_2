<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Block\Adminhtml;

/**
 * Sitemap edit form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Editparent extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'MGS_Mmegamenu';

        parent::_construct();

		$this->buttonList->add(
			'saveandcontinue',
			[
				'label' => __('Save and Continue Edit'),
				'class' => 'save',
				'data_attribute' => [
					'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
				]
			],
			-100
		);

		if ($this->getRequest()->getParam('id') != 1) {
            $this->buttonList->update('delete', 'label', __('Delete'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('mmegamenu_parents')->getId()) {
            return __('Edit %1', $this->_coreRegistry->registry('mmegamenu_parents')->getTitle());
        } else {
            return __('New Menu');
        }
    }
	
	/**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
	
	/**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/deleteparent', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }
	
	public function getBackUrl()
    {
        return $this->getUrl('*/*/parents');
    }
}
