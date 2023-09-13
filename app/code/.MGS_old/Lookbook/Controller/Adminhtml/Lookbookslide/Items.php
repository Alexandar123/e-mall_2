<?php

namespace MGS\Lookbook\Controller\Adminhtml\Lookbookslide;

class Items extends \Magento\Catalog\Controller\Adminhtml\Product
{
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    )
    {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    public function execute()
    {
        $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('lookbook.edit.tab.items')
            ->setLookbookIds($this->getRequest()->getPost('lookbook_items', null));
        return $resultLayout;
    }
}
