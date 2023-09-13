<?php

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

use MGS\StoreLocator\Model\StoreFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action {

    protected $_storeFactory;
    protected $_coreRegistry;
    protected $_storeManager;
    protected $_filesystem;
    protected $_fileUploaderFactory;

    public function __construct(Context $context, 
            StoreFactory $storeFactory, 
            \Magento\Framework\Registry $coreRegistry, 
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory) {
        parent::__construct($context);
        $this->_storeFactory = $storeFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('locator/*/');
            return;
        }
        $model = $this->_objectManager->create('MGS\StoreLocator\Model\Store');

        $entities = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "?", "%", "#", "[", "]");
        $replace = array('', '', '', '','','','','','','','','', '', '', '', '','', '');
        if($data['route'] == ''){
            $route = str_replace($entities, $replace, trim($data['name']));
            $route = str_replace('', '-', strtolower($route));
        }else{
            $route = $data['route'];
        }

        $data['route'] = $route;

        if (!empty($data['id'])) {
            $model->load($data['id']);
        }
        if (!empty($data['id']) && $model->isObjectNew()) {
            $this->messageManager->addError(__('This Locator no longer exists.'));
            $this->_redirect('locator/*/');
            return;
        }

        // Store logo Image upload
        if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $uploader->setFilesDispersion(true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if($uploader->checkAllowedExtension($ext)) {
                $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath('mgs_storelocator/');
                $uploader->save($path);

                $fileName = $uploader->getUploadedFileName();
                if ($fileName) {
                    $data['image'] = 'mgs_storelocator'.$fileName;
                }
            } else {
                $this->messageManager->addError(__('Disallowed file type.'));
                return $this->redirectToEdit($model, $data);
            }
        } else {
            if(isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                $data['image'] = '';
            } else {
                unset($data['image']);
            }
        }
        $model->setData($data);
        try {
            $model->setStoreIds($this->getRequest()->getParam('stores', []));
            $model->save();
            $this->messageManager->addSuccess(__('You saved the store locator.'));
            $this->_getSession()->setLocator(false);
            $back = $this->getRequest()->getParam('back', false);
            if($back == 'edit') {
                return $this->_redirect('locator/*/edit', ['id' => $model->getId(), '_current' => true, 'active_tab' => '']);
            }
            $this->_redirect('locator/*/');
        } catch (\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * @param MGS\StoreLocator\Model\StoreFactory $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit($model, array $data) {
        $this->_getSession()->setLocator($data);
        $arguments = $model->getId() ? ['id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('locator/*/edit', $arguments);
    }

}
