<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block\Adminhtml\Multiple;

/**
* CustomFormField Customformfield field renderer
*/
class Images extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
    * Get the after element html.
    *
    * @return mixed
    */
    public function getAfterElementHtml()
    {
        // here you can write your code.
		$content = "";
		$content .= '<div id="portfolio-gallery">
			<div class="content">
				<div class="image image-placeholder">
					<div id="portfolio_uploader" class="uploader">
						<div class="fileinput-button form-buttons button">
							<input id="multiple_image" name="multiple_image" type="file">
						</div>
						<div class="clear"></div>
					</div>
					<div class="product-image-wrapper">
						<p class="image-placeholder-text">Browse to find or drag image here</p>
					</div>
				</div>
				<div class="admin__data-grid-outer-wrap loading-gallery">
					<div class="admin__data-grid-loading-mask">
						<div class="spinner">
							<span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
						</div>
					</div>
				</div>
			</div>
		</div>';
        return $content;
    }
}
