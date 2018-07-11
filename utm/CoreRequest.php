<?php
namespace Utm;

class CoreRequest
{

    const HTTP = 1; /*!< Indique si on accede au framework par un navigateur*/
    const CLI  = 2; /*!< Indique si on accede au framework en ligne de commande*/
    const AJAX = 3; /*!< Indique si on accede au framework en ajax */

    protected $m_aReqElement = [];      /*!< Elements constituants la requete*/
    protected $m_sRequestType;          /*!< Indique si la requete est de type HTTP ou CLI*/
    protected $m_sModule;               /*!< Elements module de la requete*/
    protected $m_sController;           /*!< Elements controlleur de la requete*/
    protected $m_sAction;               /*!< Elements action de la requete*/
    protected $m_aGet;                  /*!< Elements Get de la requete*/
    protected $m_aPost;                 /*!< Elements Post de la requete*/
    protected $m_aCli;                  /*!< Elements CLI de la requete*/
    protected $m_sVerb;
    protected $m_aUrlParam;
    protected $m_aInput;

    public function __construct(array $p_aReqElement)
    { 
        $this->m_aReqElement = array_flip($p_aReqElement);

        if ('cli' == PHP_SAPI) {
            $this->m_sRequestType = self::CLI;
        } else if (true == $this->isAjax()) {
            $this->m_sRequestType = self::AJAX;
        } else {
            $this->m_sRequestType = self::HTTP;
        }
        // On definit la valeur par defaut d'un controller et de l'action
        $this->m_sController = \Utm\Core::$config['request']['default'];
        $this->m_sAction = \Utm\Core::$config['request']['default'];
    }
    
    protected function isAjax():bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && preg_match('#xmlhttprequest#i', $_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    /**
     * Parse l'url pour créer l'objet request utilisable par le framework
     */
    public function httpParser():array
    {
        $this->m_sVerb = strtoupper($_SERVER['REQUEST_METHOD']);
        parse_str($_SERVER['QUERY_STRING'], $l_aQuery);
        $l_aRoute = $this->hasRoute($l_aQuery);
        if (true == is_array($l_aRoute)) {
            $l_aQuery = $l_aRoute['component'];
        } 
        $this->m_aGet = $_GET;        
        $this->m_aPost = $_POST;
        $this->setRaw();
        return $l_aQuery;
    }

    /**
     * Acces CLI (Ligne de commande) au framework
     * Parse la requete et renvoi un tableau contenant ses éléments
     * @todo Dans les futurs version s de PHP on pourra implémenter la meme
     * syntaxe qu'une commande PHP ex: --param value --param2 value etc.
     * @return array Tableau contenant les éléments de la requete
     */
    protected function cliParser():array
    {
        $l_aQuery = [];
        // On recupere chaque valeur fournie sous la forme key=value
        for ($i=1 ; $i<$_SERVER['argc'] ; $i++) {
            parse_str($_SERVER['argv'][$i], $l_aTemp);
            $l_aQuery = array_merge($l_aQuery, $l_aTemp);
        }
        $this->m_aCli = $_SERVER['argv'];
        return $l_aQuery;
    }

    /**
     * On definit les membres de l'objet request(Type, elements, params, etc.)
     */
    public function setRequest()
    {
        if ($this->m_sRequestType == self::HTTP) {
            $l_aQuery = $this->httpParser();
        } else {
            $l_aQuery = $this->cliParser();
        }

        // On parcours le tableau afin d'y retrouver les clés definies en config
        foreach ($this->m_aReqElement AS $key => $value) {
            if (true == array_key_exists($key, $l_aQuery) && true == is_string($l_aQuery[$key])) {
                if ('module' == $value) {
                    $this->m_sModule = strip_tags($l_aQuery[$key]);
                }
                if ('controller' == $value) {
                    $this->m_sController = strip_tags($l_aQuery[$key]);
                }
                if ('action' == $value) {
                    $this->m_sAction = strip_tags($l_aQuery[$key]);
                }
            }
        }
    }

    /**
     * On remplit l'objet request en fonction d'une requete supplémentaire
     * @return array Tableau request
     */
    public function setFakeRequest($p_sController, $p_sAction, $p_sModule = null, $p_aGet = null, $p_aPost = null, $p_aCli = null)
    {
        $this->m_sModule        = strip_tags($p_sModule);
        $this->m_sController    = strip_tags($p_sController);
        $this->m_sAction        = strip_tags($p_sAction);
        $this->m_aGet           = (true == is_array($p_aGet)) ? $p_aGet : null;
        $this->m_aPost          = (true == is_array($p_aPost)) ? $p_aPost : null;
        $this->m_aCli           = (true == is_array($p_aCli)) ? $p_aCli : null;
    }

    /**
     * Accesseurs
     */
    public function getModule()
    {
        return $this->m_sModule;
    }
    
    public function getController()
    {
        return $this->m_sController;
    }
    
    public function getAction()
    {
        return $this->m_sAction;
    }
    
    public function getMethod()
    {
        return $this->m_sRequestType;
    }
    
    public function getRequest()
    {
        return [$this->m_sController, $this->m_sAction, $this->m_sModule, $this->m_aGet, $this->m_aPost, $this->m_aCli];
    }

    public function getVerb()
    {
        return $this->m_sVerb;
    }

    /**
     *
     * @param <type> $p_sElement
     * @return array
     */
    public function getInput($p_sElement = 'get')
    {
        $l_aInputs = [
            'get' => 'm_aGet',
            'post'=> 'm_aPost',
            'cli' => 'm_aCli',
            'php' => 'm_aInput'
        ];
        
        if (false == isset($l_aInputs[$p_sElement])) {
            throw new \Exception('Invalid method request');
        }
        
        $l_sMethod = $l_aInputs[strtolower($p_sElement)];
        if (true == isset($this->$l_sMethod)) {
            return $this->$l_sMethod;
        }
        
        return false;
    }
    
    /**
     * Framework added route listing for debug purpose
     * @param boolean $p_bDisplay Display or juste return
     * @return \Utm\CoreRouter
     */
    public function listRoute($p_bDisplay = true)
    {
        if ($p_bDisplay) {
            foreach (\Utm\Core::instance()->m_aRoute as $verb => $route) {
                if (true == count($route)) {
                    echo '--------------- Route ' . $verb . '---------------<br>';
                    var_dump($route);
                }
            }
        }
        return \Utm\Core::instance()->m_aRoute;
    }
    
    /**
     * Detect if url contains required information and the native request 
     * element is empty to match a route
     * @param array $p_aQuery The request array
     * @return bool
     */
    public function checkRoute($p_aQuery):bool
    {
        if (true == empty($p_aQuery) && $_SERVER['REQUEST_URI'] != '/' && 1 < strlen($_SERVER['REQUEST_URI'])) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Check if a route exists in the route array and then return it.
     * @param array $p_aQuery The request array
     * @return type
     */
    public function hasRoute($p_aQuery)
    {
        if (true == $this->checkRoute($p_aQuery)) {
            $l_sCurrentUrl = $_SERVER['REQUEST_URI'];
            
            $result = false;
            // fetch framework added route
            foreach(\Utm\Core::instance()->m_aRoute[$this->m_sVerb] as $route) {
                $checkresult = $this->matchRoute($route['url'], $l_sCurrentUrl);
                if (true == is_array($checkresult)) {
                    $result = $route;
                    break;
                }
            }
            
            if (false == $result) {
                $result['component'] = ['ctrl' => 'index', 'act' => \Utm\Core::$config['error']['404']];
            }
            return $result;
        }
    }
    
    /**
     * Try to match the url to a specific route
     * @param string $p_sUrl A route or a route pattern
     * @return boolean
     * @throws exception
     */
    protected function matchRoute($p_sUrl, $p_sCurrentUrl)
    {
        $regexp_path = preg_replace('#:[a-z-_]+#', '([a-zA-Z0-9]+)', $p_sUrl);
        $result = preg_match('#^'.$regexp_path.'$#', $p_sCurrentUrl, $matches);
        array_splice($matches, 0, 1);
        
        if (1 === $result) {
            $urlparam = explode('/', $p_sUrl);
            $i = 0;
            
            foreach ($urlparam as $param) {
                if (':' == substr($param, 0, 1)) {
                    if (true == isset($matches[$i])) {
                        $this->m_aUrlParam[ltrim($param, ':')] = $matches[$i];
                        $i++;
                    } else {
                        throw new \Exception('Route param cant be extract properly!');
                    }
                }
            }
            
            return $matches;
        }
        return false;
    }
    
    protected function setRaw()
    {
        $this->m_aInput = [];
        $rawData = file_get_contents('php://input');
        if (true == strlen($rawData)) {
            parse_str($rawData, $this->m_aInput);
        }
        return $this->m_aInput;
    }
    
    public function getRaw()
    {
        return $this->m_aInput;
    }
    
    public function getRawVar($p_sKey)
    {
        return $this->m_aInput[$p_sKey] ?? null;
    }
    
    public function issetRawVar($p_sKey)
    {
        return isset($this->m_aInput[$p_sKey]);
    }
    
    public function getUrlParam()
    {
        return $this->m_aUrlParam;
    }
    
    public function getUrlVar($p_sKey)
    {
        return $this->m_aUrlParam[$p_sKey] ?? null;
    }
    
    public function issetUrlVar($p_sKey)
    {
        return isset($this->m_aUrlParam[$p_sKey]);
    }
}
