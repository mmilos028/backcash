<?php
/* Form to reset password for players */
class ResetPasswordForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('reset-player-password');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
        $translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$val1 = new Zend_Validate_StringLength(array('min' => 4, 'max' => 15));
		$val1->setMessages(array(
		Zend_Validate_StringLength::TOO_SHORT => $translate->_("The string is too short"),
		Zend_Validate_StringLength::TOO_LONG  => $translate->_('The string is too long')));
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(
		array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		$identical_validator = new Zend_Validate_Identical(Zend_Controller_Front::getInstance()->getRequest()->getParam('PASSWORD'));
		$identical_validator->setMessage($translate->_("Values not match passwords"));
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
		$plainTextDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptPlainTextElement.phtml')));
        $i = 1;
		
		$user_name = new Zend_Form_Element_Text("NAME");
		$user_name->setOrder($i);
		$user_name->setLabel($translate->_("Affiliate Name"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true)->addValidator($val1)->setE;
        $user_name
            ->setAttrib("tabindex", $i)
            ->setAttrib("disabled", true)
            ->setAttrib("autofocus", "")
            ->setAttrib("class", "form-control margin-1");
		$user_name->setDecorators($textDecorator);
        $i++;
		
		$player_password = new Zend_Form_Element_Password("PASSWORD");
		$player_password->setOrder($i);
		$player_password->setLabel($translate->_("Player Password"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator(new Zend_Validate_Alnum(),true)->addValidator($val2,true)->addValidator($val1)->setE;
		$player_password->setDecorators($textDecorator);
        $player_password->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;
				
		$password_confirm = new Zend_Form_Element_Password("CONFIRM_NEW_PASSWORD");
		$password_confirm->setOrder($i);
		$password_confirm->setLabel($translate->_("Player ConfirmPassword"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator(new Zend_Validate_Alnum(),true)->addValidator($val2,true)->addValidator($val1)->addValidator($identical_validator)->setE;
		$password_confirm->setDecorators($textDecorator);
        $password_confirm->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;
		
		$submit = new Zend_Form_Element_Submit("SAVE");
		$submit->setOrder($i);
		$submit->setLabel($translate->_("Save changes"));
		$submit->setValue($translate->_("Save changes"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $submit->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-primary btn-block'
            )
        );
        $i++;
		
		$cancel = new Zend_Form_Element_Submit("CANCEL");
		$cancel->setOrder($i);
		$cancel->setLabel($translate->_("Cancel"));
		$cancel->setValue($translate->_("Cancel"));
		$cancel->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $cancel->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-default btn-block'
            )
        );
        $i++;
		
		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(600);
    	
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/players/ResetPasswordViewScript.phtml'))));
    	$this->addElements(array($user_name, $player_password, $password_confirm, $submit, $cancel, $csrf));
	}
}