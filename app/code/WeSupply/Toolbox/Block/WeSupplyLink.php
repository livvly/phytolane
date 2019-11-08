<?php
namespace WeSupply\Toolbox\Block;

use Magento\Framework\View\Element\Template;

class WeSupplyLink extends \Magento\Framework\View\Element\Html\Link
{
    protected $helper;

    public function __construct(
        Template\Context $context,
        \WeSupply\Toolbox\Helper\Data $helper,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }


    public function getHref(){
        if ($this->helper->trackingInfoIframeEnabled()) {
            return '/tracking-info';
        }

        return  $this->helper->getProtocol(). '://' . $this->helper->getWeSupplySubDomain() . '.' . $this->helper->getWeSupplyDomain().'/';
    }

    public function getLabel(){
        return __('Tracking Info');
    }

    public function getTarget()
    {
        if ($this->helper->trackingInfoIframeEnabled()) {
            return __('_self');
        }

        return __('_blank');
    }

    public function getClass()
    {
        return __('wesupply-tracking-info');
    }

    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        if(!$this->helper->getWeSupplyEnabled() || !$this->helper->getDeliveryEstimationsHeaderLinkEnabled()){
            return;
        }


        return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
    }
}