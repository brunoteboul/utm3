<?php
namespace Utm;

/**
 * Class that manage the framework plugins.
 * Plugin are made to create functionnalities shared.
 * They can react to framework events by adding a method with the event name:
 * like onStart or onError.
 */
class CorePlugin extends \Utm\CoreComponent
{
    /**
     * Array containing the method of each plugin in order to lunch them when
     * an event is emited
     * @var array
     */
    static public $m_aPlugin = [];

    /**
     * List of all loaded plugin
     * @var array
     */
    static public $m_aRegistredPlugin = [];

    /**
     * Register the plugin in the list
     * @param $p_aPlugin array All registered plugin
     */
    public static function register(array $p_aPlugin)
    {
        foreach ($p_aPlugin as $l_sValue) {
            if (true == is_string($l_sValue)) {
                self::$m_aRegistredPlugin[] = $l_sValue;
            }
        }
    }

    /**
     * Get all plugin methods.
     * Put all value in an array with the method name as key and the plugin
     * class as var.
     */
    public static function initPlugin()
    {
        // get all method of each plugin
        foreach (self::$m_aRegistredPlugin as $plugin) {
            $l_aMethod = get_class_methods($plugin);
            if (false == is_array($l_aMethod)) {
                throw new \Exception ('The plugin '.$plugin.' doesnt exists or contain no public method');
            }
            foreach ($l_aMethod as $l_sMethod) {
                self::$m_aPlugin[$l_sMethod][] = $plugin;
            }
        }
    }

    /**
     * Emit un event and execute all method associated to this event.
     * @param $p_sEvent string Event name triggered
     */
    public static function emit($p_sEvent)
    {
        // check if there is a method present with the event name
        if (true == isset(self::$m_aPlugin[$p_sEvent])) {
            // search all plugin to check the event method
            foreach (self::$m_aPlugin[$p_sEvent] as $l_sClass) {
                // check if the object exists in the registry
                if (true == \Utm\CoreRegistry::exists($l_sClass, \Utm\Core::$config['registry']['plugin'])) {
                    $l_oPlugin = \Utm\CoreRegistry::get($l_sClass, \Utm\Core::$config['registry']['plugin']);
                } else {
                    $l_oPlugin = new $l_sClass;
                    \Utm\CoreRegistry::set($l_sClass, $l_oPlugin, \Utm\Core::$config['registry']['plugin']);
                }
                // execute the plugin method
                call_user_func(array($l_oPlugin, $p_sEvent));
            }
        }
    }

    /**
     * Check if a plugin is loaded
     * @param string $p_sPlugin Plugin name
     * @return boolean
     */
    public function isLoaded($p_sPlugin)
    {
        return in_array($p_sPlugin, self::$m_aRegistredPlugin);
    }
}
