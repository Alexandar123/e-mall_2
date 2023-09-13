<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbook;

class Edit extends \MGS\Lookbook\Controller\Adminhtml\Lookbook
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit sitemap
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('MGS\Lookbook\Model\Lookbook');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('lookbook_lookbook', $model);

        // 5. Build edit form
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit %1', $model->getName()) : __('New Lookbook'),
            $id ? __('Edit %1', $model->getName()) : __('New Lookbook')
        )->_addContent(
            $this->_view->getLayout()->createBlock('MGS\Lookbook\Block\Adminhtml\Edit')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Lookbook'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New Lookbook')
        );
        $this->_view->renderLayout();
    }
}
