<?php
/* Form for successfull credit transfer from affiliate to player or terminal */
class TransferCreditFromPlayerToCashierCompletedForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('transfer-credit-from-player-to-cashier-completed');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
		$val1 = new Zend_Validate_StringLength(array('min' => 2, 'max' => 50));
		$translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));

        $playerName = new Zend_Form_Element_Text("DIRECT_PLAYER_NAME");
		$playerName->setLabel($translate->_("Player_Name"))->addFilter("StripTags")->addFilter("StringTrim");
        $playerName
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control bold-text margin-1 black_input_text_field")
            ->setE;
		$playerName->setDecorators($textDecorator);

        $playerCreditStatus = new Zend_Form_Element_Text("PLAYER_CREDIT_STATUS");
		$playerCreditStatus->setLabel($translate->_("Player_NewCreditStatus"))->addFilter("StripTags")->addFilter("StringTrim");
		$playerCreditStatus
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control margin-1 bold-text right-text-align black_input_text_field")
            ->setE;
		$playerCreditStatus->setDecorators($textDecorator);

        $affiliateName = new Zend_Form_Element_Text("AFF_NAME");
		$affiliateName->setLabel($translate->_("Player_Aff"))->addFilter("StripTags")->addFilter("StringTrim")
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control bold-text margin-1 black_input_text_field")
            ->setE;
		$affiliateName->setDecorators($textDecorator);

        $affiliateCreditStatus = new Zend_Form_Element_Text("AFF_CREDIT_STATUS");
		$affiliateCreditStatus->setLabel($translate->_("Affiliate_NewCreditStatus"));
		$affiliateCreditStatus
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control margin-1 bold-text right-text-align black_input_text_field")
            ->setAttrib("step", "0.01")
        ;
		$affiliateCreditStatus->setDecorators($textDecorator);

        $submit = new Zend_Form_Element_Submit("SUBMIT");
		$submit->setLabel($translate->_("Done"))->setName("SUBMIT")->setRequired(true);
		$submit->setValue($translate->_("Done"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$submit->setAttribs(
            array(
                'class' => 'btn btn-lg btn-success btn-block'
            )
        );

        $csrf = new Zend_Form_Element_Hash("CSRF");
		$csrf->setIgnore(true);

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/financial_transaction/for_touchscreen/CreditTransferFromPlayerToCashierCompletedViewScript.phtml'))));
		$this->addElements ( array ($playerName, $playerCreditStatus, $affiliateName, $affiliateCreditStatus, $submit, $csrf ) );
	}
}