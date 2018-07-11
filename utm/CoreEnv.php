<?php
namespace Utm;

/**
 * Handle the environement value such as the environement name, url and reparse 
 * the framework config array to set the correct environement values.
 * 
 * Exemple of configuration file and use in the class, adding a section [env] 
 * in the config.ini file :
 * [env]
 * local         = "http://localurl.loc||http://www.localurl.loc"
 * dev           = "http://devurl.com"
 * prod          = "http://produrl.com"
 * 
 * This will create 3 environements, they will be detecting based on url 
 * matching. Note that you can set as meny url you want by env with the uses of
 * || and that there is not / at the end of each url.
 * 
 * In order to get the isInProd method to work you should use the predifined 
 * environement values :
 * prod      For final production environement
 * preprod   For testing final environement
 * recette   For customer validation before preprod and prod
 * 
 * However you can use as many env as you need and you can name them the way you
 * like, the isInProd method can be easily overruled by your own method.
 */
class CoreEnv
{
    /**
     * App url
     * @var string 
     */
    private $m_sUrl;
    
    /**
     * Flag for https 
     * @var bool 
     */
    private $m_bHttps;
    
    /**
     * Store current environement, standard value are :
     * prod|preprod|recette|dev|local
     * Those key are defined in the config.ini file, env section
     * @var string 
     */
    private $m_sCurrent;
    
    /**
     * Call the url service setting and then reformat framework config value
     */
    public function __construct()
    {
        $this->getUrl();
        $this->setEnv();
    }
    
    /**
     * Return the website base url
     * @return string App url
     */
    public function getUrl() 
    {
        if (true != strlen($this->m_sUrl)) {
            $this->m_bHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? true : false;
            $l_sUrl = 'http' . (true == $this->m_bHttps ? 's' : null) . '://' . $_SERVER['HTTP_HOST'];
            $this->m_sUrl = rtrim($l_sUrl, '/');
        }
        
        return $this->m_sUrl;
    }
    
    /**
     * Retrieve from config.ini env section the possible environement value
     * and compare the url to check wich environement we are in.
     * If an environement is detected then it calls setConfig to reformat the 
     * framework config value according to the current env.
     */
    private function setEnv()
    {
        // check in env section from framework config
        if (isset(\Utm\Core::$config['env']) && is_array(\Utm\Core::$config['env'])) {
            foreach (\Utm\Core::$config['env'] as $key => $value) {
                $val = explode('||', $value);// handle multiple url for one env
                if (in_array($this->m_sUrl, $val)) {
                    // found the env then set the class var and leave the loop
                    $this->m_sCurrent = $key;
                    continue;
                }
            }
            
            if (true == strlen($this->m_sCurrent)) {
                //rewrite framework conf now we have detected environement
                $this->setConfig();
            }
        }
    }
    
    /**
     * Parse the framework config array and reformat it according to the 
     * detected environement
     */
    private function setConfig()
    {
        foreach (\Utm\Core::$config as $key => $value) {
            foreach ($value as $subKey => $subValue) {
                // check if we find a dot in the key for an env prefix
                if (strpos($subKey, '.')) {
                    $split = explode('.', $subKey);
                    if ($this->m_sCurrent == $split[0]) {
                        // add a new key free of env prefix
                        \Utm\Core::$config[$key][$split[1]] = $subValue;
                    } 
                    // we remove the key now the reformat is done
                    unset(\Utm\Core::$config[$key][$subKey]);
                }                
            }               
        }
    }
    
    /**
     * Check if we are dealing with a https request
     * @return bool
     */
    public function isHttps()
    {
        return $this->m_bHttps;
    }
    
    /**
     * Return current environement
     * @return string
     */
    public function getCurrent()
    {
        return $this->m_sCurrent;
    }
    
    /**
     * Check if the current environement belong to the production range of env :
     * prod|preprod|recette
     * @return bool
     */
    public function isInProd()
    {
        return in_array($this->m_sCurrent, ['prod', 'preprod', 'recette']);
    }
}