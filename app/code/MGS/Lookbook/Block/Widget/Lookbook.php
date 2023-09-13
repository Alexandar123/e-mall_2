<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Widget;

/**
 * Widget to display link to CMS page
 */
class Lookbook extends \MGS\Lookbook\Block\AbstractLookbook implements \Magento\Widget\Block\BlockInterface
{

	/**
     * @var \MGS\Lookbook\Model\LookbookFactory
     */
    protected $lookbookFactory;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Block\Product\Context $productContext,
		\MGS\Lookbook\Helper\Data $_helper,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productCollectionFactory,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\MGS\Lookbook\Model\LookbookFactory $lookbookFactory,
        array $data = []
    ) {
        parent::__construct($context, $productContext, $_helper, $_productCollectionFactory, $urlHelper, $data);
		$this->lookbookFactory = $lookbookFactory;
    }

	public function getLookbook(){
		$lookbookId = $this->getData('lookbook_id');
		$lookbook = $this->lookbookFactory->create()->load($lookbookId);
		
		if ($lookbook->getId() && $lookbook->getStatus()) {
			return $lookbook;
		}
		
		return;
	}
}
