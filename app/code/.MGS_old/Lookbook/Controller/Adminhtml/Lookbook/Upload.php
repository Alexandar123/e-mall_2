<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbook;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \MGS\Lookbook\Controller\Adminhtml\Lookbook
{
    protected $uploader;
	
	private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $min_image_width = 0;
    private $min_image_height = 0;
    private $max_image_width = 0;
    private $max_image_height = 0;    
    private $filemodel;
	
	protected $scopeConfig;
	protected $uploadXhr;
	protected $uploadfileForm;
	protected $helper;
	protected $_file;
	protected $_imageFactory;
	
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
		\MGS\Lookbook\Model\Uploadedfilexhr $uploadXhr,
		\MGS\Lookbook\Helper\Data $helper,
		\Magento\Framework\Filesystem\Driver\File $file,
		\Magento\Framework\Image\AdapterFactory $imageFactory,
		\MGS\Lookbook\Model\Uploadedfileform $uploadfileForm
	)
    {
        parent::__construct($context);
		$this->scopeConfig = $scopeConfig;
		$this->uploadXhr = $uploadXhr;
		$this->helper = $helper;
		$this->_file = $file;
		$this->uploadfileForm = $uploadfileForm;
		$this->_imageFactory = $imageFactory;
		
		$sizeLimit = $this->scopeConfig->getValue('lookbook/general/max_upload_filesize');
		$this->min_image_width = $this->scopeConfig->getValue('lookbook/general/min_image_width');
		$this->min_image_height = $this->scopeConfig->getValue('lookbook/general/min_image_height');
		$this->max_image_width = $this->scopeConfig->getValue('lookbook/general/max_image_width');
		$this->max_image_height = $this->scopeConfig->getValue('lookbook/general/max_image_height');
		$allowed_extensions = explode(',',$this->scopeConfig->getValue('lookbook/general/allowed_extensions'));
                   
        $this->allowedExtensions = array_map("strtolower", $allowed_extensions);
        if ($sizeLimit>0) $this->sizeLimit = $sizeLimit;      

        if (isset($_GET['qqfile'])) {
            $this->filemodel = $this->uploadXhr;
        } elseif (isset($_FILES['qqfile'])) {
            $this->filemodel = $this->uploadfileForm;
        } else {
            $this->filemodel = false; 
        }
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$upload_dir = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('lookbook/');
		
		$config_check = $this->checkServerSettings();
		
		if($config_check === true){
		   $result = $this->handleUpload($upload_dir); 
		} 
		else
		{
			$result = $config_check;
		}

		// to pass data through iframe you will need to encode all html tags
		$this->getResponse()->setBody(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
    }
	
	function parse_size($size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
		// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}
		else {
			return round($size);
		}
	}
	
	public function checkServerSettings(){    
        $postSize = $this->parse_size($this->toBytes(ini_get('post_max_size')));
        $uploadSize = $this->parse_size($this->toBytes(ini_get('upload_max_filesize')));        

        if ($postSize > $this->sizeLimit || $uploadSize > $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            return array('error' => 'increase post_max_size and upload_max_filesize to $size');    
        }

        if ($this->max_image_width < $this->min_image_width || $this->max_image_height < $this->min_image_height){            
            return array('error' => 'File was not uploaded. Minimal image width (height) can\'t be greater then maximal. Please, check settings.');    
        }
        return true; 
               
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
		$valNumber = substr($val,0, -1); 
        switch($last) {
            case 'g': $val = $valNumber*1024;
            case 'm': $val = $valNumber*1024;
            case 'k': $val = $valNumber*1024;   
        }
        return $val;
    }
        
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
 function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->filemodel){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->filemodel->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->filemodel->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $filename = uniqid();
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while ($this->_file->isExists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }

        if ($this->filemodel->save($uploadDirectory . $filename . '.' . $ext)){  
			$imgPathFull = $uploadDirectory . $filename . '.' . $ext;
			$dimensions = $this->helper->getImageDimensions($imgPathFull);
			 if ($this->min_image_width!=0 && $this->min_image_height!=0) {
				if ($dimensions['width'] < $this->min_image_width || $dimensions['height'] < $this->min_image_height)
				{
				   $this->_file->deleteFile($imgPathFull);
				   return array('error'=> 'Uploaded file dimensions are less than those specified in the configuration.');
				}                                                        
			 }
																		
			if ($this->max_image_width!=0 && $this->max_image_height!=0) {
				if ($dimensions['width'] > $this->max_image_width || $dimensions['height'] > $this->max_image_height)
				{
					$imageResize = $this->_imageFactory->create();         
					$imageResize->open($imgPathFull);
					$imageResize->constrainOnly(TRUE);         
					$imageResize->keepTransparency(TRUE);
					$imageResize->keepAspectRatio(TRUE);         
					$imageResize->resize($this->max_image_width,$this->max_image_height);  
   
					//save image      
					$imageResize->save($imgPathFull);
					$dimensions = $this->helper->getImageDimensions($imgPathFull);
				}
			}
            return array('success'=>true, 'filename'=>$filename . '.' . $ext, 'dimensions' => $dimensions);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }   
}
