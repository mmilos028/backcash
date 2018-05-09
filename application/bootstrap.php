<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    //initialize default settings used for applicatin
    protected function _initSettings(){
        $config = Zend_Registry::get('config');
        Zend_Locale::setDefault('en_US');
        Zend_Registry::set('possible_logins', $config->possible_logins);
    }

    //initialize database and database profiler
    protected function _initDatabase(){
        $config = Zend_Registry::get('config');
        $params = array(
            'dbname' => $config->db->dbname,
            'username' => $config->db->username,
            'password' => $config->db->password,
            'persistent' => $config->db->persistent,
            'profiler' => $config->db->profiler,
            'charset' => $config->db->charset
        );
        if($config->getSectionName() == "production" || $config->getSectionName() == "testing"){
            $params['profiler'] = array('enabled'=>true, 'class'=>'Zend_Db_Profiler_Firebug');
        }
        $db = Zend_Db::factory($config->db->adapter, $params);

        $profiler = $db->getProfiler();
        $profiler->setFilterQueryType(Zend_Db_Profiler::QUERY); //only call procedure
        //$profiler->setFilterQueryType(Zend_Db_Profiler::CONNECT); //only connect to database
        //$profiler->setFilterQueryType(Zend_Db_Profiler::TRANSACTION); //only transaction in database (prints only begin and commit to database
        Zend_Registry::set('db_auth', $db);
        unset($db);

        /*
        require_once 'Zend/Cache/Manager.php';
            $disable_caching = false;
            $frontend = array(
                'lifetime' => 600,
                'automatic_serialization' =>true,
                'disable_caching' => $disable_caching,
            );
            $backend = array(
                'lifetime' => 600,
                'cache_dir' => $config->db->cache_location,
                'automatic_serialization' => true,
                'disable_caching' => $disable_caching,
            );

            $cache = Zend_Cache::factory('Core', 'File', $frontend, $backend );
            Zend_Registry::set('db_cache', $cache);
        */
    }

    //initialize default page list
    protected function _initPagination(){
        $pages = array(
            "10"=>10,
            "25"=>25,
            "50"=>50,
            "100"=>100,
            "200"=>200,
            "ALL"=>1000000
        );
        Zend_Registry::set('pages', $pages);
    }

    //initialize routes for application URL's
    protected function _initRoutes()
    {
        $config = Zend_Registry::get('config');
        $this->bootstrap('FrontController');
        $frontController = $this->getResource('frontController');
        $frontController->setControllerDirectory( APP_DIR . DS . 'controllers');
        $languages = array_keys($config->languages->toArray());
        $zl = new Zend_Locale();
        $lang = in_array($zl->getLanguage(), $languages) ? $zl->getLanguage() : 'en';
        //$route = new Zend_Controller_Router_Customroute(':lang/:controller/:action/*',  array('controller'=>'index', 'action' => 'index', 'module'=>'default', 'lang'=>$lang));
        $route = new Zend_Controller_Router_Route(':lang/:controller/:action/*',  array('controller'=>'index', 'action' => 'index', 'module'=>'default', 'lang'=>$lang));
        $router = $frontController->getRouter();
        $router->addRoute('default', $route);
        $frontController->setRouter($router);
    }

    //load and initialize translation files
    protected function _initTranslations(){
        $config = Zend_Registry::get('config');
        require_once HELPERS_DIR . DS .'LanguageSetup.php';
        $languageHelper = new LanguageSetup($config->languages->toArray(), ROOT_DIR . '/application/configs/translations');
        Zend_Controller_Action_HelperBroker::addHelper($languageHelper);
    }

    //initialize and set locale for application
    protected function _initLocale(){
        $locale = new Zend_Locale('en_US');
        Zend_Registry::set('Zend_Locale', $locale);
    }

    //initialize backoffice loggers
    protected function _initBackofficeLoggers(){
        $config = Zend_Registry::get('config');
        $errorPathFile = $config->errorPathFile;
        $errorLogSize = $config->errorLogSize;
        try{
            if(file_exists($errorPathFile)){
                if(filesize($errorPathFile) >= $errorLogSize * 1024 * 1024){ //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($errorPathFile, '.txt');
                    $file_path = dirname($errorPathFile);
                    $new_file_name = $file_name . '_' . date('d-M-Y_H-i-s') . '.txt';
                    $new_file = $file_path . DS . $new_file_name;
                    rename($errorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($errorPathFile);
            $logger = new Zend_Log();
            $logger->addWriter($writerFile);
            Zend_Registry::set('logger', $logger);
        }catch(Exception $ex){
        }
    }

    //initialize layout scripts path
    protected function _initLayout(){
        $layout = Zend_Layout::startMvc(APP_DIR . DS . 'layouts'. DS .'scripts');
        $layout->setLayout('layout');
        return $layout;
    }

    //initialize view setup
    protected function _initView()
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->doctype('XHTML11');
        $view->addHelperPath('ZendX/JQuery/View/Helper/','ZendX_JQuery_View_Helper');
        return $view;
    }

    //initialize viewrendering and skins
    protected function _initViewRenderer(){
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($this->getResource('view'));
        //$viewRenderer->view->skin = 'cupertino';
        //$viewRenderer->view->skin = 'smoothness'; // radi kako treba
        $viewRenderer->view->skin = 'purple'; //radi kako treba
        //$viewRenderer->view->skin = 'humanity'; //radi kako treba
        //$viewRenderer->view->skin = 'redmond';
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        return $viewRenderer;
    }
}