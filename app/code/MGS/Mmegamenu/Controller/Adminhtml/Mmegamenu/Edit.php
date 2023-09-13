<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use MGS\Mmegamenu\Helper\Data;
use Magento\Framework\View\LayoutFactory;
use \Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;

class Edit extends \MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu
{
   
    public function execute()
    {
        $id = $this->getRequest()->getParam('megamenu_id');
        $model = $this->_objectManager->create('MGS\Mmegamenu\Model\Mmegamenu');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This post no longer exists.'));
                $this->_redirect('adminhtml/mmegamenu/index');
                return;
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_megamenu', $model);
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Megamenu') : __('Add Item'),
            $id ? __('Edit Post') : __('Add Item')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Mmegamenu'));
        $this->_view->getPage()->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('Edit Item'));
        $this->_view->getLayout()->getBlock('megamenu_edit');
        $this->_view->renderLayout();
    }
}
