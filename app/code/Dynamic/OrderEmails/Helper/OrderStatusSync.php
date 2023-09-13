<?php

namespace Dynamic\OrderEmails\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;


class OrderStatusSync
{
    protected $_pageFactory;

    protected $_postFactory;

    protected $_orderCollectionFactory;

    protected $_objectManager;

    protected $_varFactory;

    protected $_invoiceService;

    protected $_transaction;

    protected $transportBuilder;

    protected $storeManager;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $helperData;

    protected $ftp;

    protected $logger;

    protected $orderRepository;
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;
    protected $messageManager;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Variable\Model\VariableFactory $varFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Dynamic\FTPImportExport\Helper\Data $helperData,
        \Magento\Framework\Filesystem\Io\Ftp $ftp,
        \Psr\Log\LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        InvoiceSender $invoiceSender,
        \Magento\Framework\Message\ManagerInterface $messageManager

    ) {
        $this->_objectManager = ObjectManager::getInstance();
        $this->_varFactory = $varFactory;
        $this->_pageFactory = $pageFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->helperData = $helperData;
        $this->ftp = $ftp;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceSender = $invoiceSender;
        $this->messageManager = $messageManager;
    }
    public function getOrderSyncOnFTPimport()
    {
        $Filelines = $this->getFTPFileData();

        // echo "<pre>";
        // print_r($Filelines);
        // exit;
        if ($Filelines) {
            foreach ($Filelines['lines'] as $key => $value) {
                $this->getOrderSync($key, $value, $Filelines['conn'], $Filelines['adminemail']);
            }
        }
    }

    public function getOrderSync($filename, $content, $conn, $adminemail)
    {

        $adminreceiver = 'default@default.com';
        $adminlogdata = 0;
        if (isset($adminemail)) {
            $adminreceiver = $adminemail;
        }
        if (isset($Filelines['log_file_name'])) {
            $log_file_name = $Filelines['log_file_name'];
        } else {
            if (!file_exists('var/ftp-order-sync-log')) {
                mkdir('var/ftp-order-sync-log', 0777, true);
            }
            $log_file_name = explode(".", $filename)[0] . "_" . date('YmdHis') . "_log.txt";
            $log_file_name = "var/ftp-order-sync-log/" . $log_file_name;
        }
        if (!$content) {
            file_put_contents($log_file_name, 'System did not find data to sync' . $filename, FILE_APPEND);
            return;
        }

        file_put_contents($log_file_name, 'Import started at ' . Date("Y-m-d H:i:s") . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);


        // $product_Data = json_decode($content);
        $order_Data = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content), true);

        if (empty($order_Data)) {
            return;
        }
        $log_counter = 0;
        $admin_log_counter = 0;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/FTP_to_magento_order_status_update.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("cron wokring");
        $counter = 0;
        $myDate = date("Y-m-d 00:00:00", strtotime(date("Y-m-d 00:00:00", strtotime(date("Y-m-d 00:00:00"))) . "-10 month"));
        foreach ($order_Data as $k => $ftp_order) {
            if ($ftp_order) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get(22);

                if ($ftp_order['OrderStatus'] == "2") {
                    $this->createInvoie($order, $ftp_order);
                    ++$counter;
                }
            }
        }
        $logger->info(++$counter . ": Order status has been changed...<br><br>");
        $MoveFileToHistory = $this->MoveReadFile($filename, $conn, $log_file_name, $adminreceiver, $adminlogdata);
        // exit;echo ++$counter.": Order status has been changed...<br><br>";
    }

    public function createShipment($order)
    {
        // Check if order can be shipped or has already shipped
        if ($order->canShip()) {

            // Initialize the order shipment object
            $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
            $shipment = $convertOrder->toShipment($order);

            // Loop through order items
            foreach ($order->getAllItems() as $orderItem) {
                // Check if order item has qty to ship or is virtual
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $orderItem->getQtyToShip();

                // Create shipment item with qty
                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
            }

            // Register shipment
            $shipment->register();

            $shipment->getOrder()->setIsInProcess(true);

            try {
                // Save created shipment and order
                $shipment->save();
                $shipment->getOrder()->save();

                // Send email
                // $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                // ->notify($shipment);

                $shipment->save();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }
        }
    }
    public function createInvoie($order, $ftp_order)
    {
        $order = $this->orderRepository->get($order->getId());
        if (!$order->canInvoice()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The order does not allow an invoice to be created.')
            );
        }

        if ($order->canInvoice()) {

            $invoice = $this->_invoiceService->prepareInvoice($order);
            if (!$invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }

            // $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(true);
            $invoice->getOrder()->setIsInProcess(true);
            // $invoice->capture();

            $transactionSave =
                $this->_transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();

            // send invoice emails, If you want to stop mail disable below try/catch code
            try {
                $this->invoiceSender->send($invoice);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t send the invoice email right now.'));
            }
            $invoice->save();
            // $this->invoiceSender->send($invoice);
            $order->setState($order::STATE_PROCESSING)->save();
            $order->setStatus($order::STATE_PROCESSING)->save();
            $order->addCommentToStatusHistory(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )->setIsCustomerNotified(true)->save();
            $this->orderRepository->save($order);
        }
    }
    public function sendConfirmationEmail()
    {
        $orderid = '000000003';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderid);
        $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
    }
    public function changeStatusTo($order, $status)
    {
        echo "OrderId" . $order->getIncrementId() . '->' . $status . "<br>"; //return;//Remove this return after testing...
        $state = $order->getState();
        $ostatus = $order->getStatus();
        if ($ostatus == $status) {
            return;
        }
        $comment = 'Status changed to ' . $status;
        $isNotified = false;
        $order->setState($state);
        $order->setStatus($status);
        $order->addStatusToHistory($order->getStatus(), $comment);
        $order->save();
        /*
		*Send email to customer about status change
		*/
        $this->sendEmail($order, $status);
    }

    private function getFTPFileData()
    {
        $path = '/DbToWeb';
        $host = '45.80.134.144';
        $user = 'KorisnikFTP';
        $pwd = 'Koliko321';
        $filename = "";
        if (0) {
            $filename = $this->helperData->getGeneralStock('stock_txtfileStock');
        }
        $port = 21;
        $reportReceiver = $this->helperData->getGeneralStock('display_reportreceiverStock');

        // echo $port;
        // exit;

        try {
            $open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => false));

            if ($open) {
                $content = [];
                $HistoryFound = 1;
                if ($filename != "") {
                    $path = $this->ftp->cd("/$path/");

                    $list = $this->ftp->ls();
                    foreach ($list as $k1 => $v1) {

                        if ($v1['text'] == $filename) {
                            $content[$v1['text']] = $this->ftp->read($v1['text']);
                        }
                    }
                } else {
                    $path = $this->ftp->cd("/$path/");
                    $list = $this->ftp->ls();

                    $filename = [];
                    foreach ($list as $k1 => $v1) {
                        if ($v1['text'] != "History") {
                            /*Remove currepted data from file*/
                            if (strpos($v1['text'], 'OrderStatus') !== false) {
                                $datadd = trim($this->ftp->read($filename[] = $v1['text']));
                                $datadd = utf8_decode($datadd);
                                $datadd = str_replace('??', '', $datadd);
                                $datadd = stripslashes(html_entity_decode($datadd));

                                // $product_Data = json_decode($content);

                                // print_r($product_Data);

                                $content[$v1['text']] = $datadd;
                                // $content[$v1['text']] = $this->ftp->read($filename[] = $v1['text']);

                            }
                        }
                        if ($v1['text'] == "History") {
                            $HistoryFound = 0;
                        }
                    }
                }
                if ($HistoryFound) {
                    $this->ftp->mkdir("History");
                }

                if (empty($content)) {
                    return array('lines' => array(), 'filename' => '', 'conn' => $this->ftp);
                }

                $this->ftp->close();
                // echo "<pre>";
                // print_r($content);
                // print_r($filename);
                // print_r($reportReceiver);
                // die;
                return array('lines' => $content, 'filename' => $filename, 'conn' => $this->ftp, 'adminemail' => $reportReceiver);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->ftp->close();
            return false;
        } catch (LocalizedException $e) {
            // echo "LocalizedException";
            // exit;
            $this->ftp->close();
            return false;
        }
    }
    private function MoveReadFile($filename, $conn, $log_file_name, $adminreceiver, $adminlogdata)
    {

        $path = '/DbToWeb';
        $host = '45.80.134.144';
        $user = 'KorisnikFTP';
        $pwd = 'Koliko321';
        $port = 21;

        try {
            $open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => false));
            if ($open) {
                $conn->cd("/$path/");
                $content = $conn->mv($filename, "History/" . $filename);
                $logname = '';
                $r = explode("/", $log_file_name);
                $logname = end($r);
                $content = $conn->write("History/" . $logname, $log_file_name);
                //$this->sendEmail($adminreceiver, $log_file_name, $adminlogdata);
                $this->ftp->close();
            }
        } catch (\Exception $e) {
            $this->ftp->close();
            return false;
        }
    }
}
