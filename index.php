<?php
/**
 * Force the redirect to www/index.php in case the vhost isnt set yet
 */
$redirectQuery = (true == isset($_SERVER['QUERY_STRING']) && '' != $_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';

header('Location:www/index.php'.$redirectQuery) ;
