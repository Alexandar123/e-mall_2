<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbook;

use Magento\Backend\App\Action;

class Chooser extends \MGS\Lookbook\Controller\Adminhtml\Lookbook
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->layoutFactory->create();
        $pagesGrid = $layout->createBlock(
            'MGS\Lookbook\Block\Adminhtml\Widget\Lookbook',
            '',
            ['data' => ['id' => $uniqId]]
        );
        $html = $pagesGrid->toHtml();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($html);
    }
}
