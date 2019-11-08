<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

/**
 * Class CssInliner
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class CssDirective
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Fonts
     */
    protected $_fontHelper;

    /**
     * var \WeltPixel\CustomHeader\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CssDirective constructor.
     * @param \WeltPixel\EnhancedEmail\Helper\Fonts $fontHelper
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \WeltPixel\EnhancedEmail\Helper\Fonts $fontHelper,
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_fontHelper = $fontHelper;
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Inject fonts configured in backend
     * @param \Magento\Email\Model\Template\Filter $subject
     * @param $result
     * @return string
     */
    public function afterCssDirective(\Magento\Email\Model\Template\Filter $subject, $result)
    {
        if(!$this->_helper->isEnabled($this->_storeManager->getStore()->getId())) {
            return $result;
        }
        $fontCss = '@import url("' .$this->_fontHelper->getGoogleFonts().'");' . PHP_EOL;
        $result = $fontCss . $result;
        return $result;
    }


}