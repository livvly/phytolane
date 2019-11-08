<?php

namespace WeSupply\Toolbox\Controller\Orders;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManagerInterface;

class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        SessionManagerInterface $session
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_session = $session;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if (!$this->_session->getSessionAuthToken()) {
            return $this->_redirect('tracking-info');
        }

        // load orders iframe
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Orders View'));

        return $resultPage;
    }
}