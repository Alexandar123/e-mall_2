<?php

namespace MGS\Lookbook\Block;
use Magento\Framework\App\Action\Action;

abstract class AbstractLookbook extends \Magento\Framework\View\Element\Template
{
	protected $_helper;
	
	protected $_productCollectionFactory;
	
	protected $_imagehelper;
	
	/**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\Context $productContext,
		\MGS\Lookbook\Helper\Data $_helper,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productCollectionFactory,
		\Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_helper = $_helper;
		$this->_productCollectionFactory = $_productCollectionFactory;
		$this->_imagehelper = $productContext->getImageHelper();
		$this->_cartHelper = $productContext->getCartHelper();
		$this->urlHelper = $urlHelper;
    }
	
	public function getImageUrl($lookbook){
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . $lookbook->getImage();
	}
	
	public function getPinHtml($lookbook){
		$pins = $lookbook->getPins();
		$arrPin = json_decode($pins, true);
		$html = '';
		$width = $this->_helper->getStoreConfig('lookbook/general/pin_width');
		$height = $this->_helper->getStoreConfig('lookbook/general/pin_height');
		$background = $this->_helper->getStoreConfig('lookbook/general/pin_background');
		$color = $this->_helper->getStoreConfig('lookbook/general/pin_text');
		$productImageWidth = $this->_helper->getStoreConfig('lookbook/general/popup_image_width');
		$productImageHeight = $this->_helper->getStoreConfig('lookbook/general/popup_image_height');
		$radius = round($width/2);
		if(count($arrPin)>0){
			
			foreach($arrPin as $pin){
				$imgWidth = $pin['imgW'];
				$imgHeight = $pin['imgH'];
				$top = $pin['top'];
				$left = $pin['left'];
				$leftPercent = ($left * 100)/$imgWidth;
				$topPercent = ($top * 100)/$imgHeight;
                $product = $this->getProductInfo($pin['text']);
                // $product_id = $product->getId();
                // $url_onclick = $this->getUrl().'aquickview/index/index/catalog/product/id/'.$product_id;

				$html .= '<div class="pin__type pin__type--area" style="left:'. $leftPercent .'%; top:'. $topPercent .'%">';

				if(trim($pin['custom_text'])!=''){
                    $html .= '<span class="pin-label" style="width:'. $pin['width'] .'px; height:'. $pin['height'] .'px; background:#'. $background .'; color:#'. $color .'; -webkit-border-radius:'. $radius .'px; -moz-border-radius:'. $radius .'px; border-radius:'. $radius .'px; line-height:'. $height .'px; ">'. $pin['label'] .'</span>';
					if(trim($pin['custom_label'])!=''){
						$pinTitle = $pin['custom_label']; 
					}elseif($product = $this->getProductInfo($pin['text'])){
						$pinTitle = $product->getName();
					}
					$html .= '<div class="pin__title">'.$pinTitle.'</div>';
					$html .= '<div class="pin__popup pin__popup--'.$pin['position'].' pin__popup--fade pin__popup_text_content" style="width:'.($productImageWidth + 30).'px"><div class="popup__title">'.$pinTitle.'</div><div class="popup__content">'.$pin['custom_text'].'</div></div>';
				}else{
                    $product_id = $product->getId();
                    $url_onclick = $this->getUrl().'aquickview/index/index/catalog/product/id/'.$product_id;
                    $html .= '<span data-href="'.$url_onclick.'" data-productid="'.$product_id.'" class="pin-label" style="width:'. $pin['width'] .'px; height:'. $pin['height'] .'px; background:#'. $background .'; color:#'. $color .'; -webkit-border-radius:'. $radius .'px; -moz-border-radius:'. $radius .'px; border-radius:'. $radius .'px; line-height:'. $height .'px; ">'. $pin['label'] .'</span>';
					if($product = $this->getProductInfo($pin['text'])){
						// Product Name - Tooltip
						$html .= '<div class="pin__title">'.$product->getName().'</div>';
						$html .= '<div class="pin__popup pin__popup--'.$pin['position'].' pin__popup--fade" style="width:'. (int)($productImageWidth + 30) .'px"><div class="popup__content popup__content--product">';
						// Product Image
						$productImageUrl = $this->_imagehelper->init($product, 'category_page_grid')->resize($productImageWidth, $productImageHeight)->getUrl();
						$html .= '<div class="product-image"><a href="'.$product->getProductUrl().'"><img src="'.$productImageUrl.'" width="'.$productImageWidth.'" height="'.$productImageHeight.'" alt="" /></a></div>';
						
						$html .= '<div class="product-details">';
						// Product Name
						$html .= '<h3><a href="'.$product->getProductUrl().'"> '.$product->getName().'</a></h3>';
						
						// Product Prices
						$html .= $this->getProductPrice($product);
						
						// Add to cart
						$postParams = $this->getAddToCartPostParams($product);
						
						$html .= '<form data-role="tocart-form" action="'.$postParams['action'].'" method="post">
							<input type="hidden" name="product" value="'.$postParams['data']['product'].'">
							<input type="hidden" name="' . Action::PARAM_NAME_URL_ENCODED .'" value="' . $postParams['data'][Action::PARAM_NAME_URL_ENCODED] .'">' . $this->getBlockHtml('formkey') .'
							<button type="submit" title="' . __('Buy Now') . '" class="action tocart primary">
								<span>' . __('Add to cart') . '</span>
							</button>
						</form>';
						
						$html .= '</div></div></div>';
						$html .= '<div class="pin_close"><span class="icon-cross2"  style="width:'. $pin['width'] .'px; height:'. $pin['height'] .'px; -webkit-border-radius:'. $radius .'px; -moz-border-radius:'. $radius .'px; border-radius:'. $radius .'px; line-height:'. $height .'px; "></span></div>';
					}
				}
				$html .= '</div>';
			}
		}
		return $html;
	}
	
	/**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
	
	/**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }
	
	/**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }
	
	/**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
	
	public function getProductInfo($sku){
		$products = $this->_productCollectionFactory->create();
		$products->addStoreFilter($this->_storeManager->getStore()->getId())
			->addAttributeToSelect('*')
			->addAttributeToFilter('status',1)
			->addAttributeToFilter('visibility',array('neq'=>1))
			->addFieldToFilter('sku', $sku);

		if(count($products)>0){
			foreach($products as $product){
				if($product->getId()){
					return $product;
				}
			}
		}
		return false;
	}
	
	/**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * Specifies that price rendering should be done for the list of products
     * i.e. rendering happens in the scope of product list, but not single product
     *
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }
}
