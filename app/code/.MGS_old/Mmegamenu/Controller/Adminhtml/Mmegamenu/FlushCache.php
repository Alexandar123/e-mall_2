<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use MGS\Mmegamenu\Helper\Data;
use Magento\Framework\View\LayoutFactory;
use \Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;

class FlushCache extends \MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Backend\App\Action\Context       $context  
     * @param \Magento\Framework\App\ResourceConnection $resource 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Data $viewHelper,
        LayoutFactory $layoutFactory,
        ResultLayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory
        ) {
        parent::__construct($context, $coreRegistry,
                            $fileFactory, $viewHelper, $layoutFactory,
                            $resultLayoutFactory,$resultPageFactory);
        $this->_resource = $resource;
    }
    
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $resource   = $this->_resource;
            $table      = $resource->getTableName('mgs_megamenu_cache');
            $connection = $resource->getConnection();
            $connection->truncateTable($table);
            $this->messageManager->addSuccess(__('The Mega Menu Cache has been flushed.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong in progressing.'));
        }
        return $resultRedirect->setPath('*/*/parents');
    }
}
