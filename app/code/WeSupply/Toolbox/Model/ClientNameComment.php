<?php
namespace WeSupply\Toolbox\Model;

class ClientNameComment implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \WeSupply\Toolbox\Helper\Data
     */
    public $helper;


    /**
     * ClientNameComment constructor.
     * @param \WeSupply\Toolbox\Helper\Data $helper
     */
    public function __construct(
        \WeSupply\Toolbox\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $clientName = $this->helper->getClientName();
        if($clientName){
            return '<strong>'.$clientName.'</strong><br/>Copy this Client Name into your WeSupply account';
        }else{
            return 'Please fill in and save <strong>WeSupply SubDomain</strong> field from <strong>Step 1 - Define your WeSupply SubDomain</strong> Configuration tab, to receive your Client Name!';
        }

    }

}