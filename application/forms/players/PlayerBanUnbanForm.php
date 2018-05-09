<?php
/* Form to ban or unban player */
class PlayerBanUnbanForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('player-ban-unban-form');
		$this->setMethod('post');
		$lang = Zend_Registry::get("lang");
		$auth = Zend_Auth::getInstance();
		$authInfo = $auth->getIdentity();
		$session_id = $authInfo->session_id;
        $translate = Zend_Registry::get("translate");
		$this->setTranslator($translate);
		$textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/ViewScriptTextElement.phtml')));
        $i = 1;

		$user_name = new Zend_Form_Element_Text("NAME");
		$user_name->setOrder($i);
		$user_name->setLabel($translate->_("Player Name"))->setRequired(true)->addFilter("StripTags")->addFilter("StringTrim")->setE;
        $user_name
            ->setAttrib('tabindex', $i)
            ->setAttrib("disabled", true)
            ->setAttrib("autofocus", "")
            ->setAttrib("class", "form-control margin-1");
		$user_name->setDecorators($textDecorator);
        $i++;

        $status = new Zend_Form_Element_PlainText("STATUS");
		$status->setOrder($i);
		$status->setLabel($translate->_("Player Status"))->setE;
        $status
            ->setAttrib('tabindex', $i)
            ->setAttrib("disabled", true)
            ->setAttrib("class", "form-control margin-1");
		$status->setDecorators($textDecorator);
        $i++;

		$submit_ban = new Zend_Form_Element_Submit("BAN");
		$submit_ban->setOrder($i);
		$submit_ban->setLabel($translate->_("Ban"));
		$submit_ban->setValue($translate->_("Ban"));
		$submit_ban->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $submit_ban->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-danger btn-block'
            )
        );
        $i++;

        $submit_unban = new Zend_Form_Element_Submit("UNBAN");
		$submit_unban->setOrder($i);
		$submit_unban->setLabel($translate->_("Unban"));
		$submit_unban->setValue($translate->_("Unban"));
		$submit_unban->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $submit_unban->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-success btn-block'
            )
        );
        $i++;

		$cancel = new Zend_Form_Element_Submit("CANCEL");
		$cancel->setOrder($i);
		$cancel->setLabel($translate->_("Cancel"));
		$cancel->setValue($translate->_("Cancel"));
		$cancel->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $cancel->setAttribs(
            array(
                'tabindex'=>$i,
                'class' => 'btn btn-default btn-block'
            )
        );
        $i++;

		$csrf = new Zend_Form_Element_Hash('CSRF');
		$csrf->setSalt(md5(uniqid(rand(), TRUE)));
		$csrf->setTimeout(600);

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/players/PlayerBanUnbanViewScript.phtml'))));
    	$this->addElements(array($user_name, $status, $submit_ban, $submit_unban, $cancel, $csrf));
	}
}