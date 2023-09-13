<?php
namespace Swissup\Ajaxlayerednavigation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\EncoderInterface as Encoder;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\ResponseInterface as Response;
use Magento\Framework\View\LayoutInterface as Layout;

class GetAjaxCatalogSearchResult implements ObserverInterface
{
    protected $encoder;
    public $response;
    public $request;

    public function __construct(
        Encoder $encoder,
        Request $request,
        Layout $layout,
        Response $response
    ) {
        $this->encoder = $encoder;
        $this->response = $response;
        $this->request = $request;
        $this->layout = $layout;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isAjax = $this->request->isXmlHttpRequest();
        if ($isAjax && $this->request->getParam('is_ajax')) { 
            $output = [
                'list'    => $this->layout->getBlock('search.result')->toHtml(),
                'filters' => $this->layout->getBlock('catalogsearch.leftnav')->toHtml(),
                'state'   => $this->layout->getBlock('catalogsearch.navigation.state')->toHtml()
            ];

            $this->response->clearBody();
            return $this->response->setBody(
                $this->encoder->encode($output)
            );
        }

        return $this;
    }
}