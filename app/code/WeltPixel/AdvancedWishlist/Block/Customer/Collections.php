<?php

namespace WeltPixel\AdvancedWishlist\Block\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use WeltPixel\AdvancedWishlist\Model\MultipleWishlistProvider;
use Magento\Wishlist\Model\Wishlist as WishlistModel;
use WeltPixel\AdvancedWishlist\Helper\Data as AdvancedWishlistHelper;

/**
 * Class Collections
 * @package WeltPixel\AdvancedWishlist\Block\Customer
 */
class Collections extends Template implements IdentityInterface
{
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var integer
     */
    protected $profileId;

    /**
     * @var MultipleWishlistProvider
     */
    protected $multipleWishlistProvider;

    /**
     * @var AdvancedWishlistHelper
     */
    protected $advancedWishlistHelper;


    /**
     * Constructor
     *
     * @param Context $context
     * @param MultipleWishlistProvider $multipleWishlistProvider
     * @param AdvancedWishlistHelper $advancedWishlistHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        MultipleWishlistProvider $multipleWishlistProvider,
        AdvancedWishlistHelper $advancedWishlistHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->multipleWishlistProvider = $multipleWishlistProvider;
        $this->advancedWishlistHelper = $advancedWishlistHelper;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param integer $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @return array
     */
    public function getWishlistsForCustomer()
    {
        $isPublicWishlistEnabled = $this->advancedWishlistHelper->isPublicWishlistEnabled();
        if (!$isPublicWishlistEnabled) {
            return [];
        }
        $customer = $this->getCustomer();
        if (!$customer->getId()) {
            return [];
        }

        return $this->multipleWishlistProvider->getWishlistsForCustomer($customer->getId());
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getWishlistsForCustomer() as $wishlist) {
            $identities[] = WishlistModel::CACHE_TAG . '_' . $wishlist->getId();
        }

        if ($this->getProfileId()) {
            $identities[] =  'weltpixel_userprofile_' . $this->getProfileId();
        }

        return $identities;
    }
}
