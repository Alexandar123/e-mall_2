<?php

namespace MGS\Ajaxlayernavigation\Plugin;


class AjaxSearch
{
    protected $_jsonEncoder;

    public function __construct(
        \Magento\Framework\Json\EncoderInterface $_jsonEncoder
    ) {

        $this->_jsonEncoder = $_jsonEncoder;
    }

    public function afterExecute($subject, $page)
    {
        $request = $subject->getRequest();
        $isAjax = $request->isXmlHttpRequest();
        if ($isAjax && $request->getParam('is_ajax')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $xmlLayout = $objectManager->get(\Magento\Framework\App\View::class);
            $output = [
                'list'    => $xmlLayout->getLayout()->getBlock('search.result')->toHtml(),
                'filters' => $xmlLayout->getLayout()->getBlock('catalogsearch.leftnav')->toHtml(),
                'state'   => $xmlLayout->getLayout()->getBlock('catalogsearch.navigation.state')->toHtml()
            ];

            return $subject->getResponse()->setBody(
                $this->_jsonEncoder->encode($output)
            );
        }
        return $page;
    }
}
