<?php
/**
 * Created by PhpStorm.
 * User: karmadon
 * Date: 04.01.16
 * Time: 10:51
 */

function sanitizeString($var)
{
    $var = strip_tags($var);
    $var = htmlentities($var, ENT_QUOTES, 'UTF-8');
    $var = stripslashes($var);
    //$var = mysql_real_escape_string($var);
    return $var;
}

function getParamsFromURI($start = 1)
{

    $params = explode('/', $_SERVER['REQUEST_URI']);

    $returnParams = array(
        'action' => sanitizeString($params[$start]),
        'section' => sanitizeString($params[$start + 1]),
    );

    $i = $start + 2;
    foreach ($params as &$value) {

        if ($params[$i + 1]) {
            $varName = sanitizeString($params[$i]);
            $varValue = sanitizeString($params[$i + 1]);
            $returnParams[$varName] = $varValue;
        }
        $i += 2;

    }
    unset($params);

    return $returnParams;
}

function showMeTheValue($a)
{
    echo '<pre>' . print_r($a, 1) . '</pre>';
}

function getPOSTparams()
{
    $outputArray = array();
    if (isset($_POST)) {
        foreach ($_POST as $key => $value)
        {
            $key = sanitizeString($key);
            $value = sanitizeString($value);
            $outputArray[$key] = $value;
        }
    }

    return $outputArray;

}

function loadClass($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';

    if ($lastNsPos = strripos($className, '\\'))
    {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require $fileName;

}

function destroySession ()
{
    $_SESSION = array ();

    if (session_id () !== '' || isset ($_COOKIE [session_name ()]))
    {
        setcookie (session_name (), '', time () - 2592000, '/');
    }

    session_destroy ();
}