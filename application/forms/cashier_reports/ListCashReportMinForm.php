<?php
/* Form to list cash report in backoffice */
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
class ListCashReportMinForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('list-cash-report-min');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$this->setAction($baseUrl . '/'. $lang . '/cashier-reports/cash-report-min');
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
        $translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$val1 = new Zend_Validate_StringLength(array('min' => 2, 'max' => 50));
		$val1->setMessages(array(
		    Zend_Validate_StringLength::TOO_SHORT => $translate->_("The string is too short"),
		    Zend_Validate_StringLength::TOO_LONG  => $translate->_('The string is too long'))
        );
		$val2 = new Zend_Validate_NotEmpty();
		$val2->setMessages(array(Zend_Validate_NotEmpty::IS_EMPTY => $translate->_("Value is required and can't be empty")));
		$val_date = new Zend_Validate_Date("d-M-Y");

		$selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptSelectElement.phtml')));
        $dateDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_above_field/ViewScriptDateTimeSingleWithLabelElement.phtml')));

        $i = 1;

		$affiliates = new Zend_Form_Element_Select("AFFILIATES");
		$affiliates->setOrder($i);
		$affiliates->setLabel($translate->_("Level Down"));
		$affiliates->clearMultiOptions();
		$affiliates->setRequired(true);
        $affiliates->setDecorators($selectDecorator);
        $affiliates->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$startdate = new Zend_Form_Element_Text("REPORT_STARTDATE");
		$startdate->setOrder($i);
		$startdate->setLabel($translate->_("Start Date"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val1)->addValidator($val_date)->setE;
        $startdate->setDecorators($dateDecorator);
        $startdate->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=>'form-control',
                'readonly'=>'',
                'size'=>12
			)
		);
        $i++;

		$enddate = new Zend_Form_Element_Text("REPORT_ENDDATE");
		$enddate->setOrder($i);
		$enddate->setLabel($translate->_("End Date"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->addValidator($val2, true)->addValidator($val1)->addValidator($val_date)->setE;
        $enddate->setDecorators($dateDecorator);
        $enddate->setAttribs(
			array(
				'tabindex'=>$i,
                'class'=>'form-control',
                'readonly'=>'',
                'size'=>12
			)
		);
        $i++;

		$pageNo = new Zend_Form_Element_Select('PAGE');
		$pageNo->setOrder($i);
		$pageNo->setLabel($translate->_("Page"));
		$pageNo->setRequired(true);
        $pageNo->setDecorators($selectDecorator);
        $pageNo->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$limitItems = new Zend_Form_Element_Select('LIMIT');
		$limitItems->setOrder($i);
		$limitItems->setLabel($translate->_("Limit"));
		$pages = Zend_Registry::get("pages");
		foreach($pages as $key => $val){
			$limitItems->addMultiOption($val, $key);
		}
		$limitItems->setRequired(true);
        $limitItems->setDecorators($selectDecorator);
        $limitItems->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$currency_list = new Zend_Form_Element_Select('CURRENCIES');
		$currency_list->setOrder($i);
		$currency_list->setLabel($translate->_("Currency"));
		$currency_list->setRequired(true);
        $currency_list->setDecorators($selectDecorator);
		$currencies = $_SESSION['auth_space']['session']['currencies'];
		$currency_list->addMultiOption(ALL, ALL);
		foreach($currencies as $cur) {
            if(!empty($cur['currency'])) {
                $currency_list->addMultiOption($cur['currency'], $cur['currency']);
            }
        }
        $currency_list->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$submit = new Zend_Form_Element_Submit('GENERATE_REPORT');
		$submit->setOrder($i);
		$submit->setValue($translate->_("Generate Report"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $submit->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-primary'
            )
        );
        $i++;

        $previous_page = new Zend_Form_Element_Submit('PREVIOUS_PAGE');
		$previous_page->setOrder($i);
		$previous_page->setValue($translate->_("Previous Page"));
		$previous_page->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $previous_page->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-warning'
            )
        );
        $i++;

        $max_report = new Zend_Form_Element_Submit('MAX_REPORT');
		$max_report->setOrder($i);
		$max_report->setValue($translate->_("Expand"));
		$max_report->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $max_report->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-success'
            )
        );
        $i++;

		$level_direction = new Zend_Form_Element_Hidden("LEVEL_DIRECTION");
		$affiliate_number = new Zend_Form_Element_Hidden("AFFILIATE_NUMBER");

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/cashier_reports/ListCashReportMinViewScript.phtml'))));
		$this->addElements(array($affiliates, $startdate, $enddate, $pageNo, $limitItems,$submit, $level_direction, $currency_list, $affiliate_number,
                $previous_page, $max_report
        ));
	}
}