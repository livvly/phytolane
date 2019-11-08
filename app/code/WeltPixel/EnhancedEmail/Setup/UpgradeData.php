<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Email\Model\Template\Config;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template;

/**
 * Class UpgradeData
 * @package WeltPixel\EnhancedEmail\Setup
 *
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var Config
     */
    protected $_emailConfig;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var array|array[]
     */
    protected $_templates = [];

    protected $_template;

    /**
     * UpgradeData constructor.
     * @param BlockFactory $blockFactory
     * @param WriterInterface $configWriter
     * @param TemplateFactory $templateFactory
     * @param Config $emailConfig
     */
    public function __construct(
        BlockFactory $blockFactory,
        WriterInterface $configWriter,
        TemplateFactory $templateFactory,
        Config $emailConfig,
        CollectionFactory $collectionFactory,
        Template $template
    )
    {
        $this->blockFactory = $blockFactory;
        $this->configWriter = $configWriter;
        $this->templateFactory = $templateFactory;
        $this->_emailConfig = $emailConfig;
        $this->_collectionFactory = $collectionFactory;
        $this->_template = $template;

        $this->_templates = $this->_getTemplatesArr();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), "1.0.1", "<")) {

            $content = <<<EOT
                <a style="padding-left: 30px" href="http://facebook.com/#"><img src="{{view url='WeltPixel_EnhancedEmail/images/fb.png'}}" alt="fb" width="15" height="15" /></a> 
                <a style="padding-left: 30px" href="http://twitter.com/#"><img src="{{view url='WeltPixel_EnhancedEmail/images/twitter.png'}}" alt="twitter" width="15" height="15" /></a> 
                <a style="padding-left: 30px" href="http://instagram.com/#"><img src="{{view url='WeltPixel_EnhancedEmail/images/instagram.png'}}" alt="instagram" width="15" height="15" /></a> 
                <a style="padding-left: 30px" href="http://youtube.com/#"><img src="{{view url='WeltPixel_EnhancedEmail/images/youtube.png'}}" alt="youtube" width="15" height="15" /></a>
EOT;

            // social media block
            $cmsBlockData = [
                'title' => 'EnhancedEmail Social Media Block',
                'identifier' => 'weltpixel_social_media_email_block',
                'content' => $content,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            $this->blockFactory->create()->setData($cmsBlockData)->save();

            // custom block
            $cmsCustomBlockData = [
                'title' => 'EnhancedEmail Custom Block',
                'identifier' => 'weltpixel_custom_block_1',
                'content' => "<h3>Enhanced Email custom block content.</h3>",
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            $this->blockFactory->create()->setData($cmsCustomBlockData)->save();

            $socialMediaBlock = <<<EOT
block class="Magento\\\Cms\\\Block\\\Block" area="frontend" block_id="weltpixel_social_media_email_block" 
EOT;
            $customBlock = <<<EOT
block class="Magento\\\Cms\\\Block\\\Block" area="frontend" block_id="weltpixel_custom_block_1"
EOT;
            $this->configWriter->save('weltpixel/enhancedemail/social_media_email_block', $socialMediaBlock, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            $this->configWriter->save('weltpixel/enhancedemail/custom_block_1', $customBlock, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        }

        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $shipmentMarkup = 'layout handle="shipment_markup" order=$order shipment=$shipment area="frontend"';
            $this->configWriter->save('weltpixel/enhancedemail/shipment_markup', $shipmentMarkup, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

        }

        if (version_compare($context->getVersion(), "1.0.3", "<")) {
            $content = <<<EOT
<p><strong>RETURNS</strong>: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
EOT;

            $cmsCustomBlockData = [
                'title' => 'EnhancedEmail Custom Block - Returns',
                'identifier' => 'weltpixel_custom_block_returns',
                'content' => $content,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            $this->blockFactory->create()->setData($cmsCustomBlockData)->save();

        }

        if (version_compare($context->getVersion(), "1.0.4", "<")) {

            $templateType = TemplateTypesInterface::TYPE_HTML;

            // Enhanced Email Hader
            $tplCode = 'Email Header - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplVars = '{"var logo_height":"Email Logo Image Height","var logo_width":"Email Logo Image Width","var template_styles|raw":"Template CSS"}';
                $tplText = <<<EOT
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0, width=device-width" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <style type="text/css">
        {{var template_styles|raw}}

        {{css file="css/email.css"}}
    </style>
</head>
<body style="padding:0 !important;">
{{inlinecss file="css/email-inline.css"}}

<!-- Begin wrapper table -->
<table class="wrapper" width="100%">
    <tr>
        <td class="wrapper-inner" align="center">
            <table class="main" align="center">
                <tr>
                    <td class="header" align="center">
                        {{layout handle="light_logo" area="frontend"}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {{layout handle="menu_line" area="frontend"}}
                    </td>
                </tr>
                <tr>
                    <td class="main-content" style="padding:0 !important">
                        <!-- Begin Content -->
EOT;
                $tplSubject = <<<EOT
{{trans "Header"}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateStyles('');
                $template->setTemplateType($templateType);
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('design_email_header_template');
                $template->save();
            }


// Enhanced Email Footer
            $tplCode = 'Email Footer - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplVars = '{"var store.getFrontendName()":"Store Name"}';
                $tplText = <<<'EOT'
<!--@subject {{trans "Footer"}} @-->
<!--@vars {
"var store.getFrontendName()":"Store Name"
} @-->

<!-- End Content -->
            </td>
                </tr>
                <tr>
                    <table style="width: 660px" cellpadding="5" align="center">
                        <tr style="height: 35px"><td colspan="2"></td></tr>
                        <tr style="border-top: 2px solid #cfd4d4"><td colspan="2"></td></tr>
                        <tr>
                            <td style="vertical-align: middle; text-align: center; border-right: 2px solid #cfd4d4; width: 45%">
                                <a style="color: #000; font-weight: bold; padding: 0 15px;text-decoration: none;" href="{{var this.getUrl($store,'contact',[],_nosid:1])}}" target="_blank" style="">{{trans 'CONTACT'}}</a>
                                <a style="color: #000; font-weight: bold; padding: 0 15px;text-decoration: none;" href="{{var this.getUrl($store,'privacy-policy-cookie-restriction-mode',[],_nosid:1])}}" target="_blank" style="">{{trans 'PRIVACY'}}</a>
                            </td>
                            <td style="vertical-align: middle; text-align: center">
                                {{block class="Magento\Cms\Block\Block" area="frontend" block_id="weltpixel_social_media_email_block" }}
                            </td>
                        </tr>
                        <tr style="border-bottom: 2px solid #cfd4d4"><td colspan="2"></td></tr>
                    </table>
                </tr>
                <tr>
                    <td>
                        <div style="padding: 15px 0; text-align: center; font-size: 1em">
                            <h3 style="display:inline-block !important; margin:0 !important">{{trans 'QUESTIONS?'}}</h3>
                            <h3 style="display:inline-block !important; margin:0 !important; padding-left: 15px">{{trans 'FEEDBACK?'}}</h3>
                            <h3 style="display:inline-block !important; margin:0 !important; padding-left: 15px">1.800.WELTPIXEL</h3></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="text-align: center; font-size: 1em; color: #555656">WeltPixel Corporation | 201 Main Street | New York, NY 10015</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
<!-- End wrapper table -->
</body>
EOT;
                $tplSubject = <<<EOT
{{trans "Footer"}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('design_email_footer_template');
                $template->save();
            }


//New Order
            $tplCode = 'New Order - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just received your order! Stay close! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by checking your account.';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.getBillingAddress().getName()":"Guest Customer Name","var order.getCreatedAtFormatted(1)":"Order Created At (datetime)","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="order_markup" order=$order area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
        <tr>
            <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
                <h3 style="text-align: center; text-transform: uppercase;">
                    {{trans 'We\'re on it.'}}
                </h3>
            </td>
        </tr>
        <tr>
            <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
                <h1 style="text-align: center; margin: 0 !important">
                    {{trans 'We just received your order!'}}
                </h1>
            </td>
        </tr>
        <tr>
            <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
                <h3 style="text-align: center;">
                    {{trans 'ORDER NUMBER: <span class="no-link">%increment_id</span>' increment_id=$order.increment_id |raw}}
                </h3>
            </td>
        </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
        <tr>
            <td align="center" style="padding-top: 10px;padding-bottom:10px;">
                <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                    {{trans 'Stay close! We will send you update along the way!'}}
                </h2>
            </td>
        </tr>
        <tr>
            <td style="margin-left: 0px">
                <p>
                    {{trans 'You can view the entire status of your order by checking <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
                </p>
            </td>
        </tr>
        <tr>
            <td style="margin-left: 0px">
                <p>
                    {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
                </p>
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}

            {{layout handle="weltpixel_sales_email_order_items" order=$order area="frontend"}}

            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                            <tr>
                                <td align="center" style="padding: 8px 0 !important">
                                    <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 35px">
            {{block class="Magento\Cms\Block\Block" area="frontend" block_id="weltpixel_custom_block_returns"}}
        </td>
    </tr>
</table>



{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_order_weltpixel');
                $template->save();
            }

//New Order Guest
            $tplCode = 'New Order for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just received your order! Stay close! We will send you updates along the way once we dispatch your product(s).You can view the entire status of your orders by creating an account.';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.getBillingAddress().getName()":"Guest Customer Name","var order.getCreatedAtFormatted(1)":"Order Created At (datetime)","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="order_markup" order=$order area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans 'We\'re on it.'}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'We just received your order!'}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'ORDER NUMBER: <span class="no-link">%increment_id</span>' increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Stay close! We will send you update along the way!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}

            {{layout handle="weltpixel_sales_email_order_items" order=$order area="frontend"}}

            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 35px">
            {{block class="Magento\Cms\Block\Block" area="frontend" block_id="weltpixel_custom_block_returns"}}
        </td>
    </tr>
</table>


{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_order_guest_weltpixel');
                $template->save();
            }



//Order Update
            $tplCode = 'Order Update - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated your order information! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by checking your account.';
                $tplVars = <<<'EOT'
{"var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var comment":"Order Comment","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="order_markup" order=$order area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$order.getCustomerName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "ORDER %order_status" order_status=$order.getStatusLabel()}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans
                "ORDER NUMBER: %increment_id " increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'You can check the status of your order by logging into <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
            </p>
        </td>
    </tr>
    <tr>
        <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                        <tr>
                            <td align="center" style="padding: 8px 0 !important">
                                <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">

    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name order" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('order_update_weltpixel');
                $template->save();
            }




//Order Update Guest
            $tplCode = 'Order Update for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated your order information! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by creating an account.';
                $tplVars = <<<'EOT'
{"var billing.getName()":"Guest Customer Name","var comment":"Order Comment","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}
EOT;

                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="order_markup" order=$order area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$billing.getName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "ORDER %order_status" order_status=$order.getStatusLabel()}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans
                "ORDER NUMBER: %increment_id " increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name order" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('order_update_guest_weltpixel');
                $template->save();
            }




//New Invoice
            $tplCode = 'New Invoice - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just issued the invoice for your order! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by checking your account.';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var comment":"Invoice Comment","var invoice.increment_id":"Invoice Id","layout area=\"frontend\" handle=\"sales_email_order_invoice_items\" invoice=$invoice order=$order":"Invoice Items Grid","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.shipping_description":"Shipping Description","var order.getShippingDescription()":"Shipping Description"}
EOT;

                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="invoice_markup" invoice=$invoice area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$order.getCustomerName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "Here\’s your Invoice :)" }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your Invoice #%invoice_id' invoice_id=$invoice.increment_id |raw}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'You can view the entire status of your order by checking <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
            </p>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>

<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}

            {{layout handle="weltpixel_sales_email_order_invoice_items" invoice=$invoice order=$order area="frontend"}}

            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                            <tr>
                                <td align="center" style="padding: 8px 0 !important">
                                    <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Invoice for your %store_name order" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_invoice_weltpixel');
                $template->save();
            }





//New Invoice Guest
            $tplCode = 'New Invoice for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just issued the invoice for your order! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by creating an account.';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var billing.getName()":"Guest Customer Name","var comment":"Invoice Comment","var invoice.increment_id":"Invoice Id","layout handle=\"sales_email_order_invoice_items\" invoice=$invoice order=$order":"Invoice Items Grid","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var order.shipping_description":"Shipping Description"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="invoice_markup" invoice=$invoice area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center;text-transform: uppercase;">
                {{trans "Hi %name," name=$billing.getName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "Here\’s your Invoice :)" }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your Invoice #%invoice_id' invoice_id=$invoice.increment_id |raw}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>

<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            
            {{layout handle="weltpixel_sales_email_order_invoice_items" invoice=$invoice order=$order area="frontend"}}

            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Invoice for your %store_name order" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_invoice_guest_weltpixel');
                $template->save();
            }





//Invoice Update
            $tplCode = 'Invoice Update - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated your order invoice! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by checking your account.';
                $tplVars = <<<'EOT'
{"var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var comment":"Invoice Comment","var invoice.increment_id":"Invoice Id","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="invoice_markup" invoice=$invoice area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center;text-transform: uppercase;">
                {{trans "Hi %name," name=$order.getCustomerName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "INVOICE %order_status" order_status=$order.getStatusLabel()" }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans
                "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'You can check the status of your order by logging into <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
            </p>
        </td>
    </tr>
    <tr>
        <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                        <tr>
                            <td align="center" style="padding: 8px 0 !important">
                                <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name invoice" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('invoice_update_weltpixel');
                $template->save();
            }


//Invoice Update Guest
            $tplCode = 'Invoice Update for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated your order invoice! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by creating an account.';
                $tplVars = <<<'EOT'
{"var billing.getName()":"Guest Customer Name","var comment":"Invoice Comment","var invoice.increment_id":"Invoice Id","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="invoice_markup" invoice=$invoice area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center;text-transform: uppercase;">
                {{trans "Hi %name," name=$billing.getName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "INVOICE %order_status" order_status=$order.getStatusLabel()" }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans
                "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name invoice" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('invoice_update_guest_weltpixel');
                $template->save();
            }


//New Creditmemo
            $tplCode = 'New Credit Memo - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just issued the credit memo for your order! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by checking your account.';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var comment":"Credit Memo Comment","var creditmemo.increment_id":"Credit Memo Id","layout handle=\"sales_email_order_creditmemo_items\" creditmemo=$creditmemo order=$order":"Credit Memo Items Grid","var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var order.shipping_description":"Shipping Description"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="creditmemo_markup" creditmemo=$creditmemo area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$order.getCustomerName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "We just credited your order!" }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your Credit Memo #%creditmemo_id' creditmemo_id=$creditmemo.increment_id |raw}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'You can view the entire status of your order by checking <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
            </p>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            {{layout handle="weltpixel_sales_email_order_creditmemo_items" creditmemo=$creditmemo order=$order}}
            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                            <tr>
                                <td align="center" style="padding: 8px 0 !important">
                                    <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Credit memo for your %store_name order" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_creditmemo_weltpixel');
                $template->save();
            }


//New Creditmemo Guest
            $tplCode = 'New Credit Memo for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just issued the credit memo for your order! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by creating an account.';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var comment":"Credit Memo Comment","var creditmemo.increment_id":"Credit Memo Id","layout handle=\"sales_email_order_creditmemo_items\" creditmemo=$creditmemo order=$order":"Credit Memo Items Grid","var billing.getName()":"Guest Customer Name (Billing)","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var order.shipping_description":"Shipping Description"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="creditmemo_markup" creditmemo=$creditmemo area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$billing.getName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "We just credited your order!"}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your Credit Memo #%creditmemo_id' creditmemo_id=$creditmemo.increment_id |raw}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            {{layout handle="weltpixel_sales_email_order_creditmemo_items" creditmemo=$creditmemo order=$order}}
            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Credit memo for your %store_name order" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_creditmemo_guest_weltpixel');
                $template->save();
            }


//Creditmemo Update
            $tplCode = 'Credit Memo Update - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated the credit memo for your order! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by checking your account.';
                $tplVars = <<<'EOT'
{"var comment":"Credit Memo Comment","var creditmemo.increment_id":"Credit Memo Id","var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="creditmemo_markup" creditmemo=$creditmemo area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$order.getCustomerName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "ORDER %order_status" order_status=$order.getStatusLabel()" }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'You can check the status of your order by logging into <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
            </p>
        </td>
    </tr>
    <tr>
        <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                        <tr>
                            <td align="center" style="padding: 8px 0 !important">
                                <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>

    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name credit memo" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('creditmemo_update_weltpixel');
                $template->save();
            }


//Creditmemo Update Guest
            $tplCode = 'Credit Memo Update for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated the credit memo for your order! We will send you updates along the way once we dispatch your product(s). You can view the entire status of your order by creating an account.';
                $tplVars = <<<'EOT'
{"var comment":"Credit Memo Comment","var creditmemo.increment_id":"Credit Memo Id","var billing.getName()":"Guest Customer Name","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="creditmemo_markup" creditmemo=$creditmemo area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$billing.getName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "ORDER %order_status" order_status=$order.getStatusLabel()}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans
                "ORDER NUMBER: #%increment_id." increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name credit memo" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('creditmemo_update_guest_weltpixel');
                $template->save();
            }

//New Shipment
            $tplCode = 'New Shipment - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Your products are on the way, stay tuned! You can view the entire status of your order by checking your account. Thank you for your for your purchase and hope to shop with us again soon!';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var comment":"Shipment Comment","var shipment.increment_id":"Shipment Id","layout handle=\"sales_email_order_shipment_items\" shipment=$shipment order=$order":"Shipment Items Grid","block class='Magento\\\\Framework\\\\View\\\\Element\\\\Template' area='frontend' template='Magento_Sales::email\/shipment\/track.phtml' shipment=$shipment order=$order":"Shipment Track Details","var formattedShippingAddress|raw":"Shipping Address","var order.shipping_description":"Shipping Description","var order.getShippingDescription()":"Shipping Description"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans 'Great News!'}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Your order is now shipped!'}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %order_id" order_id=$order.increment_id}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your Shipment #%shipment_id' shipment_id=$shipment.increment_id}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}

            {{layout handle="weltpixel_sales_email_order_shipment_items" shipment=$shipment order=$order}}

            {{block class='Magento\\Framework\\View\\Element\\Template' area='frontend' template='Magento_Sales::email/shipment/track.phtml' shipment=$shipment order=$order}}
            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>

        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                            <tr>
                                <td align="center" style="padding: 8px 0 !important">
                                    <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 35px">
            {{block class="Magento\Cms\Block\Block" area="frontend" block_id="weltpixel_custom_block_returns"}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Your %store_name order has shipped" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_shipment_weltpixel');
                $template->save();
            }



//New Shipment Guest
            $tplCode = 'New Shipment for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Your products are on the way, stay tuned! You can view the entire status of your order by creating an account. Thank you for your for your purchase and hope to shop with us again soon!';
                $tplVars = <<<'EOT'
{"var formattedBillingAddress|raw":"Billing Address","var billing.getName()":"Guest Customer Name","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var comment":"Shipment Comment","var shipment.increment_id":"Shipment Id","layout handle=\"sales_email_order_shipment_items\" shipment=$shipment order=$order":"Shipment Items Grid","block class='Magento\\\\Framework\\\\View\\\\Element\\\\Template' area='frontend' template='Magento_Sales::email\/shipment\/track.phtml' shipment=$shipment order=$order":"Shipment Track Details","var formattedShippingAddress|raw":"Shipping Address","var order.shipping_description":"Shipping Description","var order.getShippingDescription()":"Shipping Description"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans 'Great News!'}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Your order is now shipped!'}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %order_id" order_id=$order.increment_id}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your Shipment #%shipment_id' shipment_id=$shipment.increment_id}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}

            {{layout handle="weltpixel_sales_email_order_shipment_items" shipment=$shipment order=$order}}

            {{block class='Magento\\Framework\\View\\Element\\Template' area='frontend' template='Magento_Sales::email/shipment/track.phtml' shipment=$shipment order=$order}}
            <table class="order-details" style="border-top: 5px solid #000000">
                <tr>
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "BILLING ADDRESS"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details" style="padding-top: 60px !important">
                        <h3>{{trans "SHIPPING ADDRESS"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info wp-method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "PAYMENT METHOD"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info" style="padding-bottom: 60px !important">
                        <h3>{{trans "SHIPPING METHOD"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>

        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 35px">
            {{block class="Magento\Cms\Block\Block" area="frontend" block_id="weltpixel_custom_block_returns"}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Your %store_name order has shipped" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('new_shipment_guest_weltpixel');
                $template->save();
            }


//Shipment Update
            $tplCode = 'Shipment Update - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated the shipping information for your recent order!  You can view the entire status of your order by checking your account. Thank you for your for your purchase!';
                $tplVars = <<<'EOT'
{"var this.getUrl($store, 'customer/account/')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var comment":"Order Comment","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status","var shipment.increment_id":"Shipment Id"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$order.getCustomerName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "ORDER %order_status" order_status=$order.getStatusLabel()}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id " increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'You can check the status of your order by logging into <a href="%account_url">your account</a>.' account_url=$this.getUrl($store,'customer/account/',[_nosid:1]) |raw}}
            </p>
        </td>
    </tr>
    <tr>
        <table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                        <tr>
                            <td align="center" style="padding: 8px 0 !important">
                                <a href="{{var this.getUrl($store,'customer/account/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "VIEW ORDER"}}</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name shipment" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('shipment_update_weltpixel');
                $template->save();
            }


//Shipment Update Guest
            $tplCode = 'Shipment Update for Guest - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'We just updated the shipping information for your recent order!  You can view the entire status of your order by creating an account. Thank you for your for your purchase!';
                $tplVars = <<<'EOT'
{"var billing.getName()":"Guest Customer Name","var comment":"Order Comment","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status","var shipment.increment_id":"Shipment Id"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$billing.getName()}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans "ORDER %order_status" order_status=$order.getStatusLabel()}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans "ORDER NUMBER: %increment_id " increment_id=$order.increment_id |raw}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>
<table align="center" style="padding-bottom:5px; padding-top:20px; width: 660px">
    <tbody>
    <tr>
        <td align="center" style="padding-top: 10px;padding-bottom:10px;">
            <h2 style="text-align: center; margin: 0 0 20px 0 !important">
                {{trans 'Your order has been updated!'}}
            </h2>
        </td>
    </tr>
    <tr>
        <td style="margin-left: 0px">
            <p>
                {{trans 'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
            </p>
        </td>
    </tr>
    </tbody>
</table>
<table style="width: 660px">
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Update to your %store_name shipment" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('shipment_update_guest_weltpixel');
                $template->save();
            }



 //New Account
            $tplCode = 'New Account - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Welcome, your new account is ready ! To sign in to our site, use the credentials during checkout or on the My Account page. You will be able to checkout faster, check orders status, view past orders and more.';
                $tplVars = <<<'EOT'
{"var this.getUrl($store, 'customer/account/')":"Customer Account URL","var customer.email":"Customer Email","var customer.name":"Customer Name"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_account_action" customer=$customer area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$customer.name}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'You\'re in :)' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Your account is now active.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>


<p style="margin: 20px 0 !important">
    {{trans
        'To sign in, use these credentials during checkout or when logging into <a href="%customer_url">your account</a>.'
        customer_url=$this.getUrl($store,'customer/account/',[_nosid:1])
    |raw}}
</p>
<ul>
    <li><strong>{{trans "Email:"}}</strong> {{var customer.email}}</li>
    <li><strong>{{trans "Password:"}}</strong> <em>{{trans "Password you set when creating account"}}</em></li>
</ul>
<p style="margin: 20px 0 !important">
    {{trans
        'Forgot your account password? Click <a href="%reset_url">here</a> to reset it.'

        reset_url="$this.getUrl($store,'customer/account/createPassword/',[_query:[id:$customer.id,token:$customer.rp_token],_nosid:1])"
    |raw}}
</p>
<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var this.getUrl($store,'/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "START SHOPPING"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Welcome to %store_name" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_new_account_weltpixel');
                $template->save();
            }


//New Account Confirmed
            $tplCode = 'New Account Confirmed - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Welcome, your new account is ready ! To sign in to our site, use the credentials during checkout or on the My Account page. You will be able to checkout faster, check orders status, view past orders and more.';
                $tplVars = <<<'EOT'
{"var this.getUrl($store, 'customer/account/')":"Customer Account URL","var customer.email":"Customer Email","var customer.name":"Customer Name"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_account_action" customer=$customer area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$customer.name}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'You\'re in :)' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Your account is now active.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p style="margin: 20px 0 !important">
    {{trans
    'To sign in, use these credentials during checkout or when logging into <a href="%customer_url">your account</a>.'
    customer_url=$this.getUrl($store,'customer/account/',[_nosid:1])
    |raw}}
</p>
<ul>
    <li><strong>{{trans "Email:"}}</strong> {{var customer.email}}</li>
    <li><strong>{{trans "Password:"}}</strong> <em>{{trans "Password you set when creating account"}}</em></li>
</ul>
<p style="margin: 20px 0 !important">
    {{trans
        'Forgot your account password? Click <a href="%reset_url">here</a> to reset it.'

        reset_url="$this.getUrl($store,'customer/account/createPassword/',[_query:[id:$customer.id,token:$customer.rp_token],_nosid:1])"
    |raw}}
</p>
<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var this.getUrl($store,'/',[_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "START SHOPPING"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Welcome to %store_name" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_new_account_confirmed_weltpixel');
                $template->save();
            }


//New Account No Password
            $tplCode = 'New Account Without Password - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'To sign in to our site, use the credentials during checkout or on the My Account page. You will be able to checkout faster, check orders status, view past orders and more.';
                $tplVars = <<<'EOT'
{ "var this.getUrl($store, 'customer/account/')":"Customer Account URL", "var customer.email":"Customer Email", "var customer.name":"Customer Name" }
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_account_action" customer=$customer area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$customer.name}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'You\'re in :)' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Your account is now active.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p style="margin: 20px 0 !important">
    {{trans
        'To sign in to our site and set a password, click on the <a href="%create_password_url">link</a>:'

        create_password_url="$this.getUrl($store,'customer/account/createPassword/',[_query:[id:$customer.id,token:$customer.rp_token],_nosid:1])"
    |raw}}
</p>
<ul>
    <li><strong>{{trans "Email:"}}</strong> {{var customer.email}}</li>
</ul>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Welcome to %store_name" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_new_account_no_password_weltpixel');
                $template->save();
            }

//New Account Confirmation
            $tplCode = 'New Account Confirmation Key - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'To sign in to our site, use the credentials during checkout or on the My Account page. You will be able to checkout faster, check orders status, view past orders and more.';
                $tplVars = <<<'EOT'
{"var this.getUrl($store, 'customer/account/')":"Customer Account URL","var customer.email":"Customer Email","var customer.name":"Customer Name"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_account_action" customer=$customer area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$customer.name}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'You\'re almost in :)' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Your need to confirm your email.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>


<p style="margin: 20px 0 !important">{{trans "You must confirm your %customer_email email before you can sign in (link is only valid once):" customer_email=$customer.email}}</p>

<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var this.getUrl($store,'customer/account/confirm/',[_query:[id:$customer.id,key:$customer.confirmation,back_url:$back_url],_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "CONFIRM YOUR ACCOUNT"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Please confirm your %store_name account" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_new_account_confirmation_weltpixel');
                $template->save();
            }


//Password Reminder
            $tplCode = 'Remind Password - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Do you want to change your password? Ok, we’re cool with that! If you did not make this request, you can ignore this email and your password will remain the same.';
                $tplVars = <<<'EOT'
{"var this.getUrl(store, 'customer/account/')":"Customer Account URL","var customer.name":"Customer Name"}
EOT;

                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_action" customer=$customer area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hi %name," name=$customer.name}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Do you want to change your password?' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Okay, we’re cool with that.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p style="margin: 20px 0 !important"> {{trans "If you requested this change, set a new password here:"}}</p>

<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var this.getUrl($store,'customer/account/createPassword',[_query:[id:$customer.id,token:$customer.rp_token],_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "SET IT!"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p>{{trans "If you did not make this request, you can ignore this email and your password will remain the same."}}</p>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Reset your %store_name password" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_new_pass_weltpixel');
                $template->save();
            }

//Reset Password - Forgot Email template - Forgot Password
            $tplCode = 'Reset Password - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Do you want to change your password? Ok, we’re cool with that! If you did not make this request, you can ignore this email and your password will remain the same.';
                $tplVars = '{"var customer.name":"Customer Name"}';
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_action" customer=$customer area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "SHHH ..."}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Passwords are hard to remember!' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Totally get it.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p class="greeting" style="margin-top: 35px">{{trans "Hello,"}}</p>
<br>

<p>
    {{trans "We have received a request to change the following information associated with your account at %store_name: password." store_name=$store.getFrontendName()}}
    {{trans 'If you have not authorized this action, please contact us immediately at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}{{depend store_phone}} {{trans 'or call us at <a href="tel:%store_phone">%store_phone</a>' store_phone=$store_phone |raw}}{{/depend}}.
</p>
<br>

<p>{{trans "Thanks,<br>%store_name" store_name=$store.getFrontendName() |raw}}</p>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Reset your %store_name password" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_password_reset_confirmation_weltpixel');
                $template->save();
            }


//Change Email
            $tplCode = 'Change Email - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Do you want to change your email address associated with your account? Ok, we’re cool with that! If you have not authorized this action, please contact us immediately.';
                $tplVars = '{}';
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hello,"}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Do you want to change your email address?' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Okay, we’re cool with that.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>


<p style="margin: 20px 0 !important">
    {{trans "We have received a request to change the following information associated with your account at %store_name: email." store_name=$store.getFrontendName()}}
    {{trans 'If you have not authorized this action, please contact us immediately at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}
</p>


{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Your %store_name email has been changed" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_change_email_weltpixel');
                $template->save();
            }


//Change Email and Password
            $tplCode = 'Change Email And Password - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Do you want to change your email address or password associated with your account? Ok, we’re cool with that! If you have not authorized this action, please contact us immediately.';
                $tplVars = '{}';
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hello,"}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Do you want to change your email/password?' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Okay, we’re cool with that.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p style="margin: 20px 0 !important">
    {{trans "We have received a request to change the following information associated with your account at %store_name: email, password." store_name=$store.getFrontendName()}}
    {{trans 'If you have not authorized this action, please contact us immediately at <a href="mailto:%store_email">%store_email</a>' store_email=$store_email |raw}}.
</p>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Your %store_name email and password has been changed" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_change_email_password_weltpixel');
                $template->save();
            }


//Newsletter Confirmation
            $tplCode = 'Newsletter Confirmation - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Hello, nice to meet you! Thank you for subscribing to our newsletter! To begin receiving the newsletter, you must first confirm your subscription by clinking the link in the email.';
                $tplVars = '{"var customer.name":"Customer Name","var subscriber.getConfirmationLink()":"Subscriber Confirmation URL"}';
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="subscription_confirmation_action" area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hello,"}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Nice to meet you :)'}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Thank you for subscribing to our newsletter.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p style="margin: 20px 0 !important">{{trans "To begin receiving the newsletter, you must first confirm your subscription by clicking on the following link:"}}</p>

<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var subscriber.getConfirmationLink()}}" style="font-weight: bold">{{trans 'Yes, I do!'}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<EOT
{{trans "Newsletter subscription confirmation"}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('newsletter_subscription_confirm_weltpixel');
                $template->save();
            }

//Wishlist Share
            $tplCode = 'Wishlist Share - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'The sender wants to share this Wish List with somebody special like you! To begin see what found and wants to share with you click on the button below.';
                $tplVars = '{"var customerName":"Customer Name","var viewOnSiteLink":"View Wish List URL","var items|raw":"Wish List Items","var message|raw":"Wish List Message"}';
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="wishlist_sharing_action" area="frontend"}}
<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "Hello, %customer_name wants to share this" customer_name=$customerName}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Wish List'}}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'with somebody special like you!'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p style="margin: 25px 0 !important">{{trans "To begin see what %customer_name found and wants to share with you click on the button below :)" customer_name=$customerName }}</p>

<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var viewOnSiteLink}}" style="font-weight: bold">{{trans "View all items"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{depend message}}
<table class="message-info">
    <tr>
        <td>
            <h3>{{trans "Message from %customer_name:" customer_name=$customerName}}</h3>
            {{var message|raw}}
        </td>
    </tr>
</table>
<br />
{{/depend}}

{{var items|raw}}
<br/>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Take a look at %customer_name's Wish List" customer_name=$customerName}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('wishlist_share_notification_weltpixel');
                $template->save();
            }
        }

        if (version_compare($context->getVersion(), "1.0.5", "<")) {

            $templateType = TemplateTypesInterface::TYPE_HTML;
            $tplCode = 'Forgot Password - WeltPixel';
            if(!$this->_templateExist($tplCode)) {
                $tplPreheader = 'Forgot your account password?';
                $tplVars = <<<'EOT'
{"var this.getUrl(store, 'customer/account/')":"Customer Account URL","var customer.name":"Customer Name"}
EOT;
                $tplText = <<<'EOT'
{{layout handle="preheader_section" area="frontend"}}
{{template config_path="design/email/header_template"}}
{{layout handle="customer_action" customer=$customer area="frontend"}}

<table align="center" style="background-color:#000; text-align:center; width: 660px">
    <tbody>
    <tr>
        <td class="dark"  style="padding-bottom:8px; padding-top:5px; background-color:#000">
            <h3 style="text-align: center; text-transform: uppercase;">
                {{trans "SHHH ..."}}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:0px; background-color:#000">
            <h1 style="text-align: center; margin: 0 !important">
                {{trans 'Passwords are hard to remember!' }}
            </h1>
        </td>
    </tr>
    <tr>
        <td class="dark" align="center" style="padding-bottom:8px; background-color:#000">
            <h3 style="text-align: center;">
                {{trans 'Totally get it.'}}
            </h3>
        </td>
    </tr>
    </tbody>
</table>

<p class="greeting" style="margin-top: 35px">{{trans "Hello,"}}</p>
<br>
<p style="margin: 20px 0 !important"> {{trans "If you requested this change, set a new password here:"}}</p>

<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
                <tr>
                    <td align="center" style="padding: 8px 0 !important">
                        <a href="{{var this.getUrl($store,'customer/account/createPassword',[_query:[id:$customer.id,token:$customer.rp_token],_nosid:1])}}" target="_blank" style="font-weight: bold">{{trans "SET IT!"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p>{{trans "If you did not make this request, you can ignore this email and your password will remain the same."}}</p>

{{template config_path="design/email/footer_template"}}
EOT;
                $tplSubject = <<<'EOT'
{{trans "Reset your %store_name password" store_name=$store.getFrontendName()}}
EOT;
                $template = $this->templateFactory->create();
                $template->setTemplateCode($tplCode);
                $template->setTemplateText($tplText);
                $template->setTemplatePreheader($tplPreheader);
                $template->setOrigTemplateVariables($tplVars);
                $template->setTemplateType($templateType);
                $template->setTemplateStyles('');
                $template->setTemplateSubject($tplSubject);
                $template->setOrigTemplateCode('customer_password_reset_confirmation_weltpixel');
                $template->save();
            }
        }

        if (version_compare($context->getVersion(), "1.0.6", "<")) {
            $collection = $this->_collectionFactory->create();
            foreach($collection as $tpl) {
                $templateCode = $tpl->getOrigTemplateCode();
                $newText = $templateText = $tpl->getTemplateText();
                $text = '{{layout handle="preheader_section" area="frontend"}}';
                $template = $this->_template->load($tpl->getTemplateId());
                if($templateCode == 'design_email_header_template' || $templateCode == 'design_email_footer_template') {
                    $searchStr = '{{inlinecss file="css/email-inline.css"}}';
                    $pos = strpos($templateText, $searchStr);
                    if ($pos !== false) {
                        $newText = substr_replace($templateText, $text, $pos, 0);
                    }
                } else {
                    $newText = str_replace($text,'', $templateText);
                }

                $template->setTemplateText($newText);
                $template->save();
            }
        }

        if (version_compare($context->getVersion(), "1.0.7", "<")) {
            $collection = $this->_collectionFactory->create();
            foreach($collection as $tpl) {
                $templateCode = $tpl->getOrigTemplateCode();
                $newText = $templateText = $tpl->getTemplateText();
                $text = '{{layout handle="preheader_section" area="frontend"}}';
                $template = $this->_template->load($tpl->getTemplateId());
                $newText = str_replace($text,'', $templateText);
                if($templateCode != 'design_email_header_template' || $templateCode != 'design_email_footer_template') {
                    $searchStr = '{{template config_path="design/email/header_template"}}';
                    $pos = strpos($newText, $searchStr);
                    if ($pos !== false) {
                        $newText = substr_replace($newText, $text, $pos + strlen($searchStr), 0);
                    }
                }

                $template->setTemplateText($newText);
                $template->save();
            }
        }

        if (version_compare($context->getVersion(), "1.0.8", "<")) {
            $collection = $this->_collectionFactory->create();
            foreach($collection as $tpl) {
                $templateCode = $tpl->getOrigTemplateCode();
                if($templateCode == 'new_shipment_weltpixel' || $templateCode == 'new_shipment_guest_weltpixel') {
                    $newText = $templateText = $tpl->getTemplateText();
                    $template = $this->_template->load($tpl->getTemplateId());

                    $text = 'Magento_Sales::email/shipment/track.phtml';
                    $modifText = 'WeltPixel_EnhancedEmail::email/shipment/track.phtml';
                    $newText = str_replace($text,$modifText, $templateText);
                    $template->setTemplateText($newText);
                    $template->save();

                }
            }
        }

        if (version_compare($context->getVersion(), "1.0.9", "<")) {
            $collection = $this->_collectionFactory->create();
            foreach($collection as $tpl) {
                $templateCode = $tpl->getOrigTemplateCode();
                $newText = $templateText = $tpl->getTemplateText();
                if($templateCode == 'design_email_header_template') {

                    $text = '{{layout handle="menu_line" area="frontend"}}';
                    $template = $this->_template->load($tpl->getTemplateId());
                    $newText = str_replace($text,'', $templateText);

                } elseif($templateCode != 'design_email_footer_template' || $templateCode != 'design_email_header_template') {
                    $searchStr = '{{layout handle="preheader_section" area="frontend"}}';
                    $text = '{{layout handle="menu_line" area="frontend"}}';
                    $pos = strpos($newText, $searchStr);
                    $posMenu = strpos($newText, $text);

                    if ($pos !== false && $posMenu === false) {
                        $newText = substr_replace($newText, $text, $pos + strlen($searchStr), 0);
                    }

                    $template = $this->_template->load($tpl->getTemplateId());

                }

                $template->setTemplateText($newText);
                $template->save();
            }
        }

    }

    /**
     * @return array
     */
    protected function _getTemplatesArr() {
        $collection = $this->_collectionFactory->create();
        $templates = [];
        foreach($collection as $template) {
            $templates[] = [
                'template_code' => $template->getTemplateCode(),
                'orig_template_code' => $template->getOrigTemplateCode()
            ];
        }

        return $templates;
    }

    /**
     * @param $templateCode
     * @return false|int|string
     */
    protected function _templateExist($templateCode) {
        return array_search($templateCode, array_column($this->_templates, 'template_code'));

    }

}