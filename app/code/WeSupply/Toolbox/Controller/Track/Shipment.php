<?php

namespace WeSupply\Toolbox\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Shipment extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * Shipment constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    )
    {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if (!$this->getTrackingCode()) {
            return $this->_redirect('/');
        }

        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shipment Tracking'));

        return $resultPage;
    }

    /**
     * @return mixed|null
     */
    private function getTrackingCode()
    {
        $keys = array_keys($this->getRequest()->getParams());

        return reset($keys) ?? null;
    }
}