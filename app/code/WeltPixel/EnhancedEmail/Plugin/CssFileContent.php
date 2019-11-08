<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

/**
 * Class CssFileContent
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class CssFileContent
{
    /**
     * var \WeltPixel\CustomHeader\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CssFileContent constructor.
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Fix for magento instances that have theme(s), wich does not extend
     * a magento default theme (blank or luma)
     *
     * @param \Magento\Framework\Css\PreProcessor\Adapter\CssInliner $subject
     * @param $css
     * @return array
     */
    public function aroundGetCssFilesContent(\Magento\Email\Model\Template\Filter $subject, \Closure $proceed, $files)
    {
        $originalCss = $proceed($files);
        if(!$this->_helper->isEnabled($this->_storeManager->getStore()->getId())) {
            return $originalCss;
        }

        $css = '';
        if(!$originalCss) {
            $css = 'tfoot.order-totals th, tfoot.order-totals td {text-align: right} 
            table.order-details{width: 100%}
            table.button table.inner-wrapper td{border-radius: 3px}
            table.button table.inner-wrapper td a{display: inline-block; text-decoration: none; padding: 7px 15px}';
        }
        $result = $originalCss . $css;
        return $result;
    }


}