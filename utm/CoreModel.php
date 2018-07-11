<?php
namespace Utm;

/**
 * Model manager for the framework.
 * All model extend CoreModel that implement a factory with a singleton by 
 * default.
 */
class CoreModel extends \Utm\CoreComponent
{
    /**
     * Factory to create instance of called model.
     * Option to access new instance allow to bypass the singleton.
     * 
     * @param string $p_sClass The model to instanciate
     * @param bool $p_bNew Allow to force a new instance, to avoid singleton
     * @return object Model called
     * @throws \Exception
     */
    public static function factory($p_sClass, $p_bNew = false)
    {
        if (true == \Utm\CoreRegistry::exists($p_sClass, core::$config['registry']['model']) && false == $p_bNew) {
            return \Utm\CoreRegistry::get($p_sClass, core::$config['registry']['model']);
        } else if (false == class_exists($p_sClass)) {
            throw new \Exception("The model doesnt exists : " . $p_sClass);
        } else {
            $l_oClass = new $p_sClass;
            \Utm\CoreRegistry::set($p_sClass, $l_oClass, \Utm\Core::$config['registry']['model']);
            return $l_oClass;
        }
    }
}
