<?php

namespace WeltPixel\CmsBlockScheduler\Model;

/**
 * Tag Model
 * @category WeltPixel
 * @package  WeltPixel_CmsBlockScheduler
 * @module   CmsBlockScheduler
 * @author   WeltPixel Developer
 */
class Block extends \Magento\Cms\Model\Block
{
    protected $_date;
	protected $_helper;
	protected $_customerSession;

    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
		\WeltPixel\CmsBlockScheduler\Helper\Data  $helper,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context, $registry);

        $this->_date = $date;
	    $this->_helper = $helper;
	    $this->_customerSession = $customerSession;
    }
    
    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->_helper->resourceEnabled('customer_group')) {

            $block_customerGroupId = explode(',', $this->getCustomerGroup());
            $customerGroupId = 0;
            
            if ($this->_customerSession->isLoggedIn()) {
                $customerGroupId = $this->_customerSession->getCustomerGroupId();
            }

            $fvalue = array_key_exists(0, $block_customerGroupId) ? $block_customerGroupId[0] : '';
            if ($fvalue != '' && !in_array($customerGroupId, $block_customerGroupId)) {
                return false;
            }
        }

        if ($this->_helper->resourceEnabled('date_range')) {

            $validFrom = $this->getValidFrom();
            $validTo   = $this->getValidTo();

            $now = $this->_date->gmtDate();

            if (($validFrom && $validFrom > 0) && ($validFrom > $now)) {
                return false;
            }
            if (($validTo && $validTo > 0) && ($validTo < $now)) {
                return false;
            }
        }

        return parent::isActive();
    }

    /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     */
    public function beforeSave()
    {
        if ($this->getCustomerGroup()) {
            $customerGroup = implode(',', $this->getCustomerGroup());
            $this->setCustomerGroup($customerGroup);
        }

        parent::beforeSave();
    }
}
