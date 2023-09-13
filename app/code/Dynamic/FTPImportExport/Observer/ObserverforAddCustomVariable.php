<?php
namespace Dynamic\FTPImportExport\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class ObserverforAddCustomVariable implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getTransport();
        $order = $transport->getOrder();
        $transport['erp_reference_number'] = $order->getErpReferenceNumber();
        $transport['erp_tracking_link'] = $order->getErpTrackingLink();
        $transport['erp_order_status'] = $order->getErpOrderStatus();
    }
}