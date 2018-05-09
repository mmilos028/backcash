<?php
/* Form to list credit transfers report in backoffice */
class ListCreditTransfersReportFullForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('list-credit-transfers');
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
		$val_date = new Zend_Validate_Date("d-M-Y");

		$selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptSelectElement.phtml')));
        $submitDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSubmitElement.phtml')));
        $dateDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_above_field/ViewScriptDateTimeSingleWithLabelElement.phtml')));
        $i = 1;
		
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

        require_once MODELS_DIR . DS . 'TransactionReportModel.php';
    	$subjects = TransactionReportModel::listSubjects($session_id);
    	$filter_by_player = new Zend_Form_Element_Select('FILTER_BY_PLAYER');
    	$filter_by_player->setOrder($i);
    	$filter_by_player->setLabel($translate->_("Filter by"));
    	foreach($subjects['cursor'] as $sub){
    		$filter_by_player->addMultiOption($sub['name'], $sub['name']);
    	}
    	$filter_by_player->setRequired(true);
        $filter_by_player->setDecorators($selectDecorator);
        $filter_by_player->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

    	$transaction_type = new Zend_Form_Element_Select('TRANSACTION_TYPE');
    	$transaction_type->setOrder($i);
    	$transaction_type->setLabel($translate->_("Transaction Type"));
    	$transaction_type->setRequired(true);
        $transaction_type->setDecorators($selectDecorator);
    	$transactionTypes = TransactionReportModel::listTransactionTypes($session_id);
    	foreach($transactionTypes['cursor'] as $type){
   			$transaction_type->addMultiOption($type['name'], $translate->_($type['name']));
    	}
    	$transaction_type->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
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
        $limitItems->setDecorators($selectDecorator);
		$pages = Zend_Registry::get("pages");
		foreach($pages as $key => $val){
			$limitItems->addMultiOption($val, $key);
		}
		$limitItems->setRequired(true);
        $limitItems->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;
				
    	$submit = new Zend_Form_Element_Submit('GENERATE_REPORT');
    	$submit->setOrder($i);
    	$submit->setValue($translate->_("Generate Report"));
		$submit->setDecorators($submitDecorator);
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
		$previous_page->setDecorators($submitDecorator);
        $previous_page->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-warning'
            )
        );
        $i++;

        $min_report = new Zend_Form_Element_Submit('MIN_REPORT');
		$min_report->setOrder($i);
		$min_report->setValue($translate->_("Minimize"));
		$min_report->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $min_report->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-success'
            )
        );
        $i++;
		
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/cashier_reports/ListCreditTransfersReportFormFullViewScript.phtml'))));
		$this->addElements(array($pageNo, $limitItems, $startdate, $enddate, $filter_by_player, $transaction_type, $submit, $previous_page, $min_report) );
	}
}