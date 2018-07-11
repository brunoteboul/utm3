<?php
namespace Utm;

/**
 * Class used to share some framework behavior.
 * Plugin, response and environement accessors.
 */
class CoreComponent
{
    /**
     * Return a plugin instance from the registry if exists or instanciate the 
     * plugin then put it in the registry before returning it.
     * @param string $p_sClass
     * @return \Utm\l_oNameSpace
     * @throws \Exception
     */
    public function getPlugin(string $p_sClass)
    {   
        $l_oNameSpace = '\Utm\\Plugin\\' . $p_sClass;
        if (true == \Utm\CoreRegistry::exists($l_oNameSpace, core::$config['registry']['plugin'])) {
            // return the plugin from the registry
            return \Utm\CoreRegistry::get($l_oNameSpace, core::$config['registry']['plugin']);
        } else if (false == class_exists($l_oNameSpace)) {
            throw new \Exception("Le plugin demandé n'existe pas, format accepté :" . $l_oNameSpace);
        } else {
            // create an instance of the plugin
            $l_oClass = new $l_oNameSpace;
            // put it in the registry
            \Utm\CoreRegistry::set($p_sClass, $l_oClass, \Utm\Core::$config['registry']['plugin']);
            return $l_oClass;
        }
    }
    
    /**
     * Accessor to the framework response instance
     * @return object \Utm\CoreResponse
     */
    public function getResponse()
    {   
        return \Utm\Core::instance()->getResponse();
    }
    
    /**
     * Accessor to the framework environement instance
     * @return object \Utm\CoreEnv
     */
    public function getEnv()
    {   
        return \Utm\Core::instance()->getEnv();
    }
    
    public function getRequest()
    {
        return \Utm\Core::instance()->getRequest();
    }
}