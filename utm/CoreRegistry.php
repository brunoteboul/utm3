<?php
namespace Utm;

/**
 * Class managing the framework internal storage and general for the 
 * application. There are 3 section/namespace ns_general, ns_model, ns_plugin.
 */
class CoreRegistry
{
    /**
     * Default registry namespace
     */
    const GENERAL = 'ns_general';
    
    /**
     * Registry element storage
     * @var array  
     */
    static private $m_aStore = [];
    
    /**
     * Registry element setter
     *
     * @param $p_sLabel string Registry key
     * @param $p_sValue array, string, object Value to be store
     * @param $p_sNameSpace string  Namespace of the storage
     */
    public static function set($p_sLabel, $p_sValue, $p_sNameSpace = self::GENERAL)
    {
        self::$m_aStore[$p_sNameSpace][$p_sLabel] = $p_sValue;
    }
    
    /**
     * Registry element getter
     *
     * @param $p_sLabel string Registry key
     * @param $p_sNameSpace string Namespace of the storage
     * @return mixed Value of the registry or false
     **/
    public static function get($p_sLabel, $p_sNameSpace = self::GENERAL)
    {
        return (true == self::exists($p_sLabel, $p_sNameSpace)) ? self::$m_aStore[$p_sNameSpace][$p_sLabel] : false;
    }
    
    /**
     * Check if a value exists in registry for a namespace
     * @param $p_sVar string Registry key
     * @param $p_sNameSpace string Namespace of the storage
     * @return boolean
     **/
    public static function exists($p_sVar, $p_sNameSpace = self::GENERAL)
    {
        return isset(self::$m_aStore[$p_sNameSpace][$p_sVar]);
    }

    /**
     * Remove a registry entry
     * @param $p_sVar string Registry key
     * @param $p_sNameSpace string Namespace of the storage
     */
    public static function erase($p_sVar, $p_sNameSpace = self::GENERAL)
    {
        if (true == self::exists($p_sVar, $p_sNameSpace)) {
            unset(self::$m_aStore[$p_sNameSpace][$p_sVar]);
        }
    }

    /**
     * Dump all the registry in order to debug
     * @param $p_sElement string Optional define a key to dump only this part
     * @return array The registry requested
     */
    public static function dump($p_sElement = null)
    {
        if (null != $p_sElement && true == isset(self::$m_aStore[$p_sElement])) {
            return self::$m_aStore[$p_sElement]; 
        }
        return self::$m_aStore;
    }
}
