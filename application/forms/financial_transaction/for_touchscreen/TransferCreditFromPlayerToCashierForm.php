<?php
/* Form to transfer credits from player to cashiers */
class TransferCreditFromPlayerToCashierForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('transfer-credit-from-player-to-cashier');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
		$val1 = new Zend_Validate_StringLength(array('min' => 2, 'max' => 50));
		$translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptTextElement.phtml')));
		$plainTextDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptPlainTextElement.phtml')));
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		$float_val = new Zend_Validate_Float();
		$float_val->setMessages(array(
			Zend_Validate_Float::INVALID => $translate->_("FLOAT_NOT_VALID"),
			Zend_Validate_Float::NOT_FLOAT => $translate->_("NOT_FLOAT")));
		
		$player_name = new Zend_Form_Element_Text("DIRECT_PLAYER_NAME");
		$player_name->setLabel($translate->_("Player_Name"))->addFilter("StripTags")->addFilter("StringTrim");
		$player_name
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control bold-text margin-1 plaintext_input_text_field right-text-align")
            ;
		$player_name->setDecorators($textDecorator);
		
		$playerCreditStatus = new Zend_Form_Element_Text("PLAYER_CREDIT_STATUS");
		$playerCreditStatus->setLabel($translate->_("Player_CreditStatus"))->addFilter("StripTags")->addFilter("StringTrim")->setAttrib("disabled", false);
		$playerCreditStatus->setAttrib("disabled", true)
            ->setAttrib("class", "form-control margin-1 bold-text right-text-align plaintext_input_text_field")
            ;
		$playerCreditStatus->setDecorators($textDecorator);
		
		$affiliateName = new Zend_Form_Element_Text("AFF_NAME");
		$affiliateName->setLabel($translate->_("Player_Aff"))->addFilter("StripTags")->addFilter("StringTrim")
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control bold-text margin-1 plaintext_input_text_field")
            ;
		$affiliateName->setDecorators($textDecorator);
		
		$affiliateCreditStatus = new Zend_Form_Element_Text("AFF_CREDIT_STATUS");
		$affiliateCreditStatus->setLabel($translate->_("Affiliate_CreditStatus"));
		$affiliateCreditStatus
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control margin-1 bold-text right-text-align plaintext_input_text_field")
        ;
		$affiliateCreditStatus->setDecorators($textDecorator);
				
		$currency = new Zend_Form_Element_Text("PLAYER_CURRENCY");
		$currency->setLabel($translate->_("Currency"));
        $currency->setAttribs(
            array(
                'disabled' => true,
                'style' => 'font-size: 14px',
                'class' => 'form-control bold-text plaintext_input_text_field'
            )
        );
		$currency->setDecorators($textDecorator);
		
		$affiliateAmount = new Zend_Form_Element_Text("TRANSFER_AMOUNT");
		$affiliateAmount->setLabel($translate->_("Withdraw Amount"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")
            ->addValidator($val2,true)
            //->addValidator($float_val)
        ;
		$affiliateAmount
            ->setAttrib("class", "form-control margin-1 bold-text right-text-align darkgray_input_text_field")
            ->setAttrib("autofocus", "")
            ->setAttrib("readonly", "")
            ->setAttrib("step", "0.01")
        ;
		$affiliateAmount->setDecorators($textDecorator);
		
		$submit = new Zend_Form_Element_Submit("SUBMIT");
		$submit->setValue($translate->_("Save"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$submit->setAttribs(
            array(
                'class' => 'btn btn-lg btn-danger btn-block'
            )
        );

        $submit_payout_all = new Zend_Form_Element_Submit("SUBMIT_PAYOUT_ALL");
		$submit_payout_all->setValue($translate->_("Payout All"));
		$submit_payout_all->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$submit_payout_all->setAttribs(
            array(
                'class' => 'btn btn-lg btn-danger btn-block',
                'onClick' => 'setTransferAmountMaxAndSubmit()'

            )
        );
		
		$cancel = new Zend_Form_Element_Submit("CANCEL");
		$cancel->setValue($translate->_("Previous page"));
		$cancel->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$cancel->setAttribs(
            array(
                'class' => 'btn btn-lg btn-warning btn-block'
            )
        );

        $clear = new Zend_Form_Element_Button("CLEAR");
		$clear->setValue($translate->_("Clear"));
		$clear->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$clear->setAttribs(
            array(
                'class' => 'btn btn-lg btn-danger btn-block'
            )
        );
		
		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(60);
		
		$enabled = new Zend_Form_Element_Hidden("ENABLED");
		$affiliateId = new Zend_Form_Element_Hidden("AFF_ID");
		$possible_ammount = new Zend_Form_Element_Hidden("POSSIBLE_AMOUNT");
		$affCreditStatusHid = new Zend_Form_Element_Hidden("AFF_CREDIT_STATUS_HIDDEN");
		$playerCreditStatusHid = new Zend_Form_Element_Hidden("PLAYER_CREDIT_STATUS_HIDDEN");
        $transferAmountHidden = new Zend_Form_Element_Hidden("TRANSFER_AMOUNT_HIDDEN");
		
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/financial_transaction/for_touchscreen/CreditTransferPayoutPlayerToCashierViewScript.phtml'))));
		$this->addElements(array($player_name, $playerCreditStatus, $affiliateName, $currency, $affiliateCreditStatus, $affiliateAmount,
            $submit, $submit_payout_all, $cancel, $clear,
            $csrf, $enabled, $affiliateId, $possible_ammount, $affCreditStatusHid, $playerCreditStatusHid, $transferAmountHidden));
	}
}