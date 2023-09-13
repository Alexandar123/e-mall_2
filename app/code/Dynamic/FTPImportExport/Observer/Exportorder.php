<?php

namespace Dynamic\FTPImportExport\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Directory\Model\CountryFactory;

class Exportorder implements ObserverInterface
{
	protected $ftp;
	protected $helperData;
	protected $order;
	protected $countryFactory;

	public function __construct(
		\Magento\Framework\Filesystem\Io\Ftp $ftp,
		\Dynamic\FTPImportExport\Helper\Data $helperData,
		\Magento\Sales\Model\Order $order,
		CountryFactory $countryFactory
	) {
		$this->ftp = $ftp;
		$this->helperData = $helperData;
		$this->order = $order;
		$this->countryFactory = $countryFactory;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Exportorder.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
		$path = $this->helperData->getOrderExportData('product_directoryEO');
		$host = $this->helperData->getOrderExportData('display_hostEO');
		$user = $this->helperData->getOrderExportData('display_usernameEO');
		$pwd = $this->helperData->getOrderExportData('display_passwordEO');
		//$OrderId = $this->helperData->getOrderExportData('product_ordernumberEO');
		$port = $this->helperData->getOrderExportData('display_portDiP');

		try {
			$OrderId = $observer->getEvent()->getOrderIds()[0];
			$logger->info($OrderId);
			if ($OrderId > 0) {
				$open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));
				// echo $open;
				// exit;
				$logger->info($open);
				if ($open) {
					$order = $this->order->load($OrderId);
					if (!$order->getId()) {
						echo "system could not find order with order id : " . $OrderId;
						return;
					}

					$orderIncrementId = $order->getIncrementId();
					$customer_email = $order->getCustomerEmail();

					/* get Billing details */
					$billingaddress = $order->getBillingAddress();
					$billingname = $billingaddress->getName();
					$billingcity = $billingaddress->getCity();
					$billingstreet = $billingaddress->getStreet();
					$billingpostcode = $billingaddress->getPostcode();
					$billingtelephone = $billingaddress->getTelephone();
					$billingstate = $billingaddress->getRegion();
					$bill_countryName = '';
					$country = $this->countryFactory->create()->loadByCode($billingaddress->getCountryId());
					if ($country) {
						$bill_countryName = $country->getName();
					}

					/* get shipping details */

					$shippingaddress = $order->getShippingAddress();
					$shipname = $shippingaddress->getName();
					$shippingcity = $shippingaddress->getCity();
					$shippingstreet = $shippingaddress->getStreet();
					$shippingpostcode = $shippingaddress->getPostcode();
					$shippingtelephone = $shippingaddress->getTelephone();
					$shippingstate = $shippingaddress->getRegion();
					$shipp_countryName = '';
					$country = $this->countryFactory->create()->loadByCode($shippingaddress->getCountryId());
					if ($country) {
						$shipp_countryName = $country->getName();
					}

					/* get payment method name */
					$payment = $order->getPayment();
					$methodCode = $payment->getMethod();

					$orderItems = $order->getAllItems();
					$Items = [];
					foreach ($orderItems as $k => $orderItem) {
						$Items[] = array("acIdent" => $orderItem->getSku(), "anQty" => $orderItem->getQtyOrdered(), "anRTPrice" => $orderItem->getBasePrice(), "anSalePrice" => $orderItem->getBasePriceInclTax(), "anDiscount" => $orderItem->getBaseDiscountAmount());
					}

					/* prepare export array to convert json */
					$orderarray = [
						"OrderId" => $order->getId(),
						"OrderDate" => $order->getCreatedAt(),
						"acPayMethod" => $methodCode ? $methodCode : "",
						"acDelivery" => $order->getShippingMethod() ? $order->getShippingMethod() : "",
						"acDiscountCode" => $order->getCouponCode() ? $order->getCouponCode() : "",
						"acBuyer" => [
							[
								"acSubject" => $billingname . " " . $order->getCustomerId(),
								"acName2" => $billingname,
								"acCode" => "",
								"acRegNo" => "",
								"acPost" => $billingpostcode ? $billingpostcode : "",
								"acAddress" => (isset($billingstreet[0]) ? $billingstreet[0] : '') . " " . (isset($billingstreet[1]) ? $billingstreet[1] : ''),
								"acPhone" => $billingtelephone ? $billingtelephone : "",
								"acEmail" => $customer_email
							]
						],
						"acDeliveryAddress" => [
							[
								"acName" => $shipname,
								"acPost" => $shippingpostcode,
								"acAddress" => (isset($billingstreet[0]) ? $billingstreet[0] : '') . " " . (isset($billingstreet[1]) ? $billingstreet[1] : ''),
								"acPhone" => $shippingtelephone ? $shippingtelephone : $shippingtelephone
							]
						],
						"Items" => $Items
					];
					// echo "<pre>";
					// print_r(json_encode($orderarray));
					// exit;

					

					$st = json_encode($orderarray);
					$logger->info($st);

					$this->ftp->write($path . '/Orders/' . 'Order_data_#' . $order->getIncrementId() . '.txt', $st, $mode = null);
					$this->ftp->close();
					// echo "Please check FTP server and find Order_data_#" . $order->getIncrementId() . ".txt file";
					// return;
				}
			} else {
				// echo "system could not find order";
				return;
			}
		} catch (\Exception $e) {
			$logger->info($e->getMessage());
			return false;
			print_r($e->getMessage());
		}
	}
}
