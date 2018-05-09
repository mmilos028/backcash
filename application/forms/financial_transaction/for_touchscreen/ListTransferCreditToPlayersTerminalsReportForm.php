<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
/* Form to list transfer credits to players / terminals report */
class ListTransferCreditToPlayersTerminalsReportForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('list-players-terminals-transfer-credits');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
		$translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);

        $selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSelectElement.phtml')));

        $pageNo = new Zend_Form_Element_Select('PAGE');
		$pageNo->setLabel($translate->_("Page"));
		$pageNo->setRequired(true);
        $pageNo->setDecorators($selectDecorator);
		$pageNo->setAttribs(
            array(
                'class' => 'form-control input-lg'
            )
        );

		$limitItems = new Zend_Form_Element_Select('LIMIT');
		$limitItems->setLabel($translate->_("Limit"));
		$pages = Zend_Registry::get("pages");
		foreach($pages as $key => $val){
			//if($key == ALL)continue;
			$limitItems->addMultiOption($val, $key);
		}
		$limitItems->setRequired(true);
        $limitItems->setDecorators($selectDecorator);
		$limitItems->setAttribs(
            array(
                'class' => 'form-control input-lg'
            )
        );

		$currencyList = new Zend_Form_Element_Select('CURRENCY');
		$currencyList->setLabel($translate->_("Currency"));
		$currencyList->setAttribs(
            array(
                'class' => 'form-control input-lg'
            )
        );
		$currencies = $_SESSION['auth_space']['session']['currencies'];
		foreach($currencies as $cur){
			$currencyList->addMultiOption($cur['currency'], $cur['currency']);
		}
		$currencyList->setRequired(true);
        $currencyList->setDecorators($selectDecorator);

    	$submit = new Zend_Form_Element_Submit('GENERATE_REPORT');
		$submit->setValue($translate->_("Generate Report"));
		$submit->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
		$submit->setAttribs(
            array(
                'class' => 'btn btn-lg btn-primary'
            )
        );
		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/financial_transaction/for_touchscreen/ListTransferCreditToPlayersTerminalsReportViewScript.phtml'))));
		$this->addElements(array($pageNo, $limitItems, $currencyList, $submit));
	}
}