<?php
namespace Utm;

/**
 * Main framework class that handle the all execution.
 * This class has been coded as a singleton and will only allow the current 
 * one instance to be used during a route rendering whatever it contains a 
 * forward or not.
 * A redirect will lead to another framework loading based on the new 
 * redirected route.
 * The framework handle the cli mode and has fully been coded in PHP 7 version.
 * Minimal configuration values can be found in the app/config/ folder within 
 * the utm.ini and config.ini files.
 * The configuration can be extended by any new *.ini file added in the 
 * app/config folder.
 */
final class Core 
{
    /**
     * Path to the config folder
     */
    const INI_PATH = '../app/config/';
    
    /**
     * Framework current instance
     */
    private static $m_oInstance;
    
    /**
     * Configuration storage
     */
    public static $config = [];
    
    /**
     * Environement storage
     * @var object \Utm\CoreEnv
     */
    private $m_oEnv;
    
    /**
     * Request object
     * @var object \Utm\CoreRequest
     */
    private $m_oRequest;
    
    /**
     * Response object
     * @var object \Utm\CoreResponse
     */
    private $m_oResponse;
    
    /**
     * Framework route storage
     * @var array
     */
    public $m_aRoute = ['GET' => [], 'PUT' => [], 'POST' => [], 'PATCH' => [], 'DELETE' => []];

    /**
     * Singleton pattern
     */
    private function __construct(){}

    /**
     * Framework creation
     * @return object \Utm\Core store the framework unique execution
     */
    public static function instance():\Utm\Core
    {
        // if exists we return the current object
        if (true == isset(self::$m_oInstance)) {
            return self::$m_oInstance;
        }
        // load config
        self::loadIniFile();
        
        return self::$m_oInstance = new \Utm\Core();
    }
    
    /**
     * Get all ini files from config folder and merge the value
     */
    protected static function loadIniFile()
    {
        // getting all config ini files
        $l_aFile = glob(self::INI_PATH.'*.ini');
        if (0 === count($l_aFile)) {
            die('Framework fatal issue, no configuration file found in the folder : ' . self::INI_PATH);
        }
        
        foreach ($l_aFile as $l_sFile) {
            self::$config = array_merge(self::$config, parse_ini_file($l_sFile, true));
        }
    }

    /**
     * Execute the framework
     */
    public function run()
    {
        // framework error handler
        set_error_handler(['\Utm\CoreError', 'error_handler']);

        try {
            // detect environement and set config accordingly
            $this->m_oEnv = new \Utm\CoreEnv();
            $this->m_oRequest = new \Utm\CoreRequest(\Utm\Core::$config['request']);
            // Retrieve plugin public method
            \Utm\CorePlugin::initPlugin();
            // Emit the first event
            \Utm\CorePlugin::emit('onStart');
            // Initialize the request object
            $this->m_oRequest->setRequest();
            \Utm\CorePlugin::emit('onPostRequest');
            // Add the response object
            $this->m_oResponse = new \Utm\CoreResponse();
            // Execute the request
            $this->execute($this->m_oRequest);
            \Utm\CorePlugin::emit('onRender');
            // Display the response
            $this->m_oResponse->render();
            \Utm\CorePlugin::emit('onFinish');
                        
        } catch (\Exception $e) {
            trigger_error($e, E_USER_ERROR);
        } catch (\Throwable $t) {
            trigger_error($t, E_USER_ERROR);
        }
    }
    
    /**
     * Execute the controller
     * @param object \Utm\CoreRequest $p_oRequest Request Object
     * @throws \Exception In case the controller cant be found or the method not
     * callable
     */
    public function execute(\Utm\CoreRequest $p_oRequest)
    {
        \Utm\CorePlugin::emit('onExecute');

        // Retrieve the controller
        $l_aCtrl = $this->findController($p_oRequest);

        if (class_exists($l_aCtrl['class']) && is_callable(array($l_aCtrl['class'], $l_aCtrl['method']))) {
            // Instanciate the controller
            $l_oInstance = new $l_aCtrl['class'];
            // And execute the method
            call_user_func(array($l_oInstance, $l_aCtrl['method']));
        } else {
            throw New \Exception('Class ' . $l_aCtrl['class'] . ' must have method ' . $l_aCtrl['method']);
        }
    }
    
    /**
     * 
     * @param \Utm\CoreRequest $p_oRequest
     * @return mixed false or array with route to the controller value
     * @throws \Exception
     */
    public function findController(\Utm\CoreRequest $p_oRequest)
    {
        $l_aReturn    = false;
        $l_sModule    = $p_oRequest->getModule();
        $l_sClass     = ucfirst($p_oRequest->getController()).self::$config['core']['controller_name'];
        $l_sNameSpace = ($l_sModule) ? $l_sModule . self::$config['core']['module_separator'] : '';
        $l_sPath      = self::$config['path']['controller'] . $l_sNameSpace . $l_sClass . '.php';

        if (file_exists($l_sPath)) {
            $l_aReturn['path']   = $l_sPath;
            $l_aReturn['class']  = '\Utm\\' . self::$config['core']['controller_name'] . '\\';
            $l_aReturn['class']  .= ($l_sModule) ? $l_sModule.'\\'.$l_sClass : $l_sClass;
            $l_aReturn['method'] = $p_oRequest->getAction();
        } else {
            throw new \Exception("The controller is not well formed, expected file :" . $l_sPath);
        }
        return $l_aReturn;
    }
    
    /**
     * Access the environement object
     * @return mixed false or \Utm\CoreEnv
     */
    public function getEnv()
    {
        return $this->m_oEnv ?? false;
    }
    
    /**
     * Access the request object
     * @return mixed false or \Utm\CoreRequest
     */
    public function getRequest()
    {
        return $this->m_oRequest ?? false;
    }
    
    /**
     * Reset the request object
     * @param type $p_sCtrl Controller called
     * @param type $p_sAction Method called
     * @param type $p_sModule Module called
     * @param type $p_aGet Get value
     * @param type $p_aPost Post Value
     * @param type $p_aCli Php cli mod value
     */
    public function resetRequest($p_sCtrl, $p_sAction, $p_sModule = null, $p_aGet = null, $p_aPost = null, $p_aCli = null)
    {
        $this->m_oRequest->setFakeRequest($p_sCtrl, $p_sAction, $p_sModule, $p_aGet, $p_aPost, $p_aCli);
    }
    
    /**
     * Access the response object
     * @return mixed false or \Utm\CoreResponse
     */
    public function getResponse()
    {
        return $this->m_oResponse ?? false;
    }
    
    /**
     * Register the framework plugins used before the run method to load each 
     * plugin.
     * Can handle a simple string with the plugin namespace or an array of 
     * plugin namespace
     */
    public function registerPlugin()
    {
        // on place le plugin dans le tableau des plugins
        \Utm\CorePlugin::register(func_get_args());
    }
    
    /**
     * Add route
     */
    public function addRoute($p_sVerb, $p_sRoute, $p_sComponent)
    {
        array_push($this->m_aRoute[$p_sVerb], ['url' => $p_sRoute, 'component' => $p_sComponent]);
    }
}
