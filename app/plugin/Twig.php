<?php
namespace Utm\Plugin;

class Twig extends \Utm\CorePlugin
{
    public $m_oTwig;
    public $m_oTwigTemplate;
    public $m_aTwigVar = [];

    public function onStart()
    {
        $l_sPath = realpath(\Utm\Core::$config['path']['view']);
        if (true == is_dir($l_sPath) && true == is_readable($l_sPath)) {
            if (class_exists('Twig_Loader_Filesystem')) {
                $l_oLoader = new \Twig_Loader_Filesystem($l_sPath);
                $l_aLoader = (false == $this->getEnv()->isInProd()) ? ['debug' => true] : ['cache' => \Utm\Core::$config['path']['var'] . 'twig/'];
                $this->m_oTwig = new \Twig_Environment($l_oLoader, $l_aLoader);

                $this->m_oTwig->addExtension(new \Twig_Extension_Debug());

                $home = new \Twig_Function('home', [$this, 'getHome',]);
                $this->m_oTwig->addFunction($home);

                $routeId = new \Twig_Function('routeId', [$this, 'getRouteId',]);
                $this->m_oTwig->addFunction($routeId);
            } else {
                throw new \Exception('Twig must be installed !');
            }
        } else {
            throw new \Exception('The twig view folder : ' . $l_sPath . ' must exists and must be readable.');
        }
    }
    
    public function setVar($p_sKey, $p_mVar)
    {
        $this->m_aTwigVar[$p_sKey] = $p_mVar;
    }
    
    public function renderView($p_sTemplate, array $p_aVar, $p_sKey)
    {
        if (false == array_key_exists($p_sKey, $this->m_aTwigVar)) {
            $this->m_oTwigTemplate = $this->m_oTwig->load($p_sTemplate);
            $this->setVar($p_sKey, $this->m_oTwigTemplate->render($p_aVar));
        } else {
            throw new Exception('The twig partial render key ' . $p_sKey . ' already exists.');
        }
    }
    
    public function render($p_sTemplate, array $p_aVar)
    {
        $this->m_oTwigTemplate = $this->m_oTwig->load($p_sTemplate);
        $this->getResponse()->setBody($this->m_oTwigTemplate->render(array_merge($p_aVar, $this->m_aTwigVar)));
    }
    
    public function getHome()
    {
        return $this->getEnv()->getUrl();
    }
    
    public function getRouteId()
    {
        $l_sModule = \Utm\Core::instance()->getRequest()->getModule();
        $l_sClass = (true == strlen($l_sModule)) ? $l_sModule . '_' : '';
        $l_sClass .= \Utm\Core::instance()->getRequest()->getController() . '-';
        $l_sClass .= \Utm\Core::instance()->getRequest()->getAction();
        return $l_sClass;
    }
}
