<?php

namespace WeSupply\Toolbox\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use WeSupply\Toolbox\Helper\Data as Helper;

class Track extends Template
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Track constructor.
     * @param Context $context
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        Helper $helper
    )
    {
        $this->params = $context->getRequest()->getParams();
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->helper->getPlatform();
    }

    /**
     * @return mixed|string
     */
    public function getTrackingCode()
    {
        $keys = array_keys($this->getParams());

        return reset($keys) ?? '';
    }

    /**
     * @return string
     */
    public function getWeSupplyTrackUrl()
    {
        $protocol = $this->helper->getProtocol();
        $domaine = $this->helper->getWeSupplyDomain();
        $subDomaine = $this->helper->getWeSupplySubDomain();

        return $protocol . '://' . $subDomaine . '.' . $domaine . '/track/';
    }

    /**
     * @return array
     */
    private function getParams()
    {
        return $this->params;
    }
}