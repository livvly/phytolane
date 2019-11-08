<?php

namespace WeSupply\Toolbox\Model\Config\Source\CarrierMethods;

use \Magento\Shipping\Model\CarrierFactoryInterface;

class USPS implements \Magento\Framework\Option\ArrayInterface
{
    private $carrierFactory;

    const CARRIER_CODE = 'usps';

    public function __construct(CarrierFactoryInterface $carrierFactory)
    {
        $this->carrierFactory = $carrierFactory;
    }

    public function toOptionArray()
    {
        $carrier = $this->carrierFactory->create(self::CARRIER_CODE);
        if(!$carrier){
            return [];
        }

        $options = array();
        if(method_exists($carrier,'getAllowedMethods')) {
            $allowedMethods = $carrier->getAllowedMethods();
            foreach ($allowedMethods as $code => $method) {
                $options[] = ['value' => $code, 'label' => $method];
            }
        }

        return $options;
    }
}