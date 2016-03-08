<?php
namespace Rivio\Rivio\Block;
class Rivio extends \Magento\Framework\View\Element\Template
{
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\UrlInterface $urlinterface,
    \Magento\Catalog\Helper\Image $imageHelper,
    \Rivio\Rivio\Block\Config $config,
    \Psr\Log\LoggerInterface $logger,
    array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_urlinterface = $urlinterface;
        $this->_config = $config;
        $this->_imageHelper = $imageHelper;
        $this->_logger = $logger;
        parent::__construct($context, $data);
    }

    public function getProduct(){
		if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('current_product'));
        }
        return $this->getData('product');
    }

    public function getProductId(){
    	return $this->getProduct()->getId();
    }

    public function getProductName(){
        $productName = $this->getProduct()->getName();
        return htmlspecialchars($productName);
    }

    public function getLanguageCode(){
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Locale\Resolver $resolver */
        $resolver = $om->get('Magento\Framework\Locale\Resolver');

        $localeCode = $resolver->getLocale();
        $languageCode = substr($localeCode, 0, 2);

        return $languageCode;
    }

    public function getProductPrice(){
        return $this->getProduct()->getFinalPrice();
    }

    public function getProductCategory(){
        // Get product categories
        $cats = $this->getProduct()->getCategoryIds();

        if (isset($cats[0])){
            /** @var \Magento\Framework\ObjectManagerInterface $om */
            $om = \Magento\Framework\App\ObjectManager::getInstance();

            // Get the category name
            $category = $om->get('\Magento\Catalog\Model\CategoryFactory')->create()->load($cats[0])->getName();
        } else{
            $category = "Default category";
        }

        return $category;
    }

    public function getProductDescription(){
        $description = $this->getProduct()->getShortDescription();

        if (strlen($description) == 0){
            $description = $this->getProduct()->getDescription();
        }

        return $description;
    }

    public function getProductUrl(){
        return $this->_urlinterface->getCurrentUrl();
    }    

    public function isRenderWidget(){
        return $this->getProduct() != null && 
        ($this->_config->isWidgetEnabled() || $this->getData('fromHelper'));
    }    

    public function isRenderStars(){
        return $this->_config->isStarsEnabled();
    } 

    public function getProductImageUrl(){
        return $this->_imageHelper->init($this->getProduct(), 'product_page_image_large')->getUrl();
    } 
    
    private function isProductPage(){
        return $this->getProduct() != null;
    }     
}