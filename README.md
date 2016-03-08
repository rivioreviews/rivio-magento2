RIVIO Reviews for Magento 2
==========================

##Installation:

1.  Install Magento Composer Installer if not installed yet
    use: composer require magento/magento-composer-installer

2.  Edit your Magento 2 composer.json file, and add these lines (Should be located in the Magento 2 root folder): 
        
        "repositories": [
              {
                "type": "vcs",
                "url": "https://github.com/rivioreviews/rivio-magento2"
              }
        ]
        
    
3.  Install the Rivio module with the command: composer require Rivio/Rivio

4.  Open the file: app/etc/config.php and insert the line:  ```'Rivio_Rivio' => 1,```
    Modify the line ```'Magento_Review' => 1```, and set to ```'Magento_Review' => 0,``` to remove the default Magento review engine
     
5.  Run the command: bin/magento setup:upgrade if any errors would occur

##Setup:

1.  Log in to your Magento admin panel

2.  Navigate using the side menu to:  STORES => Configuration

3.  Change Store View at the top to the store, you want to use RIVIO with

4.  Click on RIVIO => RIVIO Reviews

5.  Set your credentials as required and don't forget to set Enable Default Widget Position to Yes and Show Rivio Stars to Yes if you want to show it.

6.  Click on Save Config and you're all ready to set!