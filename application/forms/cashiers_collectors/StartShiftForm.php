<?php
/* Form that starts new shift */
class StartShiftForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('start-shift-form');
		$this->setMethod('post');
		$lang = Zend_Registry::get('lang');
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
		$translate = Zend_Registry::get('translate');
		$this->setTranslator($translate);			
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
		$float_val = new Zend_Validate_Float();
		$float_val->setMessages(array(
		Zend_Validate_Float::INVALID => $translate->_('FLOAT_NOT_VALID'),
		Zend_Validate_Float::NOT_FLOAT => $translate->_('NOT_FLOAT')));
		$val1 = new Zend_Validate_NotEmpty();
		$val1->setMessages(
		array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		
		$cash_in = new Zend_Form_Element_Hidden('CASH_IN');
		$cash_in->setE;		
		
		$cash_out = new Zend_Form_Element_Hidden('CASH_OUT');
		$cash_out->setE;		
		
		$balance = new Zend_Form_Element_Hidden('BALANCE');
		$balance->setE;	
		
		$shift_start_time = new Zend_Form_Element_Text('START_SHIFT_TIME');
		$shift_start_time->setOrder(1);
		$shift_start_time->setAttribs(
            array(
                'disabled'=>true,
                'size'=>20,
                'tabindex'=>1,
                'class'=>'form-control margin-1'
            )
        );
		$shift_start_time->setLabel($translate->_('Shift Start Time'))->setE;
		$shift_start_time->setDecorators($textDecorator);		
		
		$amount = new Zend_Form_Element_Text('AMOUNT');
		$amount->setOrder(2);
		$amount->setLabel($translate->_('Amount'))->setRequired(true)->addFilter('StripTags')->addFilter('StringTrim')->addValidator($val1)->addValidator($float_val);
        $amount
            ->setAttribs(
                array(
                    'tabindex' => 2,
                    'class' => 'form-control margin-1',
                )
            )
            ->setE;
		$amount->setDecorators($textDecorator);
		$amount->setValue(0);
				
		$comment = new Zend_Form_Element_Textarea('COMMENT');
		$comment->setOrder(3);
		$comment->setLabel($translate->_('Comment'));
        $comment
            ->setAttribs(
                array(
                    'rows'=>5,
                    'cols'=>69,
                    'tabindex'=>3,
                    'class'=>'form-control margin-1'
                )
            )
            ->setE;
				
		$submit = new Zend_Form_Element_Submit('SAVE');
		$submit->setOrder(4);
		$submit->setAttribs(
            array(
                'tabindex'=>4,
                'class' => 'btn btn-primary btn-block'
            )
        );
		$submit->setLabel($translate->_("Start Shift"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$submit->setValue($translate->_("Start Shift"));
        $submit->setAttribs(
            array(
                'class' => 'btn btn-primary btn-block'
            )
        );
				
		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(600);

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/cashiers_collectors/StartShiftFormViewScript.phtml'))));
		$this->addElements(array($cash_in, $cash_out, $balance, $shift_start_time, $amount, $comment, $submit, $csrf));
	}
}