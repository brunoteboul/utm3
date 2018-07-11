<?php
namespace Utm\Controller;

class IndexController extends \Utm\CoreController
{    
    public function index() 
    {
        $this->getPlugin('Twig')->render('index.html.twig', [
            'the' => 'variables', 
            'go' => 'here']
        );
        var_dump($this->getRequest()->getUrlParam(), $this->getRequest()->getRaw());
    }
    
    public function systeme() { }
    public function error404() 
    {
        $this->getResponse()->setHeader("HTTP/1.0 404 Not Found");
        $this->getResponse()->setBody('index::404');
//        $this->getPlugin('Twig')->render('404.html.twig', [
//            'title' => 'Erreur 404', 
//            'message' => 'Cette page est out',
//        ]);
    }
    
    public function put() 
    {
        var_dump($this->getRequest()->getRaw());
        var_dump($this->getRequest()->getVerb(), $this->getRequest()->getUrlParam());
    }
    
    public function delete() 
    {
        var_dump($this->getRequest()->getRaw(), $this->getRequest()->getUrlVar('id'));
        var_dump($this->getRequest()->getVerb(), $this->getRequest()->getUrlParam());
    }
    
    public function patch() 
    {
        var_dump($this->getRequest()->getRaw());
        var_dump($this->getRequest()->getVerb(), $this->getRequest()->getUrlParam());
    }
}
