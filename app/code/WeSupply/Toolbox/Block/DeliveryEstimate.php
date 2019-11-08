<?php
namespace WeSupply\Toolbox\Block;
use Magento\Framework\View\Element\Template;
class DeliveryEstimate extends Template
{

    /**
     * @var
     */
    private $product;
    /**
     * @var \WeSupply\Toolbox\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var array
     */
    private $defaultAddress = [];
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $prdHelper;
    /**
     * DeliveryEstimate constructor.
     * @param Template\Context $context
     * @param \WeSupply\Toolbox\Helper\Data $helper
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Catalog\Block\Product\Context $prdcontext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \WeSupply\Toolbox\Helper\Data $helper,
        \Magento\Checkout\Model\Session $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Helper\Data $prdHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $session;
        $this->customerSession = $customerSession;
        $this->catalogSession = $catalogSession;
        $this->scopeConfig = $scopeConfig;
        $this->prdHelper = $prdHelper;
        $this->setCurrentProduct();
        $this->initDefaultAddress();
        parent::__construct($context, $data);
    }
    /**
     * for logged in customers, we init the default address
     */
    private function initDefaultAddress()
    {
        $customer = $this->customerSession->getCustomer();
        if($customer){
            $defaultAddress = $customer->getDefaultShippingAddress();
            if(!$defaultAddress){
                $defaultAddress = $customer->getDefaultBillingAddress();
            }
            if($defaultAddress){
                $this->defaultAddress = $defaultAddress;
            }
        }
    }
    /**
     * @return array|bool
     */
    public function getSelectedDeliveryEstimate()
    {
        $sessionEstimationsData = $this->catalogSession->getEstimationsData();
        if(!$sessionEstimationsData){
            return false;
        }
        $estimationsArr = unserialize($sessionEstimationsData);
        /**
         * if estimations session array was created more then 3 hours ago, we destroy it
         */
        if(isset($estimationsArr['created_at'])){
            if( (time() - $estimationsArr['created_at']) > 10800){
                $this->catalogSession->unsEstimationsData();
                return false;
            }
        }
        if(isset($estimationsArr['default'])){
            $selectedZip = $estimationsArr['default'];
            if(isset($estimationsArr[$selectedZip])){
                if(isset($estimationsArr[$selectedZip]['estimated_arrival'])){
                    $result = array();
                    $estimatedDelivery = $estimationsArr[$selectedZip]['estimated_arrival'];
                    $countrycode = (isset($estimationsArr[$selectedZip]['countrycode'])) ? $estimationsArr[$selectedZip]['countrycode'] : '';
                    $country = $this->helper->getCountryname($countrycode);
                    $result['estimatedDelivery'] = $estimatedDelivery;
                    $result['zipcode'] = $selectedZip;
                    $result['country'] = $country;
                    return $result;
                }
            }
        }
        return false;
    }
    /**
     * @param $key
     * @return null
     */
    public function getAddressDetail($key)
    {
        if(is_object($this->defaultAddress) && method_exists($this->defaultAddress, 'getData')){
            try{
                return $this->defaultAddress->getData($key);
            }catch(\Exception $e){
                return null;
            }
        }
    }
    /**
     * @return bool|mixed
     */
    public function getDeliveryEstimationsEnabled()
    {
        if($this->helper->getWeSupplyEnabled()) {
            return $this->helper->getDeliveryEstimationsEnabled();
        }
        return false;
    }
    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }
    /**
     * @return bool
     */
    public function productIsShippable()
    {
        if(!$this->product){
            return false;
        }
        if( !$this->product->isSaleable()
            || $this->product->getTypeId() ==  \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            || $this->product->getTypeId() ==  \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
            || $this->product->getIsVirtual())
        {
            return false;
        }
        return true;
    }
    /**
     * @return bool
     */
    public function checkQuoteIsVirtual()
    {
        if($this->checkoutSession instanceof \Magento\Checkout\Model\Session ){
            return $this->checkoutSession->getQuote()->isVirtual();
        }
        return true;
    }
    /**
     * @return float|int
     */
    public function getQuoteTotal()
    {
        if($this->checkoutSession instanceof \Magento\Checkout\Model\Session ) {
            return $this->checkoutSession->getQuote()->getGrandTotal();
        }
        return 0;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    /**
     * @return string
     */
    public function getEstimationsUrl()
    {
        return $this->getUrl('wesupply/estimations/estimatedelivery');
    }
    /**
     * setting the current product internally
     */
    private function setCurrentProduct()
    {
        $this->product = $this->prdHelper->getProduct();
    }
    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
    /**
     * @return int
     */
    public function getProductPrice()
    {
        if(!$this->product){
            return 1;
        }
        return $this->product->getFinalPrice();
    }
}