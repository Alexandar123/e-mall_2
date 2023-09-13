<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Block\Adminhtml\Edit\Tab;

use Magento\Framework\Escaper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Image extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var Escaper
     */
    protected $_escaper;

    protected $_helper;
	
	protected $_filesystem;
	
	protected $_file;
	
    protected $request;
	
    protected $lookbookFactory;
	
	/**
	 * @var Magento\Backend\Helper\Data
	 */
	protected $HelperBackend;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        Escaper $escaper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Backend\Helper\Data $HelperBackend,
		\MGS\Lookbook\Helper\Data $_helper,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Backend\Block\Admin\Formkey $formKey,
		\MGS\Lookbook\Model\LookbookFactory $lookbookFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
		$this->setType('hidden');
		$this->_helper = $_helper;
		$this->_filesystem = $filesystem;
		$this->_file = $file;
		$this->HelperBackend = $HelperBackend;
		$this->formKey = $formKey;
		$this->_storeManager = $storeManager;
		$this->request = $request;
		$this->lookbookFactory = $lookbookFactory;
    }
	
	public function getElementHtml()
    {
		$hotspot_icon  = $this->getMediaUrl().'lookbook/icons/default/hotspot-icon.png';	
		$width = $this->_helper->getStoreConfig('lookbook/general/pin_width');
		$height = $this->_helper->getStoreConfig('lookbook/general/pin_height');
		$background = $this->_helper->getStoreConfig('lookbook/general/pin_background');
		$color = $this->_helper->getStoreConfig('lookbook/general/pin_text');
		$okText = __('Save');
		$deleteText = __('Delete');
		$cancelText = __('Cancel');
		$addPinText = __('Add Pin');
		$radius = round($width/2);
	
        $upload_action  = $this->HelperBackend->getUrl('adminhtml/lookbook/upload');
		$media_url  = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
		$upload_folder_path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
		$sizeLimit = $this->_helper->getStoreConfig('lookbook/general/max_upload_filesize');
		$allowed_extensions = implode('","',explode(',',$this->_helper->getStoreConfig('lookbook/general/allowed_extensions')));
		
		$html = '<style>
			.image-annotate-area, .image-annotate-edit-area{
				background: #'.$background.';
				color: #'.$color.';
				-webkit-border-radius:'.$radius.'px;
				-moz-border-radius:'.$radius.'px;
				border-radius:'.$radius.'px;
				line-height:'.$height.'px;
			}
		</style>';
		
		$html .= '<input type="hidden" id="default_pin_text" value="'.$this->_helper->getStoreConfig('lookbook/general/pin_default').'"/>
			<input type="hidden" id="ok_text" value="'.$okText.'"/>
			<input type="hidden" id="delete_text" value="'.$deleteText.'"/>
			<input type="hidden" id="cancel_text" value="'.$cancelText.'"/>
			<input type="hidden" id="add_text" value="'.$addPinText.'"/>
			<input type="hidden" id="pin_width" value="'.$width.'"/>
			<input type="hidden" id="pin_height" value="'.$height.'"/>
			<input type="hidden" id="check_product_url" value="'. $this->HelperBackend->getUrl('adminhtml/lookbook/checkproduct').'"/>
			<input type="hidden" id="load_product_url" value="'. $this->HelperBackend->getUrl('adminhtml/lookbook/loadproduct').'"/>';
		
		$html .= '<script type="text/javascript">
				require([
					"jquery",
					"jquery/ui",
					"lookbookUploader"
				], function(jQuery){
					(function($) {
						$(document).ready(function() {
							InitHotspotBtn();
							img_uploader = new qq.FileUploader({
								element: document.getElementById(\'maket_image\'),
								action: "'.$upload_action.'",
								params: {"form_key":"'.$this->formKey->getFormKey().'"},
								multiple: false,
								allowedExtensions: ["'.$allowed_extensions.'"],
								sizeLimit: '. $sizeLimit .',
								onComplete: function(id, fileName, responseJSON){                           
									if (responseJSON.success) 
									{
										if ($(\'#LookbookImageBlock\')) 
										{
										  $.each($(\'#LookbookImageBlock\').children(),function(index) {
											$(this).remove();
										  });
										}
									   $(\'#LookbookImageBlock\').append(\'<img id="LookbookImage"';
									   $html .= ' src="'.$media_url.'lookbook/\'+responseJSON.filename+\'" alt="\'+responseJSON.filename+\'"'; 
									   $html .= ' width="\'+responseJSON.dimensions.width+\'" height="\'+responseJSON.dimensions.height+\'"/>\');
									   
										if ($(\'#advice-required-entry-image\')) 
										{
											$(\'#advice-required-entry-image\').remove();
										}
										$(\'#LookbookImage\').load(function(){
										   $(this).attr(\'width\',responseJSON.dimensions.width);
										   $(this).attr(\'height\',responseJSON.dimensions.height);
										   InitHotspotBtn();
										});                       
										$(\'#image\').val(\'lookbook/\'+responseJSON.filename);
										$(\'#image\').removeClass(\'validation-failed\');
									}

								}
							});
						});
					})(jQuery);
				});
				
				
				function InitHotspotBtn() {
					require([
						"jquery",
						"jquery/ui",
						"lookbookAnnotate",
					], function(jQuery){
						(function($) {
							if ($("img#LookbookImage").attr("id")) {
								var annotObj = $("img#LookbookImage").annotateImage({            				    
									editable: true,
									useAjax: false,';
									if($id = $this->request->getParam('id')){
										$lookbook = $this->lookbookFactory->create()->load($id);
										if ($lookbook->getPins()){
											$html .= 'notes: '.$lookbook->getPins().',';
										}
									}
									
									$html .= 'input_field_id: "pins"                                          
								});
									
								return annotObj;
							}else{
								return false;
							}
						})(jQuery);
					});
					
				};
				
				function setBlankPinLabel(){
					require([
						"jquery",
						"jquery/ui",
					], function(jQuery){
						(function($) {
							$("#image-annotate-label").val("");
						})(jQuery);
					});
					
				}
				
				
				
                </script>
                <div id="LookbookImageBlock">';

        if ($this->getValue()) {
            $img_src = $media_url.$this->getValue();
            $img_path = $upload_folder_path.$this->getValue();
            if ($this->_file->isExists($img_path)) {  
                $dimensions = $this->_helper->getImageDimensions($img_path);
                
                $html .= '<img id="LookbookImage" src="'.$img_src.'" />';
            }
            else
            {
                $html .= '<h4 id="LookbookImage" style="color:red;">File '.$img_src.' doesn\'t exists.</h4>';
            }     
        }

        $html .= '</div>
                <div id="maket_image">       
                    <noscript>          
                        <p>Please enable JavaScript to use file uploader.</p>
                        <!-- or put a simple form for upload here -->
                    </noscript>         
                </div>';
				
		$html.= parent::getElementHtml();
		
		$html.= '<p class="note" style="clear:both; float:left;">Allowed file extensions: ' . $this->_helper->getStoreConfig('lookbook/general/allowed_extensions') . '</p>';

        return $html;
    }

}
