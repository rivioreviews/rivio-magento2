<?php

namespace Rivio\Rivio\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Core\Model\ObjectManager;

class PurchaseObserver implements ObserverInterface
{   

	public function __construct(
        \Rivio\Rivio\Helper\ApiClient $helper,
        \Rivio\Rivio\Block\Config $config,
        \Psr\Log\LoggerInterface $logger)
                        
	{
        $this->_helper = $helper;
        $this->_config = $config;
        $this->_logger = $logger;       
	}

    //observer function hooked on event sales_order_save_after
    public function execute(Observer $observer) 
    {
        try {
            if (!$this->_config->isAppKeyAndSecretSet())
            {
                return $this;
            }
            $order = $observer->getEvent()->getOrder();
            if($order->getStatus() != \Magento\Sales\Model\Order::STATE_COMPLETE)
            {
                return $this;
            }
            $data = $this->_helper->prepareOrderData($order);

            $this->_helper->createPurchases($data); 
            return $this;   
        } catch(\Exception $e) {
            $this->_logger->addDebug('Failed to send mail after purchase. Error: '.$e); 
            return $this;
        }

    }
}
