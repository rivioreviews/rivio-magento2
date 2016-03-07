<?php

namespace Rivio\Rivio\Block;
use Magento\Store\Model\ScopeInterface;
class Config
{
    const RIVIO_APP_KEY = 'rivio/settings/app_key';
    const RIVIO_SECRET = 'rivio/settings/secret';
    const RIVIO_WIDGET_ENABLED = 'rivio/settings/widget_enabled';
    const RIVIO_STARS_ENABLED = 'rivio/settings/stars_enabled';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {        
        $this->_scopeConfig = $scopeConfig;
    }

    public function getAppKey(){
        return $this->_scopeConfig->getValue(self::RIVIO_APP_KEY, ScopeInterface::SCOPE_STORE);
    }

    public function getSecret(){
        return $this->_scopeConfig->getValue(self::RIVIO_SECRET, ScopeInterface::SCOPE_STORE);
    }

    public function getCustomOrderStatus(){
        null;
    }

    public function isWidgetEnabled(){
        return (bool)$this->_scopeConfig->getValue(self::RIVIO_WIDGET_ENABLED, ScopeInterface::SCOPE_STORE);
    } 

    public function isStarsEnabled(){
        return (bool)$this->_scopeConfig->getValue(self::RIVIO_STARS_ENABLED, ScopeInterface::SCOPE_STORE);
    } 

    public function isAppKeyAndSecretSet(){
        return ($this->getAppKey() != null && $this->getSecret() != null);
    } 

    public function getTimeFrame(){
        $today = time();
        $last = $today - (7776000); //90 days ago
        return date("Y-m-d", $last);
    }              
}
