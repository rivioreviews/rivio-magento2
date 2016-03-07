<?php

namespace Rivio\Rivio\Controller\Adminhtml\RivioController;

class RivioController extends \Magento\Backend\App\Action
{

//max amount of orders to export
    const MAX_ORDERS_TO_EXPORT = 5000;
    const MAX_BULK_SIZE        = 200;

    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Rivio\Rivio\Block\Config $config,
        \Rivio\Rivio\Helper\ApiClient $api,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_api = $api;
        $this->_logger = $logger;
        $this->_messageManager = $messageManager;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }


    public function execute()
    {

        try {
            $PostDataArr = $this->_request->getPost()->toArray();
            $storeId = $PostDataArr["store_id"];
            $appKey = $this->_config->getAppKey();
            $secret = $this->_config->getSecret();
            if(($secret == null) || ($appKey == null))
            {
                $this->_messageManager->addError(__('Please make sure you insert your API KEY and SECRET KEY and save configuration before trying to export past orders'));
                return;
            }
            $offset = 0;
            $orderStatuses = $this->_config->getCustomOrderStatus();
            if ($orderStatuses == null) {
                $orderStatuses = array(\Magento\Sales\Model\Order::STATE_COMPLETE);
            } else {
                $orderStatuses = array_map('strtolower', explode(' ', $orderStatuses));
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderModel = $objectManager->get('Magento\Sales\Model\Order');
            $salesCollection = $orderModel->getCollection()
                ->addFieldToFilter('status', $orderStatuses)
                ->addFieldToFilter('store_id', $storeId)
                ->addAttributeToFilter('created_at', array('gteq' => $this->_config->getTimeFrame()))
                ->addAttributeToSort('created_at', 'DESC')
                ->setPageSize(self::MAX_BULK_SIZE);
            $pages = $salesCollection->getLastPageNumber();
            $success = true;
            do {
                try {

                    $offset++;
                    $salesCollection->setCurPage($offset)->load();
                    $orders = array();

                    foreach($salesCollection as $order)
                    {
                        $order_data = $this->_api->prepareOrderData($order);
                        if (!$order->getCustomerIsGuest()) {
                            $order_data["user_reference"] = $order->getCustomerId();
                        }
                        $orders[] = $order_data;
                    }
                    if (count($orders) > 0)
                    {
                        for ($i=0; $i < count($orders); $i++){

                            // Order data
                            $rivioOrders[$i]['order_id'] = $orders[$i]['order_id'];
                            $rivioOrders[$i]['ordered_date'] = $orders[$i]['order_date'];

                            $key = current(array_keys($orders[0]['products']));

                            // Product data
                            $rivioOrders[$i]['product_id'] = $key;
                            //$rivioOrders[$i]['product_category'] = $orders[$i]['products'][$key]['category'];
                            $rivioOrders[$i]['product_name'] = $orders[$i]['products'][$key]['name'];
                            $rivioOrders[$i]['product_description'] = $orders[$i]['products'][$key]['description'];
                            $rivioOrders[$i]['product_url'] = $orders[$i]['products'][$key]['url'];
                            $rivioOrders[$i]['product_image_url'] = $orders[$i]['products'][$key]['image'];
                            //$rivioOrders[$i]['product_barcode'] = "";
                            $rivioOrders[$i]['product_price'] = $orders[$i]['products'][$key]['price'];

                            if($orders[$i]['customer_name'] != 'Guest'){
                                $rivioOrders[$i]['customer_first_name'] = $orders[$i]['customer_name'];
                            } else{
                                $parts = explode("@", $orders[$i]['email']);
                                $rivioOrders[$i]['customer_first_name'] = ucfirst($parts[0]);
                            }

                            $rivioOrders[$i]['customer_email'] = $orders[$i]['email'];

                            $resData = $this->_api->massCreatePurchases($rivioOrders);

                            $success = ($resData['code'] != 200) ? false : $success;

                            print_r($success);


                        }
                    }
                } catch (\Exception $e) {
                    $this->_logger->addDebug('Failed to export past orders. Error: '.$e);
                }
                $salesCollection->clear();
            } while ($offset <= (self::MAX_ORDERS_TO_EXPORT / self::MAX_BULK_SIZE) && $offset < $pages);
        } catch(\Exception $e) {
            $this->_logger->addDebug('Failed to export past orders. Error: '.$e);
        }
        if($success)
        {
            $this->_messageManager->addSuccess(__("Past orders were exported successfully. Emails will be sent to your customers within 24 hours, and you will start to receive reviews."));
            $this->_logger->addDebug("Past orders were exported successfully.");
        }
        else
        {
            $this->_messageManager->addError(__("An error occured, please try again later."));
            $this->_logger->addDebug("Failed to export past orders.");
        }
        $this->_response->setBody(1);
        return;
    }

}