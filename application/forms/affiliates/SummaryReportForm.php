<?php
/**
 * Summary Report
 */
class SummaryReportForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct ( $options );
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName ('summary-report');
		$this->setMethod ('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance ();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
        $translate = Zend_Registry::get("translate");
        $this->setTranslator($translate);
		$val1 = new Zend_Validate_StringLength(array('min' => 5, 'max' => 50));
		$val1->setMessages( array(
		Zend_Validate_StringLength::TOO_SHORT => $translate->_("The string is too short"),
		Zend_Validate_StringLength::TOO_LONG  => $translate->_('The string is too long')));
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		$val_date = new Zend_Validate_Date("d-M-Y");
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
        $dateDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptDateTimeSingleElement.phtml')));
        $submitDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSubmitElement.phtml')));
        $i = 1;

        $start_date = new Zend_Form_Element_Text("START_DATE");
    	$start_date->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val1)->addValidator($val_date)->setE;
    	$start_date->setDecorators($dateDecorator);
        $start_date->setAttribs(
            array(
                'tabindex'=>$i,
                'style'=>'max-width: 100px;',
                'class'=>'form-control',
                'readonly'=>''
            )
        );
        $i++;

        $end_date = new Zend_Form_Element_Text("END_DATE");
    	$end_date->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val1)->addValidator($val_date)->setE;
    	$end_date->setDecorators($dateDecorator);
        $end_date->setAttribs(
            array(
                'tabindex'=>$i,
                'style'=>'max-width: 100px;',
                'class'=>'form-control',
                'readonly'=>''
            )
        );
        $i++;

        $generate_report = new Zend_Form_Element_Submit('GENERATE_REPORT');
		$generate_report->setOrder($i);
        $generate_report->setValue($translate->_("Generate Report"));
        $generate_report->setDecorators($submitDecorator);
        $generate_report->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-primary'
            )
        );

        $this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/affiliates/SummaryReportViewScript.phtml'))));
		$this->addElements ( array ($start_date, $end_date, $generate_report) );
	}
}