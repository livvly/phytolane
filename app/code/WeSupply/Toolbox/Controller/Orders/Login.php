<?php

namespace WeSupply\Toolbox\Controller\Orders;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManagerInterface;

class Login extends Action
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
     * Login constructor.
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
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if (!$this->getAuthToken()) {
            return $this->_redirect('tracking-info');
        }

        $this->_session->unsFirstAttempt();
        if ($this->getAuthTokenFromParams()) {
            $this->_session->setFirstAttempt(true);

            $this->_session->setSessionAuthSearchBy($this->getAuthSearchByFromParams());
            $this->_session->setSessionAuthToken($this->getAuthTokenFromParams());
        }

        return $this->_redirect('wesupply/orders/view');
    }

    /**
     * @return mixed|null
     */
    private function getAuthTokenFromParams()
    {
        return $this->getRequest()->getParam('token') ?? null;
    }

    /**
     * @return array
     */
    private function getAuthSearchByFromParams()
    {
        if ($this->getRequest()->getParam('embedded-oid')) {
            return ['embedded-oid' => $this->getRequest()->getParam('embedded-oid')];
        }

        if ($this->getRequest()->getParam('embedded-em')) {
            return ['embedded-em' => $this->getRequest()->getParam('embedded-em')];
        }

        return [];
    }

    /**
     * @return mixed|null
     */
    private function getAuthToken()
    {
        if ($this->getAuthTokenFromParams()) {
            return $this->getAuthTokenFromParams();
        }

        return $this->_session->getSessionAuthToken();
    }
}