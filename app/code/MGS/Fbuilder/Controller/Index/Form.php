<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Fbuilder\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\HTTP\PhpEnvironment\Request;

class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captchaHelper;
    
    protected $_url;
    
    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var Request
     */
    protected $request;
    
    protected $_fileUploaderFactory;
    protected $_filesystem;
    
    protected $_transportBuilder;
    
    protected $_filterProvider;
    
    protected $builderHelper;
    
    protected $attachment;
    
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Captcha\Helper\Data $captchaHelper,
        \Magento\Captcha\Observer\CaptchaStringResolver $captchaStringResolver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Url $url,
        Request $request,
        \MGS\Fbuilder\Model\TransportBuilder $transportBuilder,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \MGS\Fbuilder\Helper\Generate $builderHelper
    ) {

        parent::__construct($context);
        $this->_captchaHelper = $captchaHelper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->request = $request;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $filesystem;
        $this->_url = $url;
        $this->_transportBuilder = $transportBuilder;
        $this->builderHelper = $builderHelper;
        $this->_filterProvider = $filterProvider;
    }
    
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $uenc = json_decode(base64_decode(strtr($data['uenc'], '-_,', '+/=')), true);

        $formId = $uenc['block_id'];
        $captcha = $this->_captchaHelper->getCaptcha($formId);
        if ($uenc['use_mgs_captcha'] && isset($data['captcha'])) {
            if (!$captcha->isCorrect($this->captchaStringResolver->resolve($this->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA.'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($data['referurl']);
                return $resultRedirect;
            }
            
            unset($data['captcha']);
        }
        
        $fields = $uenc['fields'];
        
        unset($data['uenc']);
        
        try {
            $emailSender = $this->builderHelper->getStoreConfig('contact/email/sender_email_identity');
            $this->_transportBuilder
                ->setFrom($emailSender)
                ->addTo($uenc['mgs_receive_email'], '');
                
            $html = $this->_filterProvider->getBlockFilter()->filter($uenc['top_content']);
            $middleContent = '<table><tr><td>';
            if (count($fields)>0) {
                foreach ($fields as $_field) {
                    $middleContent .= $this->getHtmlContent($_field, $data);
                }
            }
            $html .= $middleContent.'</td></tr></table>';
            $html .= $this->_filterProvider->getBlockFilter()->filter($uenc['bottom_content']);
            
            /* $bodyMessage = new \Zend\Mime\Part($html);
            $bodyMessage->type = 'text/html';

            $bodyPart = new \Zend\Mime\Message();

            if(isset($this->attachment) && count($this->attachment)>0){
                foreach($this->attachment as $_attachment){
                    $bodyPart->setParts(array($bodyMessage,$_attachment));
                }
            }else{
                $bodyPart->setParts(array($bodyMessage));
            } */
            
            $this->_transportBuilder->setSubject($uenc['mgs_email_subject']);
            $this->_transportBuilder->setBodyHtml($html);
            
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            
            $this->messageManager->addSuccess($uenc['success_message']);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($data['referurl']);
        return $resultRedirect;
    }
    
    public function getHtmlContent($_field, $data)
    {
        $html = '';
        $identifier = $_field['identifier'];
        switch ($_field['type']) {
        case 'text':
        case 'date':
        case 'select':
        case 'radios':
            if (isset($data[$identifier]) && $data[$identifier]!='') {
                $html = '<p><b>'.$_field['label'].'</b>: '.$data[$identifier].'</p>';
            }
            break;
        case 'textarea':
            if (isset($data[$identifier]) && $data[$identifier]!='') {
                $html = '<p><b>'.$_field['label'].'</b>:</p>';
                $html .= nl2br($data[$identifier]);
            }
            break;
        case 'file':
            if (isset($files[$identifier]) && $files[$identifier]['name'] != '') {
                if ($_field['extension']!='') {
                    $allowExtensions = explode(',', str_replace(' ', '', $_field['extension']));
                    $uploadFileNameArr = explode('.', $files[$identifier]['name']);
                    if (!in_array(end($uploadFileNameArr), $allowExtensions)) {
                        $this->messageManager->addError(__('Not allow extension .%1 for %2. Allowed extensions: %3', end($uploadFileNameArr), $_field['label'], $_field['extension']));
                        break;
                    }
                }
                try {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $identifier]);
                    if ($_field['extension']!='') {
                        $uploader->setAllowedExtensions($allowExtensions);
                    }
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                        
                    $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/form/');
                    $uploader->save($path);
                        
                    $uploadedFilename = $uploader->getUploadedFileName();
                        
                    $url = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mgs/fbuilder/form/'.$uploadedFilename;
                        
                    $uploadedFilenameArr = explode('.', $uploadedFilename);
                        
                    $filePatch = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/form/'). $uploadedFilename;
                        
                    $fileNameArr = explode('/', $uploadedFilename);
                    $fileNameAttach = end($fileNameArr);
                        
                    //$this->_transportBuilder->attachFile($filePatch, $fileNameAttach);
                    $this->attachment[] = $this->_transportBuilder->attachFile($filePatch, $fileNameAttach);
                        
                        
                } catch (\Exception $e) {
                        
                }
            }
                
            break;
        default:
            if (isset($data[$identifier]) && $data[$identifier]!='') {
                $html = '<p><b>'.$_field['label'].'</b>: '.implode(', ', $data[$identifier]).'</p>';
                if (isset($_field['validate']) && $_field['validate'][0]=='validate-email' && (!filter_var($data[$identifier], FILTER_VALIDATE_EMAIL) === false)) {
                    $this->_transportBuilder->setReplyTo($data[$identifier]);
                }
            }
            break;
        }
        return $html;
    }
}
