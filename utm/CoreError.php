<?php
namespace Utm;

class CoreError extends \Exception
{
    /**
     * Html error message formated
     * @var string 
     */
    private static $m_sHtmlErrorMessage;
    
    /**
     * Text error message (used for logging purpose or php mod cli).
     * @var string 
     */
    private static $m_sTextErrorMessage;

    /**
     * Reformat the error message title based on the error number
     * @param $p_sError string Error php number
     * @return string Formated error title
     */
    protected static function getCodeTitle($p_sError)
    {
        switch($p_sError){
            default:
            case E_ERROR:
            $errname = 'E_ERROR ('.E_ERROR.') Erreur'; break;
            case E_WARNING:
            $errname = 'E_WARNING ('.E_WARNING.') Alerte'; break;
            case E_PARSE:
            $errname = 'E_PARSE ('.E_PARSE.') Erreur d\'analyse'; break;
            case E_NOTICE:
            $errname = 'E_NOTICE ('.E_NOTICE.') Note'; break;
            case E_CORE_ERROR:
            $errname = 'E_CORE_ERROR ('.E_CORE_ERROR.') PHP Core error'; break;
            case E_CORE_WARNING:
            $errname = 'E_CORE_WARNING ('.E_CORE_WARNING.') PHP Core warning'; break;
            case E_COMPILE_ERROR:
            $errname = 'E_COMPILE_ERROR ('.E_COMPILE_ERROR.') Erreur de compilation'; break;
            case E_COMPILE_WARNING:
            $errname = 'E_COMPILE_WARNING ('.E_COMPILE_WARNING.') Avertissement de compilation'; break;
            case E_USER_ERROR:
            $errname = 'E_USER_ERROR ('.E_USER_ERROR.') Erreur utilisateur'; break;
            case E_USER_WARNING:
            $errname = 'E_USER_WARNING ('.E_USER_WARNING.') Avertissement utilisateur'; break;
            case E_USER_NOTICE:
            $errname = 'E_USER_NOTICE ('.E_USER_NOTICE.') Note utilisateur'; break;
            case E_STRICT:
            $errname = 'E_STRICT ('.E_STRICT.') Note strict'; break;
            case E_RECOVERABLE_ERROR:
            $errname = 'E_RECOVERABLE_ERROR ('.E_RECOVERABLE_ERROR.') Erreur fatale'; break;
            case E_ALL:
            $errname = 'E_ALL ('.E_ALL.')'; break;
        }
        return $errname;
    }

    /**
     * Error management, with display depending of the configuration.
     * A framework event is send onError.
     * @param $p_sNo int Error php number
     * @param $p_sMess string Error message
     * @param $p_sFile string Error file name
     * @param $p_sLine string Error line number
     */
    public static function error_handler($p_sNo, $p_sMess, $p_sFile, $p_sLine)
    {
        // formating message
        $l_sErrMess = preg_replace('/exception \'Exception\' with message /', '', wordwrap($p_sMess,110));
        $l_sErrMess = preg_replace('/Stack trace/', "\nPile d'appels des methodes ", $l_sErrMess);

        // Text message
        self::$m_sTextErrorMessage = "Erreur :\t " . self::getCodeTitle($p_sNo) . "
Fichier : \t" . $p_sFile . " 
Ligne : \t" . $p_sLine . "
Message : \t" . $l_sErrMess . "\n\n";

        if ('cli' != PHP_SAPI) {
        // Html message
        self::$m_sHtmlErrorMessage = "<pre style=\"font-weight:bold;padding:15px;margin:0;border-top:1px dashed red;border-bottom:1px dashed red;\">
    Erreur \t: <span style=\"color:red\">" . self::getCodeTitle($p_sNo) . "</span>
    Fichier \t: <span style=\"color:red\">" . $p_sFile . "</span>
    Ligne \t: <span style=\"color:red\">" . $p_sLine . "</span>
    Message \t: <span style=\"color:red\">" . $l_sErrMess . "</span>
</pre>";
        }

        // onError event emiting
        \Utm\CorePlugin::emit('onError');
        $l_bErrorDisplay = \Utm\Core::$config['error']['display'] ?? false;
        if (true == $l_bErrorDisplay) {
            echo ('cli' != PHP_SAPI) ? self::$m_sHtmlErrorMessage : self::$m_sTextErrorMessage;
        }
    }

    /**
     * accessor
     * @param string $p_sFormat : HTML or TEXT
     * @return string Error message
     */
    public static function getError($p_sFormat = 'html')
    {
        return ('html' == strtolower($p_sFormat) && false == empty(self::$m_sHtmlErrorMessage)) 
               ? self::$m_sHtmlErrorMessage
               : self::$m_sTextErrorMessage;
    }
}
