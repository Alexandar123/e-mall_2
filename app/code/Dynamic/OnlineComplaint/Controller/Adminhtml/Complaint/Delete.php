<?php
namespace Dynamic\OnlineComplaint\Controller\Adminhtml\Complaint;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    public $complaintFactory;
    
    public function __construct(
        Context $context,
        \Dynamic\OnlineComplaint\Model\ComplaintFactory $complaintFactory
    ) {
        $this->complaintFactory = $complaintFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        try {
            $complaintModel = $this->complaintFactory->create();
            $complaintModel->load($id);
            $complaintModel->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the Complaint Details.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dynamic_OnlineComplaint::delete');
    }
}