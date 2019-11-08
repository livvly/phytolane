<?php

namespace WeSupply\Toolbox\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use WeSupply\Toolbox\Model\OrderInfoBuilder;
/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{

    const WESUPPLY_DOMAIN = 'labs.wesupply.xyz';
    const PROTOCOL = 'https';

    /**
     * Platform name
     */
    const PLATFORM = 'embedded';

    /**
     * array of carrier codes that are excluded from being sent to wesupply validation
     */
    const EXCLUDED_CARRIERS = [
        'flatrate',
        'tablerate',
        'freeshipping'
    ];

    /**
     * @var \WeSupply\Toolbox\Api\WeSupplyApiInterface
     */
    protected $weSupplyApi;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipconfig;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * Data constructor.
     * @param \WeSupply\Toolbox\Api\WeSupplyApiInterface $weSupplyApi
     */
    public function __construct(
        \WeSupply\Toolbox\Api\WeSupplyApiInterface $weSupplyApi,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Directory\Model\CountryFactory $countryFactory
        )
    {
        parent::__construct($context);
        $this->weSupplyApi = $weSupplyApi;
        $this->shipconfig = $shipconfig;
        $this->catalogSession = $catalogSession;
        $this->countryFactory = $countryFactory;
     }


    /**
     * @return mixed
     */
    public function getWeSupplyEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_2/access_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->getWeSupplySubDomain();
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        //return $this->scopeConfig->getValue('wesupply_api/massupdate/batch_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return 0;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return self::PROTOCOL;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return self::PLATFORM;
    }

    /**
     * @return mixed
     */
    public function getWeSupplyDomain()
    {
        return self::WESUPPLY_DOMAIN;
    }

    /**
     * @return mixed
     */
    public function getWeSupplySubDomain()
    {
       return $this->scopeConfig->getValue('wesupply_api/step_1/wesupply_subdomain', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getEnabledNotification()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/checkout_page_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getNotificationDesign()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/design_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getNotificationAlignment()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/design_notification_alingment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getNotificationBoxType()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/notification_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getEnableWeSupplyOrderView()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_order_view_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getWeSupplyApiClientId()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_1/wesupply_client_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getWeSupplyApiClientSecret()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_1/wesupply_client_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getWeSupplyOrderViewEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_order_view_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsHeaderLinkEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/enable_delivery_estimations_header_link', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/enable_delivery_estimations', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsRange()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/estimation_range', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsFormat()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/estimation_format', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|bool
     */
    public function getEstimationsDefaultCarrierAndMethod()
    {
        $defaultCarrier = $this->scopeConfig->getValue('wesupply_api/step_5/estimation_default_carrier', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($defaultCarrier == '0'){
            return FALSE;
        }

        try {
            $searchedMethod = strtolower($defaultCarrier);
            $defaultMethod = $this->scopeConfig->getValue('wesupply_api/step_5/estimation_carrier_methods_' . $searchedMethod, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


            return ['carrier' => $defaultCarrier , 'method'=> $defaultMethod];
        }catch (\Exception $e)
        {
            return FALSE;
        }


    }

    /**
     * @return mixed
     */
    public function orderViewModalEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_order_view_iframe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function trackingInfoIframeEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_tracking_info_iframe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orders
     * @return string
     */
    public function externalOrderIdString($orders)
    {
        $arrayOrders = $orders->toArray();

        $externalOrderIdString = implode(',', array_map(function($singleOrderArray) {
            return $singleOrderArray['increment_id'];
        }, $arrayOrders['items']));

        return $externalOrderIdString;
    }

    /**
     * @param $orders
     * @return string
     */
    public function internalOrderIdString($orders)
    {
        $arrayOrders = $orders->toArray();

        $externalOrderIdString = implode(',', array_map(function($singleOrderArray) {
            return OrderInfoBuilder::PREFIX.$singleOrderArray['entity_id'];
        }, $arrayOrders['items']));

        return $externalOrderIdString;
    }

    /**
     * maps the Wesupply Api Response containing links to each order, to an internal array
     */
    public function getGenerateOrderMap($orders)
    {
        $orderIds = $this->externalOrderIdString($orders);
        try{
            $this->weSupplyApi->setProtocol($this->getProtocol());
            $apiPath = $this->getWeSupplySubDomain().'.'.$this->getWeSupplyDomain().'/api/';
            $this->weSupplyApi->setApiPath($apiPath);
            $this->weSupplyApi->setApiClientId($this->getWeSupplyApiClientId());
            $this->weSupplyApi->setApiClientSecret($this->getWeSupplyApiClientSecret());

            $result = $this->weSupplyApi->weSupplyInterogation($orderIds);
        }catch(\Exception $e){
            echo $e->getMessage();
        }

        return $result;
    }

    /**
     * @param $string
     * @return float|int
     */
    public function strbits($string)
    {
        return (strlen($string)*8);
    }

    /**
     * @param $bytes
     * @return string
     */
    public function formatSizeUnits($bytes)
    {

        /**
         * transforming bytes in MB
         */
        if ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2);
        }
        else
        {
            return 0;
        }


        return $bytes;
    }

    /**
     * gets an array of al available shipping methods mapped to wesupply naming conventions
     * @return array
     */
    public function getMappedShippingMethods(){

        try {
            $activeCarriers = $this->shipconfig->getActiveCarriers();
            $methods = array();
            foreach ($activeCarriers as $carrierCode => $carrierModel) {

                if(in_array($carrierCode, self::EXCLUDED_CARRIERS)){
                    continue;
                }

                if(isset(WeSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode])){
                    $carrierCode = WeSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode];
                    $methods[] = $carrierCode;
                }
            }

            return $methods;

        }catch(\Exception $e){
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * returns mapped ups xml carrier code value
     * @param $magentoUpsCarrierCode
     * @return string
     */
    public function getMappedUPSXmlMappings($magentoUpsCarrierCode)
    {
        if(isset(WeSupplyMappings::UPS_XML_MAPPINGS[$magentoUpsCarrierCode])){
            return WeSupplyMappings::UPS_XML_MAPPINGS[$magentoUpsCarrierCode];
        }

        return '';
    }

    /**
     * @param $countryCode
     * @return string
     */
    public function getCountryname($countryCode)
    {
        try {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            return $country->getName();
        }catch(\Exception $e)
        {
            return '';
        }
    }

    /**
     * reverts back wesupply quotes to magento format
     * @param $quotes
     * @return array
     */
    public function revertWesupplyQuotesToMag($quotes)
    {
        $flipedCarrierMappings = array_flip(WeSupplyMappings::MAPPED_CARRIER_CODES);
        $mappedQuotes = [];
        foreach($quotes as $carrierKey => $values)
        {
            $magentoCarrierKey = $carrierKey;
            if(isset($flipedCarrierMappings[$carrierKey])){
                $magentoCarrierKey = $flipedCarrierMappings[$carrierKey];
            }
            $mappedQuotes[$magentoCarrierKey] = $values;
        }
        return $mappedQuotes;
    }

    /**
     * sets estimations data into session if session exists, otherwise creates a new session variable
     * @param $estimations
     */
    public function setEstimationsData($estimations)
    {
        $sessionEstimationsData = $this->catalogSession->getEstimationsData();
        /** existing session variable update */
        if ($sessionEstimationsData) {
            $sessionEstimationsArr = unserialize($sessionEstimationsData);
            if(isset($estimations['zip'])){
                $sessionEstimationsArr[$estimations['zip']] = $estimations;
                $sessionEstimationsArr['default'] = $estimations['zip'];
                $this->catalogSession->setEstimationsData(serialize($sessionEstimationsArr));
            }
          return;
        }

        /**  new session creation */
        if(isset($estimations['zip'])){
            $sessionEstimationsArr[$estimations['zip']] = $estimations;
            $sessionEstimationsArr['default'] = $estimations['zip'];
            $sessionEstimationsArr['created_at'] = time();
            $this->catalogSession->setEstimationsData(serialize($sessionEstimationsArr));
        }
        return;
    }

    /**
     * Generates all printable options for my account order view
     * @param $order
     * @return array
     */
    public function generateAllPrintableOptionsForOrder($order)
    {
        $options = [];
        $options[] = [
            'label' => __('Print...'),
            'url' => '#'
        ];

        if($order->hasInvoices()){
            $options[] = ['label' => 'All Invoices', 'url' => $this->getPrintAllInvoicesUrl($order)];
        }

        if($order->hasShipments()){
            $options[] = ['label' => 'All Shipments', 'url' => $this->getPrintAllShipmentsUrl($order)];
        }

        if($order->hasCreditmemos()){
            $options[] = ['label' => 'All Refunds', 'url' => $this->getPrintAllCreditMemoUrl($order)];
        }

        return $options;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getPrintAllInvoicesUrl($order)
    {
        return $this->_getUrl('sales/order/printInvoice', ['order_id' => $order->getId()]);
    }

    /**
     * @param $order
     * @return string
     */
    public function getPrintAllShipmentsUrl($order)
    {
        return $this->_getUrl('sales/order/printShipment', ['order_id' => $order->getId()]);
    }

    /**
     * @param $order
     * @return string
     */
    public function getPrintAllCreditMemoUrl($order)
    {
        return $this->_getUrl('sales/order/printCreditmemo', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getWesupplyFullDomain()
    {
        return $this->getProtocol() . '://' .  $this->getWeSupplySubDomain() . '.' . $this->getWeSupplyDomain() . '/';
    }
}
