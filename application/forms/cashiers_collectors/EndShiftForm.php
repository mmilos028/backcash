<?php
/* Form that ends shift */
class EndShiftForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('start-shift-form');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
		$translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
		$float_val = new Zend_Validate_Float();
		$float_val->setMessages(array(
		Zend_Validate_Float::INVALID => $translate->_("FLOAT_NOT_VALID"),
		Zend_Validate_Float::NOT_FLOAT => $translate->_("NOT_FLOAT")));
		$val1 = new Zend_Validate_NotEmpty();
		$val1->setMessages(
		array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		
		$total_in = new Zend_Form_Element_Text("TOTAL_IN");
		$total_in->setLabel($translate->_("IN"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$total_in
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                )
            )
            ->setE;
		$total_in->setDecorators($textDecorator);		
		
		$total_out = new Zend_Form_Element_Text("TOTAL_OUT");
		$total_out->setLabel($translate->_("OUT"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
		$total_out
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                )
            )
            ->setE;
		$total_out->setDecorators($textDecorator);		
		
		$balance = new Zend_Form_Element_Hidden("BALANCE");		
		$cash_in = new Zend_Form_Element_Hidden("CASH_IN");		
		$cash_out = new Zend_Form_Element_Hidden("CASH_OUT");			
		$cash_in_s = new Zend_Form_Element_Hidden("CASH_IN_S");		
		$cash_out_s = new Zend_Form_Element_Hidden("CASH_OUT_S");			
		$balance_s = new Zend_Form_Element_Hidden("BALANCE_S");		
		
		$shift_start_time = new Zend_Form_Element_Text("START_SHIFT_TIME");
		$shift_start_time
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                    'size' => 22
                )
            );
		$shift_start_time->setLabel($translate->_("Shift End Time"))->setE;
		$shift_start_time->setDecorators($textDecorator);		
		
		$shift_start_time_s = new Zend_Form_Element_Text("START_SHIFT_TIME_S");
		$shift_start_time_s
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                    'size' => 22
                )
            );
		$shift_start_time_s->setLabel($translate->_("Shift Start Time"))->setE;
		$shift_start_time_s->setDecorators($textDecorator);		
		
		$duration_time = new Zend_Form_Element_Text("DURATION_TIME");
		$duration_time
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                    'size' => 22
                )
            );
		$duration_time->setLabel($translate->_("Duration Time"))->setE;
		$duration_time->setDecorators($textDecorator);
		
		$balance_start = new Zend_Form_Element_Text("BALANCE_START");
		$balance_start
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                    'size' => 20
                )
            );
		$balance_start->setLabel($translate->_("Balance Start"))->setE;
		$balance_start->setDecorators($textDecorator);
		
		$balance_end = new Zend_Form_Element_Text("BALANCE_END");
		$balance_end//->setAttribs(array("disabled"=>"disabled", "size"=>20));
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                    'size' => 20
                )
            );
		$balance_end->setLabel($translate->_("Balance End"))->setE;
		$balance_end->setDecorators($textDecorator);		
		
		$balance_diff = new Zend_Form_Element_Text("BALANCE_DIFF");
		$balance_diff
            ->setAttribs(
                array(
                    'disabled' =>true,
                    'class' => 'form-control margin-1',
                    'size' => 20
                )
            );
		$balance_diff->setLabel($translate->_("Netto"))->setE;
		$balance_diff->setDecorators($textDecorator);		
		
		$amount = new Zend_Form_Element_Text('AMOUNT');
		$amount->setOrder(1);
		$amount
            ->setAttribs(
                array(
                    'tabindex' => 1,
                    'class' => 'form-control margin-1',
                    'size' => 22
                )
            );
		$amount->setLabel($translate->_('Amount'))->setRequired(true)->addFilter('StripTags')->addFilter('StringTrim')->addValidator($val1)->addValidator($float_val)->setE;
		$amount->setDecorators($textDecorator);
		$amount->setValue(0);
			
		$comment = new Zend_Form_Element_Textarea("COMMENT");
		$comment->setOrder(2);
		$comment
            ->setAttribs(
                array(
                    'rows' => 5,
                    'cols' => 69,
                    'tabindex' => 2,
                    'class' => 'form-control margin-1'
                )
            );
		$comment->setLabel($translate->_("Comment"));
		$comment->setE;
	
		$submit = new Zend_Form_Element_Submit("SAVE");
		$submit->setOrder(3);
		$submit
            ->setAttribs(
                array(
                'class' => 'btn btn-primary btn-block'
            )
            );
		$submit->setLabel($translate->_("End Shift Button"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$submit->setValue($translate->_("End Shift Button"));
		
		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(600);

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/cashiers_collectors/EndShiftFormViewScript.phtml'))));
		$this->addElements(array($total_in, $total_out, $balance, $cash_in, $cash_out, $shift_start_time, $balance_s, $cash_in_s, $cash_out_s, $shift_start_time_s, $duration_time, $balance_diff, $balance_start, $balance_end, $amount, $comment, $submit, $csrf));
	}
}