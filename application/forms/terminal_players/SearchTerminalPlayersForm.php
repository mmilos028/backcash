<?php
/* Form to search terminal players in backoffice */
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';

class SearchTerminalPlayersForm extends Zend_Form {
	public function __construct($options = null)
    {
        parent::__construct($options);
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->setName('search-terminal-players');
        $this->setMethod('post');
        $lang = Zend_Registry::get("lang");
        $auth = Zend_Auth::getInstance();
        $authInfo = $auth->getIdentity();
        $session_id = $authInfo->session_id;
        $translate = Zend_Registry::get("translate");
        $this->setTranslator($translate);

        $textDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptTextElement.phtml')));
        $selectDecorator = array(array('ViewScript', array('viewScript' => 'viewScripts/element_decorators/label_left_from_field/ViewScriptSelectElement.phtml')));

        $i = 1;

        $username = new Zend_Form_Element_Text("USERNAME");
		$username->setOrder($i);
		$username->setLabel($translate->_("Username"));
		$username->setDecorators($textDecorator);
        $username->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

        $hardware_key = new Zend_Form_Element_Text("HARDWARE_KEY");
        $hardware_key->setOrder($i);
        $hardware_key->setLabel($translate->_("TerminalPlayer_Hardware Key"));
        $hardware_key->setDecorators($textDecorator);
        $hardware_key->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

        $ba_serial = new Zend_Form_Element_Text("BA_SERIAL");
        $ba_serial->setOrder($i);
        $ba_serial->setLabel($translate->_("TerminalPlayer_BASerial"));
        $ba_serial->setDecorators($textDecorator);
        $ba_serial->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

        $city = new Zend_Form_Element_Text("CITY");
        $city->setOrder($i);
        $city->setLabel($translate->_("Aff City"));
        $city->setDecorators($textDecorator);
        $city->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

        $country = new Zend_Form_Element_Select("COUNTRY");
        $country->setOrder($i);
        $country->setLabel($translate->_("Aff Country"));
        $country->setDecorators($selectDecorator);
        require_once MODELS_DIR . DS . 'CurrencyModel.php';
        $countries = CurrencyModel::getCountries($session_id);
        $country->addMultiOption('', $translate->_("SelectCountry"))->setValue('');
        foreach ($countries['cursor'] as $c){
            $country->addMultiOption(StringHelper::filterCountry($c['name']), StringHelper::filterCountry($c['name']))->setValue(StringHelper::filterCountry($c['name']));
        }
		$country->setValue('');
        $country->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

		$parent_affiliate = new Zend_Form_Element_Select("PARENT_AFFILIATE");
		$parent_affiliate->setOrder($i);
		$parent_affiliate->setLabel($translate->_("Selected Affiliate"));
		$parent_affiliate->setDecorators($selectDecorator);
		require_once MODELS_DIR . DS . 'AffiliatesModel.php';
		$affiliates = AffiliatesModel::getAffiliatesForNewUserForm($session_id);
		$parent_affiliate->addMultiOption("", $translate->_("Selected Affiliate"));
		foreach($affiliates['cursor'] as $aff) {
            $parent_affiliate->addMultiOption($aff['subject_id_to'], $aff['name_to']);
        }
        $parent_affiliate->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

    	$currency = new Zend_Form_Element_Select("CURRENCY");
    	$currency->setOrder($i);
    	$currency->setLabel($translate->_("Currency"))->addFilter("StripTags")->addFilter("StringTrim")->setE;
    	$currency->clearMultiOptions();
    	$currencies = CurrencyModel::getCurrencies();
    	$currency->addMultiOption(ALL, $translate->_("Select Currency"));
    	foreach($currencies['cursor'] as $c) {
            $currency->addMultiOption($c['currency'], $c['currency']);
        }
    	$currency->setValue(ALL);
    	$currency->setDecorators($selectDecorator);
        $currency->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

    	$show_banned = new Zend_Form_Element_Select("BANNED");
		$show_banned->setOrder($i);
        $show_banned->addMultiOption(NO, $translate->_("No"));
        $show_banned->addMultiOption(YES, $translate->_("Yes"));
		$show_banned->setDecorators($selectDecorator);
    	$show_banned->setLabel($translate->_("Show Banned"));
        $show_banned->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

    	$page_no = new Zend_Form_Element_Select('PAGE');
    	$page_no->setOrder($i);
    	$page_no->setLabel($translate->_("Page"))->setRequired(true);
    	$page_no->addMultiOption('1', 1);
        $page_no->setDecorators($selectDecorator);
        $page_no->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

    	$limit_items = new Zend_Form_Element_Select('LIMIT');
    	$limit_items->setOrder($i);
    	$limit_items->setLabel($translate->_("Limit"));
        $limit_items->setDecorators($selectDecorator);
		$pages = Zend_Registry::get("pages");
		foreach($pages as $key => $val){
			$limit_items->addMultiOption($val, $key);
		}
    	$limit_items->setRequired(true);
        $limit_items->setAttribs(
            array(
                'tabindex'=>$i,
                'class'=>'form-control margin-1'
            )
        );
        $i++;

    	$submit = new Zend_Form_Element_Submit('SUBMIT');
    	$submit->setOrder($i);
		$submit->setValue($translate->_("Search"));
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

		$this->setDecorators(array(array('ViewScript', array('viewScript'=>'viewScripts/terminal_players/SearchTerminalPlayersViewScript.phtml'))));
		$this->addElements(array($page_no, $limit_items, $username, $hardware_key, $ba_serial, $city, $country, $parent_affiliate, $show_banned, $currency, $submit, $previous_page));
	}
}