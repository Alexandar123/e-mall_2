<?php
namespace MGS\Ajaxlayernavigation\Plugin;
  

class AjaxView
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
            $layout = $page->getLayout(); 
            $output = [
                'list'    => $layout->getBlock('category.products')->toHtml(),
                'filters' => $layout->getBlock('catalog.leftnav')->toHtml(),
                'state'   => $layout->getBlock('catalog.navigation.state')->toHtml()
            ];
            return $subject->getResponse()->setBody(
                $this->_jsonEncoder->encode($output)
            );
        }
        return $page;
    }
}
