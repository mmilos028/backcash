<?php
//pings if bo session is valid and if not automaticly does user logout
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';

class SessionValidationController extends Zend_Controller_Action{
    /**
     * @var int
     */
    private $session_id = 0;

	public function indexAction(){
		$this->forward('login', 'auth');
	}
	
	//pings for server if session is active if not it logouts user
	public function pingSessionAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        $this->view->baseUrl = $this->_request->getBaseUrl();
        try {
            $config = Zend_Registry::get('config');
            require_once MODELS_DIR . DS . 'SessionModel.php';
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity()) {
                $response = array(
                    "status" => OK,
                    "valid_session" => false
                );
                echo($this->_helper->Json($response, false));
            } else {
                $authInfo = $auth->getIdentity();
                if (isset($authInfo)) {
                    $this->session_id = $authInfo->session_id;
                }
                $modelSession = new SessionModel();
                $res = $modelSession->pingSession($this->session_id);
                if($res['yes_no_status'] == NO){
                    $response = array(
                        "status" => OK,
                        "valid_session"=> false
                    );
                    echo($this->_helper->Json($response, false));
                }else{
                    $response = array(
                        "status" => OK,
                        "valid_session"=> true
                    );
                    echo($this->_helper->Json($response, false));
                }
                /*
                if ($res["yes_no_status"] == NO) {
                    $response = array(
                        "status" => OK,
                        "valid_session" => false
                    );
                    echo($this->_helper->Json($response, false));
                } else {
                    $show_dialog = false;
                    $sessionTimeoutSeconds = (intval($config->sessionTimeout) / 1000) / 2; //-- wait for 30 seconds to extend session
                    $remainingSeconds = intval($res['remaining_seconds']); //*3
                    if ($remainingSeconds < ($sessionTimeoutSeconds) * 2) {
                        $show_dialog = true;
                    } else {
                        $show_dialog = false;
                    }
                    $response = array(
                        "status" => OK,
                        "valid_session" => true,
                        "show_dialog" => $show_dialog,
                        "session_timeout_time" => $sessionTimeoutSeconds,
                        "remaining_seconds" => $remainingSeconds
                    );
                    echo($this->_helper->Json($response, false));
                }
                */
            }
            die;
        }catch(Zend_Exception $ex){
            $response = array(
                "status" => OK,
                "valid_session" => false
            );
            echo($this->_helper->Json($response, false));
        }
	}

    //extend backoffice session
	public function extendBackofficeSessionAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        $this->view->baseUrl = $this->_request->getBaseUrl();
        try {
            $auth = Zend_Auth::getInstance();
            $authInfo = $auth->getIdentity();
			if(isset($authInfo)) {
                $this->session_id = $authInfo->session_id;
            }
            require_once MODELS_DIR . DS . 'SessionModel.php';
            SessionModel::setTimeModified($this->session_id);
            $response = array("status"=>OK);
            echo($this->_helper->Json( $response, false ));
        }catch(Zend_Exception $ex){
            $response = array("status"=>NOK);
            echo($this->_helper->Json( $response, false ));
        }
	}
}