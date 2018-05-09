<?php
/* Form to update and insert players in system */
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'validators' . DS . 'FirstCharacterIsLetterValidator.php';

class NewPlayerForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('new-player');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
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
		$val3 = new Zend_Validate_EmailAddress();
		$val3->setOptions(array('domain'=> false));
		$EmailAddressMessages = array(
		Zend_Validate_EmailAddress::LENGTH_EXCEEDED => $translate->_("Email length exceeded"),
		Zend_Validate_EmailAddress::INVALID => $translate->_("Email invalid"),
		Zend_Validate_EmailAddress::INVALID_FORMAT => $translate->_("Email format invalid"));
		$val3->setMessages($EmailAddressMessages);
        $val5 = new FirstCharacterLetterValidator();
        $val5->setMessages(
            array(
                FirstCharacterLetterValidator::MSG_FIRST_LETTER_IS_CHARACTER => $translate->_("In '%value%' value first character must start with a letter"),
            )
        );
		$identical_validator = new Zend_Validate_Identical(Zend_Controller_Front::getInstance()->getRequest()->getParam('PASSWORD'));
		$identical_validator->setMessage($translate->_("Values not match passwords"));
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
		$selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSelectElement.phtml')));
		$dateDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptDateTimeSingleWithLabelElement.phtml')));

        $i = 1;
		
		$player_name = new Zend_Form_Element_Text("NAME");
		$player_name->setOrder($i);
		$player_name->setLabel($translate->_("Player_Name"))
            ->setRequired(true)
            ->addFilter("StripTags")
            ->addFilter("StringTrim")
            ->addValidator($val2,true)
            ->addValidator($val1)
            ->addValidator($val5)
            ->setE;
		$player_name->setDecorators($textDecorator);
        $player_name->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$password = new Zend_Form_Element_Password("PASSWORD");
		$password->setOrder($i);
		$password->setLabel($translate->_("Player Password"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true)->addValidator($val1)->setE;
		$password->setDecorators($textDecorator);
        $password->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$conf_password = new Zend_Form_Element_Password("CONFIRM_PASSWORD");
		$conf_password->setOrder($i);
		$conf_password->setLabel($translate->_("Player ConfirmPassword"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true)->addValidator($val1)->addValidator($identical_validator)->setE;
		$conf_password->setDecorators($textDecorator);
        $conf_password->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$email = new Zend_Form_Element_Text("EMAIL");
		$email->setOrder($i);
		$email->setLabel($translate->_("Player Email"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val3)->setE;
		$email->setDecorators($textDecorator);
        $email->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$first_name = new Zend_Form_Element_Text("FIRST_NAME");
		$first_name->setOrder($i);
		$first_name->setLabel($translate->_("Player First Name"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val1)->setE;
		$first_name->setDecorators($textDecorator);
        $first_name->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$last_name = new Zend_Form_Element_Text("LAST_NAME");
		$last_name->setOrder($i);
		$last_name->setLabel($translate->_("Player Last Name"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val1)->setE;
		$last_name->setDecorators($textDecorator);
        $last_name->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$birthday = new Zend_Form_Element_Text("BIRTHDATE");
		$birthday->setOrder($i);
		$birthday->setLabel($translate->_("Player Birthday"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$birthday->setDecorators($dateDecorator);
        $birthday->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;

        $phone = new Zend_Form_Element_Text("PHONE");
		$phone->setOrder($i);
		$phone->setLabel($translate->_("Player Phone"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$phone->setDecorators($textDecorator);
        $phone->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;

        $address = new Zend_Form_Element_Text("ADDRESS");
		$address->setOrder($i);
		$address->setLabel($translate->_("Player Address"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$address->setDecorators($textDecorator);
        $address->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$zip = new Zend_Form_Element_Text("ZIP");
		$zip->setOrder($i);
		$zip->setLabel($translate->_("Player Zip"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$zip->setDecorators($textDecorator);
        $zip->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;

		$city = new Zend_Form_Element_Text("CITY");
		$city->setOrder($i);
		$city->setLabel($translate->_("Player City"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$city->setDecorators($textDecorator);
        $city->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;
		
		$country = new Zend_Form_Element_Select("COUNTRY");
		$country->setOrder($i);
		$country->setLabel($translate->_("Player Country"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->setE;
		require_once MODELS_DIR . DS . 'CurrencyModel.php';
		$countries = CurrencyModel::getCountries($session_id);
		$country->addMultiOption('', $translate->_("SelectCountry"));
		foreach($countries['cursor'] as $c) {
            $country->addMultiOption($c['id'], StringHelper::filterCountry($c['name']));
        }
		$country->setDecorators($selectDecorator);
		$country->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1'
			)
		);
        $i++;

        $currencyHidden = new Zend_Form_Element_Hidden("CURRENCY_HIDDEN");
		$multi_currency = $_SESSION["auth_space"]["session"]["multi_currency"];
		$default_currency = $_SESSION["auth_space"]["session"]["currency"];
		if(!is_null($_SESSION["auth_space"]["session"]["multi_currency"])){
			$currency = new Zend_Form_Element_Select("CURRENCY");
			$currency->setOrder($i);
			$currency->setAttribs(
				array(
					'tabindex'=>$i,
                    'class'=> 'form-control margin-1'
				)
			);
			$currency->setLabel($translate->_("Currency"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->setE;
			$currency->clearMultiOptions();
			$currency->addMultiOption('', $translate->_("SelectCurrency"));
			$currencies = $_SESSION['auth_space']['session']['currencies'];
			//take currency from logged in affiliate and set it as default
			foreach($currencies as $c) {
                if(trim($c['currency']) != "") {
                    $currency->addMultiOption($c['currency'], $c['currency']);
                }
            }
			$currency->setValue($default_currency);
			$currencyHidden->setValue($default_currency);
			$currency->setDecorators($selectDecorator);
		}else{
			$currency = new Zend_Form_Element_Text("CURRENCY");
			$currency->setOrder($i);
			$currency->setAttribs(
				array(
                    'disabled'=>'',
					'tabindex'=>$i,
                    'class'=> 'form-control margin-1'
				)
			);
			$currency->setLabel($translate->_("Currency"))->setE;
			$currency->setDecorators($textDecorator);
			$currency->setValue($default_currency);
			$currencyHidden->setValue($default_currency);
		}
        $i++;

		$banned = new Zend_Form_Element_Select('BANNED');
        $banned->setLabel($translate->_('Player Banned'))->addFilter('StripTags')->addFilter('StringTrim')->addValidator($val2,true)->setE;
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
		
		$type = new Zend_Form_Element_Hidden("PLAYER_TYPE");
		require_once MODELS_DIR . DS . 'PlayersModel.php';
		$type_id = PlayersModel::getPlayerTypeID($session_id, ROLA_PL_PC_PLAYER_INTERNET);
        $type_id = $type_id['player_type_id'];
		$type->setValue($type_id);
				

		$submit = new Zend_Form_Element_Submit("SAVE");
		$submit->setOrder($i);
		$submit->setAttrib("tabindex", $i);
		$submit->setLabel($translate->_("Save"));
		$submit->setValue($translate->_("Save"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $submit->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-primary btn-block'
            )
        );
		
		$cancel = new Zend_Form_Element_Submit("CANCEL");
		$cancel->setOrder($i);
		$cancel->setAttrib("tabindex", $i);
		$cancel->setLabel($translate->_("Cancel"));
		$cancel->setValue($translate->_("Cancel"));
		$cancel->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $cancel->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-default btn-block'
            )
        );
		
		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(600);
		
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/players/NewPlayerViewScript.phtml'))));
		$this->addElements(array($player_name, $password, $conf_password, $email, $first_name, $last_name, $birthday, $zip, $phone, $city,
            $address, $country, $currency, $currencyHidden, $banned, $submit, $cancel, $type, $csrf));
	}
}