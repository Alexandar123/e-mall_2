<?php

namespace MGS\Blog\Controller\Adminhtml\Post ;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Backend\App\Action {

    protected $imageloader;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MGS\Blog\Model\ImageUploader $imageloader
    )
    {
        parent::__construct($context);
        $this->imageloader = $imageloader;

    }
    public function execute()
    {
        $imageId = $this->_request->getParam('param_name','image');
        try {
            $result = $this->imageloader->saveFileToTmpDir($imageId);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
