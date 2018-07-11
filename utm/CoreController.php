<?php
namespace Utm;

/**
 * Base of all controller, allow the acces of 2 important method :
 * forward and redirect 
 */
class CoreController extends \Utm\CoreComponent
{
    /**
     * Forward action : modify the request object and execute the framework
     *
     * @param $p_sController string Controller name
     * @param $p_sAction string     Action name
     * @param $p_sModule string     Module name
     * @param $p_aGet array         GET param
     * @param $p_aPost array        POST param
     * @param $p_aCli array         Cli param
     */
    protected function forward($p_sController, $p_sAction, $p_sModule = null, $p_aGet = null, $p_aPost = null, $p_aCli = null)
    {
        $l_oCore = \Utm\Core::instance();
        // redefine the request to create a specific request object
        $l_oCore->resetRequest($p_sController, $p_sAction, $p_sModule, $p_aGet, $p_aPost, $p_aCli);
        // execute the new request
        return $l_oCore->execute($l_oCore->getRequest());
    }
    
    /**
     * Redirect to another route with the help of the response object
     *
     * @param $p_sController string Controller name
     * @param $p_sAction string     Action name
     * @param $p_sModule string     Module name
     * @param $p_aGet array         GET param
     */
    protected function redirect($p_sController, $p_sAction, $p_sModule = null, $p_aGet = null)
    {        
        if (false == headers_sent()) {
            $l_aReq = \Utm\Core::$config['request'];
        
            $l_sUrl = (null != $p_sModule) ? $l_aReq['module'] . '=' . $p_sModule . '&' : ''; 
            $l_sUrl .= $l_aReq['controller'] . '=' . $p_sController . '&';
            $l_sUrl .= $l_aReq['action'] . '=' . $p_sAction . '&';

            // add the GET param to the redirected url
            if (null != $p_aGet) {
                if (false == is_array($p_aGet)) {
                    throw new \Exception ('GET param must be provided as array.');
                }
                foreach ($p_aGet as $key => $value) {
                    $l_sUrl .= $key . '=' . $value . '&';
                }
            }
            
            $l_sBaseUrl = ($this->getEnv()->getUrl()) ? $this->getEnv()->getUrl().'/index.php?' : 'index.php?';
            $l_sUrl = $l_sBaseUrl.rtrim($l_sUrl, '&');
            // redirect instruction send to the response object
            $this->getResponse()->resetHeader()->setHeader('Location: ' . $l_sUrl);
        } else {
            throw new \Exception('Framework redirect impossible, header already sent.');
        }
    }
}
