<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Cms;

/**
 * Main contact form block
 */
class Block extends \Magento\Cms\Block\Block
{
    
    protected $_panelHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context    $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\BlockFactory            $blockFactory
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \MGS\Fbuilder\Helper\Data $panelHelper,
        array $data = []
    ) {
        $this->_panelHelper = $panelHelper;
        parent::__construct($context, $filterProvider, $storeManager, $blockFactory, $data);
    }
    
    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $blockId = $this->getBlockId();
        $html = '';
        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /**
 * @var \Magento\Cms\Model\Block $block 
*/
            $block = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);
            if ($block->isActive()) {
                $canUsePanel = $this->_panelHelper->acceptToUsePanel();
                if ($canUsePanel) {
                    $html .= '<span class="builder-container child-builder static-can-edit">
					<span class="edit-panel child-panel">
						<ul>
							<li><a title="'.__('Edit').'" class="popup-link" href="'.str_replace('https:', '', str_replace('http:', '', $this->getUrl('fbuilder/edit/staticblock', ['cms'=>'block', 'id'=>$block->getIdentifier()]))).'"><em class="fa fa-edit">&nbsp;</em></a></li>
						</ul>
					</span>
					<span id="static_'.$block->getIdentifier().'">';
                }
                
                $html .= $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
                
                if ($canUsePanel) {
                    $html .= '</span></span>';
                }
            }
            $html .= '<!--Identifier: '.$block->getIdentifier().', Block Id: '.$block->getId().'-->';
        }
        return $html;
    }
}
