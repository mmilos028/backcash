<?php
/* Form for credit transfer from cashier to player - terminals */
class TransferCreditFromCashierToPlayerTerminalForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('transfer-credit-from-cashier-to-player-terminal');
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
            ->setAttrib("disabled",true)
            ->setAttrib("class", "form-control bold-text margin-1 plaintext_input_text_field right-text-align")
            ;
		$player_name->setDecorators($textDecorator);
		
		$playerCreditStatus = new Zend_Form_Element_Text("PLAYER_CREDIT_STATUS");
		$playerCreditStatus->setLabel($translate->_("Player_CreditStatus"))->addFilter("StripTags")->addFilter("StringTrim")->setAttrib("disabled", false);
		$playerCreditStatus
            ->setAttrib("class", "form-control margin-1 bold-text plaintext_input_text_field right-text-align")
        ;
		$playerCreditStatus->setDecorators($textDecorator);
		
		$affiliateName = new Zend_Form_Element_Text("AFF_NAME");
		$affiliateName->setLabel($translate->_("Player_Aff"))->addFilter("StripTags")->addFilter("StringTrim")
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control bold-text margin-1 plaintext_input_text_field right-text-align")
        ;
		$affiliateName->setDecorators($textDecorator);
		
		$affiliateCreditStatus = new Zend_Form_Element_Text("AFF_CREDIT_STATUS");
		$affiliateCreditStatus->setLabel($translate->_("Limit"));
		$affiliateCreditStatus
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control margin-1 bold-text plaintext_input_text_field right-text-align")
        ;
		$affiliateCreditStatus->setDecorators($textDecorator);
				
		$currency = new Zend_Form_Element_Text("PLAYER_CURRENCY");
		$currency->setLabel($translate->_("Currency"));
		$currency->setDecorators($textDecorator);
        $currency
                ->setAttrib("disabled", true)
                ->setAttrib("class", "form-control bold-text plaintext_input_text_field margin-1 right-text-align");
		
		$affiliateAmount = new Zend_Form_Element_Text("TRANSFER_AMOUNT");
		$affiliateAmount->setLabel($translate->_("Deposit Amount"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")
            ->addValidator($val2,true)
            //->addValidator($float_val)
        ;
		$affiliateAmount
            ->setAttrib("class", "form-control margin-1 bold-text darkgray_input_text_field right-text-align")
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
                'class' => 'btn btn-lg btn-primary btn-block'
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
		
		$cancel = new Zend_Form_Element_Submit("CANCEL");
		$cancel->setValue($translate->_("Previous page"));
		$cancel->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$cancel->setAttribs(
            array(
                'class' => 'btn btn-lg btn-warning btn-block'
            )
        );
		
		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(300);
		
		$enabled = new Zend_Form_Element_Hidden("ENABLED");
		$affiliateId = new Zend_Form_Element_Hidden("AFF_ID");
		$possibleAmmount = new Zend_Form_Element_Hidden("POSSIBLE_AMOUNT");
		$affCreditStatusHid = new Zend_Form_Element_Hidden("AFF_CREDIT_STATUS_HIDDEN");
		$playerCreditStatusHid = new Zend_Form_Element_Hidden("PLAYER_CREDIT_STATUS_HIDDEN");
        $transferAmountHidden = new Zend_Form_Element_Hidden("TRANSFER_AMOUNT_HIDDEN");
		
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/financial_transaction/for_touchscreen/CreditTransferFromCashierToPlayerTerminalViewScript.phtml'))));
		$this->addElements(array ($player_name, $playerCreditStatus, $affiliateName, $currency, $affiliateCreditStatus, $affiliateAmount,
            $submit, $cancel, $clear,
            $csrf, $enabled, $affiliateId, $possibleAmmount, $affCreditStatusHid, $playerCreditStatusHid, $transferAmountHidden));
	}
}