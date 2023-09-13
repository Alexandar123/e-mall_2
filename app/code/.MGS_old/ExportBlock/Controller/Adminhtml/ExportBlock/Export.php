<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ExportBlock\Controller\Adminhtml\ExportBlock;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
class Export extends \MGS\ExportBlock\Controller\Adminhtml\ExportBlock
{
	/**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $fileFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, \Magento\Framework\App\Response\Http\FileFactory $fileFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
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
		$collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
		$content = "<static_block>\n";
		foreach ($collection as $block) {
            $content .= "\t<item>\n";
            $content .= "\t\t<title><![CDATA[".$block->getTitle()."]]></title>\n";
            $content .= "\t\t<identifier><![CDATA[".$block->getIdentifier()."]]></identifier>\n";
            $content .= "\t\t<content><![CDATA[".$block->getContent()."]]></content>\n";
            $content .= "\t</item>\n";
        }
		$content .= '</static_block>';
		
		try{
			if($content!=''){
				$fileName = 'static_blocks.xml';
				return $this->fileFactory->create($fileName, $content, 'var');
			}
		}catch(\Exception $e){
			$this->messageManager->addError(__($e->getMessage()));
		}

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
    }
}
