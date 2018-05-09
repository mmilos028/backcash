<?php
/* Form to unlock player status */
class PlayerUnlockForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$this->setName('player-unlock-form');
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

        $submit_unlock = new Zend_Form_Element_Submit("UNLOCK");
		$submit_unlock->setOrder($i);
		$submit_unlock->setLabel($translate->_("Unlock"));
		$submit_unlock->setValue($translate->_("Unlock"));
		$submit_unlock->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/element_decorators/ViewScriptSubmitElement.phtml'))));
        $submit_unlock->setAttribs(
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

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/players/PlayerUnlockViewScript.phtml'))));
    	$this->addElements(array($user_name, $submit_unlock, $cancel, $csrf));
	}
}