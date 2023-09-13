<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\Request;
class Import extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
{
	protected $_sectionCollection;
	protected $_blockCollection;
	
	/**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Request
     */
    protected $request;
	

	protected $_filesystem;
	protected $_fileUploaderFactory;
	protected $_file;
	
	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;
	
	protected $_xmlArray;
	protected $_generateHelper;

	
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory $sectionCollectionFactory,
		\MGS\Fbuilder\Model\ResourceModel\Child\CollectionFactory $blockCollectionFactory,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Framework\Xml\Parser $parser,
        Request $request,
		\Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\MGS\Fbuilder\Helper\Generate $generateHelper,
		\Magento\Framework\Filesystem\Driver\File $file
	)
    {
        parent::__construct($context);
		$this->_sectionCollection = $sectionCollectionFactory;
		$this->_blockCollection = $blockCollectionFactory;
		$this->_pageFactory = $pageFactory;
        $this->request = $request;
		$this->_filesystem = $filesystem;
		$this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_parser = $parser;
		$this->_generateHelper = $generateHelper;
    }

    /**
     * Edit sitemap
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
		if($this->getRequest()->isAjax()){
			if($pageId = $this->getRequest()->getParam('page_id')){
				$result = ['result'=>'error', 'data'=>__('Can not upload file.')];
			
				if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
					$uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
					$file = $uploader->validateFile();
					
					if(($file['name']!='') && ($file['size'] >0)){
						$uploader->setAllowedExtensions(['xml']);
						$uploader->setAllowRenameFiles(true);
						$path = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('fbuilder_import');
						$uploader->save($path);
						$fileName = $uploader->getUploadedFileName();
						
						if($this->isFile('fbuilder_import/'.$fileName)){
							$dir = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('fbuilder_import/');
							$importFile = $dir.$fileName;
							
							if (is_readable($importFile)){
								try {
									$this->_xmlArray = $this->_parser->load($importFile)->xmlToArray();
									
									// Remove old sections
									$sections = $this->_sectionCollection->create()
										->addFieldToFilter('page_id', $pageId);

									if (count($sections) > 0){
										foreach ($sections as $_section){
											$_section->delete();
										}
									}
									
									// Remove old blocks
									$childs = $this->_blockCollection->create()
										->addFieldToFilter('page_id', $pageId);

									if (count($childs) > 0){
										foreach ($childs as $_child){
											$_child->delete();
										}
									}
									
									$html = '';
									
									// Import new sections
									$sectionArray = $this->_xmlArray['page']['section'];
									if(isset($sectionArray)){
										if(isset($sectionArray[0]['name'])){
											foreach($sectionArray as $section){
												$section['store_id'] = 0;
												$section['page_id'] = $pageId;
												$this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($section)->save();
											}
										}else{
											$sectionArray['store_id'] = 0;
											$sectionArray['page_id'] = $pageId;
											$this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($sectionArray)->save();
										}
									}
									
									// Import new blocks
									$blockArray = $this->_xmlArray['page']['block'];
									if(isset($blockArray)){
										if(isset($blockArray[0]['block_name'])){
											foreach($blockArray as $block){
												$block['store_id'] = 0;
												$block['page_id'] = $pageId;
												$oldId = $block['child_id'];
												unset($block['child_id']);
												$child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($block)->save();
												$customStyle = $child->getCustomStyle();
												$customStyle = str_replace('.block'.$oldId,'.block'.$child->getId(),$customStyle);
												$child->setCustomStyle($customStyle)->save();
											}
										}else{
											$blockArray['store_id'] = 0;
											$blockArray['page_id'] = $pageId;
											$oldId = $blockArray['child_id'];
											unset($blockArray['child_id']);
											$child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($blockArray)->save();
											$customStyle = $child->getCustomStyle();
											$customStyle = str_replace('.block'.$oldId,'.block'.$child->getId(),$customStyle);
											$child->setCustomStyle($customStyle)->save();
										}
									}
									
									$this->_generateHelper->importContent($pageId);
									
									$this->generateBlockCss();
									
									$this->_eventManager->dispatch('mgs_fbuilder_import_before_end', ['content' => $this->_xmlArray]);
									
									$result['result'] = 'success';
								}catch (\Exception $e) {
									$result['result'] = $e->getMessage();
								}
							}else{
								$result['result'] = __('Cannot import page');
							}
							$result['data'] = $fileName;
						}
					}
				}
			}else{
				$result['result'] = __('Have no page to import');
			}

			return $this->getResponse()->setBody(json_encode($result));
		}

		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
		
		
    }
	
	public function generateBlockCss(){
		$model = $this->_objectManager->create('MGS\Fbuilder\Model\Child');
		$collection = $model->getCollection();
		$customStyle = '';
		foreach($collection as $child){
			if($child->getCustomStyle() != ''){
				$customStyle .= $child->getCustomStyle();
			}
		}
		if($customStyle!=''){
			try{
				$this->_generateHelper->generateFile($customStyle, 'blocks.css', $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
			}catch (\Exception $e) {
				
			}
		}
	}
	
	public function isFile($filename)
    {
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        return $mediaDirectory->isFile($filename);
    }
}
