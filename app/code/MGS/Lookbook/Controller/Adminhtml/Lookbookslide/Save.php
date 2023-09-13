<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbookslide;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \MGS\Lookbook\Controller\Adminhtml\Lookbookslide
{
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('MGS\Lookbook\Model\Slide')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
			
			if (isset($_FILES['next_image']['name']) && $_FILES['next_image']['name'] != '') {
				$uploader = $this->_objectManager->create(
					'Magento\MediaStorage\Model\File\Uploader',
					['fileId' => 'next_image']
				);
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
				$uploader->setAllowRenameFiles(true);
				$uploader->setAllowCreateFolders(true);
				$uploader->setFilesDispersion(true);
				$ext = pathinfo($_FILES['next_image']['name'], PATHINFO_EXTENSION);
				if ($uploader->checkAllowedExtension($ext)) {
					$path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
						->getAbsolutePath('lookbook/icons/');
					$uploader->save($path);
					$fileName = $uploader->getUploadedFileName();
					if ($fileName) {
						$data['next_image'] = 'lookbook/icons'.$fileName;
					}
				} else {
					$this->messageManager->addError(__('Disallowed file type.'));
					return $this->redirectToEdit($model, $data);
				}
			} else {
				if (isset($data['next_image']['delete']) && $data['next_image']['delete'] == 1) {
					$data['next_image'] = '';
				} else {
					unset($data['next_image']);
				}
			}
			
			if (isset($_FILES['prev_image']['name']) && $_FILES['prev_image']['name'] != '') {
				$uploader = $this->_objectManager->create(
					'Magento\MediaStorage\Model\File\Uploader',
					['fileId' => 'prev_image']
				);
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
				$uploader->setAllowRenameFiles(true);
				$uploader->setAllowCreateFolders(true);
				$uploader->setFilesDispersion(true);
				$ext = pathinfo($_FILES['prev_image']['name'], PATHINFO_EXTENSION);
				if ($uploader->checkAllowedExtension($ext)) {
					$path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
						->getAbsolutePath('lookbook/icons/');
					$uploader->save($path);
					$fileName = $uploader->getUploadedFileName();
					if ($fileName) {
						$data['prev_image'] = 'lookbook/icons'.$fileName;
					}
				} else {
					$this->messageManager->addError(__('Disallowed file type.'));
					return $this->redirectToEdit($model, $data);
				}
			} else {
				if (isset($data['prev_image']['delete']) && $data['prev_image']['delete'] == 1) {
					$data['prev_image'] = '';
				} else {
					unset($data['prev_image']);
				}
			}
			
			$data['status'] = $data['slider_status'];
			
			

            $model->setData($data);
			
			//echo '<pre>'; print_r($data); die();
			
			if (isset($data['lookbook_ids'])) {
				$jsHelper = $this->_objectManager->create('Magento\Backend\Helper\Js');
				$decode = $jsHelper->decodeGridSerializedInput($data['lookbook_ids']);
				$model->setLookbooks($decode);
			}

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the slider.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
