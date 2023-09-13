<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Panel;

/**
 * Main contact form block
 */
class ProductBlock extends \MGS\Fbuilder\Block\Panel\Block
{
    public function getBlocks()
    {
        $blockName = $this->getBlockName();
        $product = $this->getProduct();
        $storeId = $this->_storeManager->getStore()->getId();
        $blocks = $this->_childCollectionFactory->create()
            ->addFieldToFilter('block_name', $blockName)
            ->addFieldToFilter('page_type', 'product')
            ->addFieldToFilter('product_id', $product->getId())
            ->setOrder('position', 'ASC');
        if ($this->getOverriden()!=0) {
            $blocks->addFieldToFilter('store_id', $storeId);
        } else {
            $blocks->addFieldToFilter('store_id', 0);
        }
        
        return $blocks;
    }
    
    public function getEditChildHtml($block, $child)
    {
        $html = '<div class="edit-panel child-panel"><ul>';

        $html .= '<li class="sort-handle"><a href="#" onclick="return false;" title="' . __('Move Block') . '"><em class="fa fa-arrows">&nbsp;</em></a></li>';

        $html .= '<li><a href="' . $this->getUrl('fbuilder/create/element', ['product_id'=>$child->getProductId(), 'page_type'=>'product', 'overriden'=>$child->getStoreId(), 'block' => $block, 'id' => $child->getId(), 'type' => $child->getType()]) . '" class="popup-link" title="' . __('Edit') . '"><em class="fa fa-edit">&nbsp;</em></a></li>';
        
        $html .= '<li><a href="#" title="' . __('Copy') . '" onclick="copyBlock('.$child->getId().');return false;"><em class="fa fa-copy">&nbsp;</em></a></li>';

        $html .= '<li class="change-col"><a href="javascript:void(0)" title="' . __('Change column setting') . '"><em class="fa fa-columns">&nbsp;</em></a><ul>';

        for ($i = 1; $i <= 12; $i++) {
            $html .= '<li><a id="changecol-'.$child->getId().'-'.$i.'" href="' . str_replace('https:', '', str_replace('http:', '', $this->getUrl('fbuilder/element/changecol', ['id' => $child->getId(), 'col' => $i]))) . '" onclick="changeBlockCol(this.href, '.$child->getCol().', '.$child->getId().'); return false"';
            if ($i == $child->getCol()) {
                $html .= ' class="active"';
            }
            $html .='><span>' . $i . '/12</span></a></li>';
        }

        $html .= '</ul></li>';

        $html .= '<li><a href="' . str_replace('https:', '', str_replace('http:', '', $this->getUrl('fbuilder/element/delete', ['id' => $child->getId()]))) . '" onclick="if(confirm(\'' . __('Are you sure you would like to remove this block?') . '\')) removeBlock(this.href, '.$child->getId().'); return false" title="' . __('Delete Block') . '"><em class="fa fa-trash">&nbsp;</em></a></li>';
        $html .= '</ul></div>';

        return $html;
    }
}
