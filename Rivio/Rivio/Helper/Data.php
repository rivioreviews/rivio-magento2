<?php
namespace Rivio\Rivio\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function showWidget($thisObj, $product = null, $print=true)
    {
        return $this->renderRivioProductBlock($thisObj, 'widget_div', $product, $print);
    }  
    

    public function showStars($thisObj, $product = null, $print=true)
    {
        return $this->renderRivioProductBlock($thisObj, 'stars', $product, $print);
    }  

    private function renderRivioProductBlock($thisObj, $blockName, $product = null, $print=true)
    {

        $block = $thisObj->getLayout()->getBlock($blockName);

        if ($block == null) {
            $this->_logger->addDebug('can\'t find rivio block');
            return;
        }
        $block->setAttribute('fromHelper', true);

        if ($product != null)
        {
            $block->setAttribute('product', $product);
        }

        if ($print == true) {
            echo $block->toHtml();
            $block->setAttribute('fromHelper', false);
        } else {
            $ret = $block->toHtml();
            $block->setAttribute('fromHelper', false);
            return $ret;
        }        
    }      
}