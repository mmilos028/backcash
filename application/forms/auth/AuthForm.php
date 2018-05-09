<?php
/* Login form for user authentification */
class AuthForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('auth-form');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$this->setAction($baseUrl . '/' . $lang . '/auth/login');
		$auth = Zend_Auth::getInstance();		
		$val1 = new Zend_Validate_StringLength(array('min' => 2, 'max' => 30));
		$translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$val1->setMessages( array(
		Zend_Validate_StringLength::TOO_SHORT => $translate->_("The string is too short"),
		Zend_Validate_StringLength::TOO_LONG  => $translate->_('The string is too long')));
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(
		array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));

        $selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSelectElement.phtml')));

        $i = 1;

        $language = new Zend_Form_Element_Select("language");
		$language->setOrder($i);
		$language->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true);
		$language->setDecorators($selectDecorator);
		$language->addMultiOption("en", "English");
		$language->addMultiOption("de", "Deutsch");
		$language->addMultiOption("se", "Svenska");
		$language->addMultiOption("dk", "Danske");
        $language->addMultiOption("it", "Italiano");
        $language->addMultiOption("ru", "Русский");
        $language->addMultiOption("pl", "Polski");
        $language->addMultiOption("cs", "Český");
        $language->addMultiOption("hr", "Hrvatski");
        $language->addMultiOption("rs", "Srpski");
        $language->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=> 'form-control margin-1 input-lg'
			)
		);
        $i++;

		$username = new Zend_Form_Element_Text("username");
		$username->setOrder($i);
		$username->setAttribs(
			array(
				'tabindex'=>$i,
				'class'=>'form-control input-lg',
				'placeholder'=>$translate->_("Username"),
				'required'=>"required"
			)
		);
		$username->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2,true)->addValidator($val1)->setE;
        $username->setDecorators(
            array(
                array('ViewHelper'),
                //array('Errors', array('class'=>'form-control alert alert-danger'))
            )
        );

		$password = new Zend_Form_Element_Password("password");
		$password->setOrder($i);
		$password->setAttribs(
			array(
				'tabindex'=>$i,
				'class'=>'form-control input-lg',
				'placeholder'=>$translate->_("Password"),
				'required'=>"required"
			)
		);
		$password->setRequired(true)->addValidator($val2,true)->addValidator($val1)->setValue('')->setE;
        $password->setDecorators(
            array(
                array('ViewHelper'),
                //array('Errors', array('class'=>'form-control alert alert-danger'))
            )
        );

		$fontPath = ROOT_DIR . DS . 'fonts';
		
		$captcha = new Zend_Form_Element_Captcha('captcha',
            array('label'=>$translate->_('Enter code below'),
                'captcha'=> array(
                    'captcha'=>'Image',
                    'wordLen'=>6,
                    'timeout'=>60,
                    'suffix'=>'.png',
                    'width'=>220,
                    'height'=>100,
                    'fsize'=>20,
                    'fontSize'=>45,
                    'expiration'=>50,
                    'font'=>$fontPath . DS . 'ariali.ttf',
                    'imgUrl'=> $baseUrl . '/images/captcha/',
                    'gcFreq'=>50,
                    'dotNoiseLevel'=>100,
                    'lineNoiseLevel'=>5,

                )
            )
        );
		$captcha->setOrder($i);
		$captcha->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control input-lg',
                'placeholder'=>$translate->_("Enter code below")
            )
        );
        $captcha->setDecorators(
            array(
                //array('Errors', array('class'=>'form-control alert alert-danger'))
            )
        );
		
		$submit = new Zend_Form_Element_Submit("login");
		$submit->setOrder($i);
		$submit->setAttribs(
			array(
				'tabindex'=>$i,
				'class'=>'btn btn-lg btn-primary btn-block pull-down'
			)
		);
		$submit->setLabel($translate->_("Login"))->setName("login");
		
		$this->setDecorators(
            array(
                array('ViewScript', array('viewScript'=>'viewScripts/auth/AuthFormViewScript.phtml'))
            )
        );
		$this->addElements(array($language, $username, $password, $captcha, $submit));
	}
}