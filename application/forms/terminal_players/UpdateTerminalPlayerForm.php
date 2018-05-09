<?php
/* Form to update or add new terminal players */
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'validators' . DS . 'FirstCharacterIsLetterValidator.php';
class UpdateTerminalPlayerForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('update_terminal_player');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance ();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
        $translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);        
		$val1 = new Zend_Validate_StringLength(array('min' => 2, 'max' => 50));
		$val1->setMessages( array(
		Zend_Validate_StringLength::TOO_SHORT => $translate->_("The string is too short"),
		Zend_Validate_StringLength::TOO_LONG  => $translate->_('The string is too long')));
		
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		$int_val = new Zend_Validate_Int();
		$int_val->setMessages(array(
		Zend_Validate_Int::INVALID => $translate->_("INT_NOT_VALID"),
		Zend_Validate_Int::NOT_INT  => $translate->_('NOT_INT')));
		$identical_validator = new Zend_Validate_Identical(Zend_Controller_Front::getInstance()->getRequest()->getParam('TERMINAL_PLAYER_PASSWORD'));
		$identical_validator->setMessage($translate->_("Values not match passwords"));
		$val4 = new Zend_Validate_StringLength(array('min' => 6, 'max' => 9));
		$val4->setMessages( array(
		Zend_Validate_StringLength::TOO_SHORT => $translate->_("The string is too short"),
		Zend_Validate_StringLength::TOO_LONG  => $translate->_('The string is too long')));
        $val5 = new FirstCharacterLetterValidator();
        $val5->setMessages(
            array(
                FirstCharacterLetterValidator::MSG_FIRST_LETTER_IS_CHARACTER => $translate->_("In '%value%' value first character must start with a letter"),
            )
        );
		$identical_validator2 = new Zend_Validate_Identical(Zend_Controller_Front::getInstance()->getRequest()->getParam('TERMINAL_PLAYER_ACCESS_CODE'));
		$identical_validator2->setMessage($translate->_("Access Code not match Confirm Access Code"));
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));

        $selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSelectElement.phtml')));

        $i = 1;

		$name = new Zend_Form_Element_Text("TERMINAL_PLAYER_NAME");
		$name->setOrder($i);
		$name->setLabel($translate->_("TerminalPlayer_Name"))
            ->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter("StringTrim")
            ->addValidator($val2,true)
            ->addValidator($val1)
            ->addValidator($val5);
        $name->setDecorators($textDecorator);
		$name->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1',
                'readonly'=>'readonly'
			)
		);
        $i++;

		$type = new Zend_Form_Element_Hidden("TERMINAL_PLAYER_TYPE");
		
		require_once MODELS_DIR . DS . 'PlayersModel.php';
		$type_id_result = PlayersModel::getPlayerTypeID($session_id, ROLA_PL_TERMINAL_PLAYER);
        $type_id = $type_id_result['player_type_id'];
		$type->setValue($type_id);


		$terminal_type = new Zend_Form_Element_Select("TERMINAL_TYPE");
		$terminal_type->setOrder($i);
		$terminal_type->setLabel($translate->_("TerminalPlayer_Type"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true);
		$terminal_type->setDecorators($selectDecorator);
		$terminal_type->addMultiOption("", $translate->_("Select Terminal Type"));
		$terminal_type->addMultiOption("A", $translate->_("Auto login"));
		$terminal_type->addMultiOption("M", $translate->_("Manual login"));
		$terminal_type->addMultiOption("G", $translate->_("General Purpose"));
        $terminal_type->addMultiOption("T", $translate->_("Ticket Terminal"));
        $terminal_type->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$key_exit = new Zend_Form_Element_Select("TERMINAL_PLAYER_KEY_EXIT");
		$key_exit->setOrder($i);
		$key_exit->setDecorators($selectDecorator);
		$key_exit->setLabel($translate->_("ExitButton"))->addFilter("StripTags")->addFilter("StringTrim");
        $key_exit->addMultiOption("0", $translate->_("No"));
        $key_exit->addMultiOption("1", $translate->_("Yes"));
        $key_exit->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$enter_password = new Zend_Form_Element_Select("TERMINAL_PLAYER_ENTER_PASSWORD");
		$enter_password->setOrder($i);
		$enter_password->setDecorators($selectDecorator);
		$enter_password->setLabel($translate->_("EnterPassword"))->addFilter("StripTags")->addFilter("StringTrim");
        $enter_password->addMultiOption("0", $translate->_("No"));
        $enter_password->addMultiOption("1", $translate->_("Yes"));
        $enter_password->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;

		$multi_currency = $_SESSION["auth_space"]["session"]["multi_currency"];
		if(!is_null($_SESSION["auth_space"]["session"]["multi_currency"])){
			$currency = new Zend_Form_Element_Select("TERMINAL_PLAYER_CURRENCY");
			$currency->setOrder($i);
			$currency->setLabel($translate->_("Currency"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true);
			$currency->clearMultiOptions();
			$currency->addMultiOption('',$translate->_("SelectCurrency"));
			$currencies = $_SESSION['auth_space']['session']['currencies'];
			foreach($currencies as $cur) {
                if(trim($cur['currency']) != "") {
                    $currency->addMultiOption($cur['currency'], $cur['currency']);
                }
            }
			$currency->setValue($_SESSION['auth_space']['session']['currency']);
    		$currency->setDecorators($selectDecorator);
            $currency->setAttribs(
                array(
                    'tabindex'=>$i,
                    'class'=> 'form-control margin-1'
                )
            );
		}else{
			$currency = new Zend_Form_Element_Text("TERMINAL_PLAYER_CURRENCY");
			$currency->setOrder($i);
			$currency->setLabel($translate->_("Currency"));
			$currency->setValue($_SESSION["auth_space"]["session"]["currency"]);
			$currency->setDecorators($textDecorator);
            $currency->setAttribs(
                array(
                    'tabindex'=>$i,
                    'class'=> 'form-control margin-1',
                    'readonly' => 'readonly'
                )
            );
		}
		$currency->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->setE;
			
		$banned = new Zend_Form_Element_Select('TERMINAL_PLAYER_BANNED');
        $banned->setLabel($translate->_('Player Banned'))->addFilter('StripTags')->addFilter('StringTrim')->addValidator($val2,true);
        $banned->setValue(NO);
        $banned->setOrder($i);
        $banned->setDecorators($selectDecorator);
        $banned->addMultiOption(NO, $translate->_('No'));
        $banned->addMultiOption(YES, $translate->_('Yes'));
        $banned->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
			
		$access_code = new Zend_Form_Element_Number("TERMINAL_PLAYER_ACCESS_CODE");
		$access_code->setOrder($i);
		$access_code->setLabel($translate->_("TerminalPlayer_AccessCode"))->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true)->addValidator($val4)->addValidator($int_val);
		$access_code->setDecorators($textDecorator);
        $access_code->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1',
			)
		);
        $i++;
		
		$conf_access_code = new Zend_Form_Element_Number("TERMINAL_PLAYER_CONFIRMATION_ACCESS_CODE");
		$conf_access_code->setOrder($i);
		$conf_access_code->setLabel($translate->_("TerminalPlayer_ConfirmAccessCode"))->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true)->addValidator($val4,true)->addValidator($identical_validator2)->addValidator($int_val);
		$conf_access_code->setDecorators($textDecorator);
        $conf_access_code->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1',
			)
		);
        $i++;
		
		$aff_path = new Zend_Form_Element_Text("TERMINAL_PLAYER_LOCATION");
		$aff_path->setOrder($i);
		$aff_path->setDecorators($textDecorator);
        $aff_path->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1',
                'readonly'=>'readonly'
			)
		);
        $i++;
				
		$pin_code = new Zend_Form_Element_Text("TERMINAL_PLAYER_PIN_CODE");
		$pin_code->setOrder($i);
		$pin_code->setLabel($translate->_("Pin Code"));
		$pin_code->setDecorators($textDecorator);
        $pin_code->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1',
                'readonly'=>'readonly'
			)
		);
        $i++;
				
		$submit = new Zend_Form_Element_Submit("SAVE");
		$submit->setOrder($i);
		$submit->setAttrib('tabindex', $i);
		$submit->setLabel($translate->_("Save"));
		$submit->setValue($translate->_("Save"));
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
		$cancel->setAttrib('tabindex', $i);
		$cancel->setLabel($translate->_("Cancel"));
		$cancel->setValue($translate->_("Cancel"));
		$cancel->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $cancel->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-default btn-block'
            )
        );
		
		//hidden elements
		$terminal_type_hidden = new Zend_Form_Element_Hidden('TERMINAL_TYPE_HIDDEN');
		$exit_button_hidden = new Zend_Form_Element_Hidden('KEY_EXIT_HIDDEN');
		$enter_password_hidden = new Zend_Form_Element_Hidden('ENTER_PASSWORD_HIDDEN');
		$general_purpose_hidden = new Zend_Form_Element_Hidden('GENERAL_PURPOSE_HIDDEN');
		$banned_hidden = new Zend_Form_Element_Hidden('BANNED_HIDDEN');
		///
		$csrf = new Zend_Form_Element_Hash('csrf');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(600);
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/terminal_players/UpdateTerminalPlayerViewScript.phtml'))));
		$this->addElements(array($aff_path, $name, $banned, $currency, $submit, $cancel,
		$type, $csrf, $terminal_type, $access_code, $conf_access_code, $key_exit, $enter_password,/* $general_purpose,*/ $pin_code,
		
		$terminal_type_hidden, $exit_button_hidden, $enter_password_hidden, $general_purpose_hidden, $banned_hidden));
	}
}