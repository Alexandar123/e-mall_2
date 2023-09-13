<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Fbuilder\Model;
 
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\AddressConverter;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    
    protected $subject;
    protected $content;
    
    public function attachFile($file, $name)
    {
        if (!empty($file) && file_exists($file)) {
            $attachment = new \Zend\Mime\Part(file_get_contents($file));
            $attachment->type = \Zend_Mime::TYPE_OCTETSTREAM;
            $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
            $attachment->filename = $name;
            return $attachment;
        }

        return false;
    }
    
    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }
    
    public function setSubject($mailSubject)
    {
        $this->subject = $mailSubject;
        return $this;
    }
    
    public function setBodyHtml($mailContent)
    {
        $this->content = $mailContent;
        return $this;
    }
    
    /* protected function prepareMessage()
    {
        //echo $this->content; die();
        $this->message->setBodyHtml('test');
        $this->message->setSubject(html_entity_decode($this->subject, ENT_QUOTES));
        return $this;
    } */
    
    protected function prepareMessage()
    {
        $content = $this->content;
        $part['type'] = MimeInterface::TYPE_HTML;
        
        $mimeMessageInterfaceFactory = $this->objectManager->get(MimeMessageInterfaceFactory::class);
        
        $mimePartInterfaceFactory = $this->objectManager->get(MimePartInterfaceFactory::class);
            
        $emailMessageInterfaceFactory = $this->objectManager->get(EmailMessageInterfaceFactory::class);
        
        $mimePart = $mimePartInterfaceFactory->create(['content' => $content]);
        $this->messageData['body'] = $mimeMessageInterfaceFactory->create(
            ['parts' => [$mimePart]]
        );

        $this->messageData['subject'] = html_entity_decode(
            (string)$this->subject,
            ENT_QUOTES
        );

        $this->message = $emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }
    
    public function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        $addressConverter =  $this->objectManager->get(AddressConverter::class);
        if (is_string($email)) {
            $this->messageData[$addressType][] = $addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        } else {
            $this->messageData[$addressType] = $convertedAddressArray;
        }
    }
}
