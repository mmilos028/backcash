<?php
/* Form to list players in system */
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
class ListTerminalPlayersForm extends Zend_Form {
	public function __construct($options = null){
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('list-affiliates');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
		$translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);

		$selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptSelectElement.phtml')));
        $submitDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptSubmitElement.phtml')));

        $i = 1;

		$filterbystatus = new Zend_Form_Element_Select('FILTER_BY_STATUS');
		$filterbystatus->setOrder($i);
		$filterbystatus->setLabel($translate->_("Filter By Status"));
		$filterbystatus->addMultiOption(ALL, $translate->_("All"));
		$filterbystatus->addMultiOption(NO, $translate->_("Active"));
		$filterbystatus->addMultiOption(YES, $translate->_("Banned"));
		$filterbystatus->setDecorators($selectDecorator);
		$filterbystatus->setRequired(true);
        $filterbystatus->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$filterby = new Zend_Form_Element_Select('FILTER_BY');
		$filterby->setOrder($i);
		$filterby->setLabel($translate->_("Filter By"));
		$filterby->addMultiOption("All", $translate->_("All Affiliates"));
		$filterby->addMultiOption("Direct", $translate->_("Direct Affiliates"));
		$filterby->setDecorators($selectDecorator);
		$filterby->setRequired(true);
        $filterby->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$pageNo = new Zend_Form_Element_Select('PAGE');
		$pageNo->setOrder($i);
		$pageNo->setLabel($translate->_("Page"));
		$pageNo->setDecorators($selectDecorator);
		$pageNo->setRequired(true);
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
		$limitItems->setDecorators($selectDecorator);
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

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/terminal_players/ListTerminalPlayersViewScript.phtml'))));
		$this->addElements(array($filterbystatus, $filterby, $pageNo, $limitItems, $submit, $previous_page));
	}
}