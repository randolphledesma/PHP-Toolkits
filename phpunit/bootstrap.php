<?php

$_SESSION = [];
$_COOKIE = [];
$GLOBALS['test'] = 'test';

PHPUnit_Framework_Error_Deprecated::$enabled = false;

function error_level_tostring($intval, $separator = ',')
{
    $errorlevels = array(
        E_ALL => 'E_ALL',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_DEPRECATED => 'E_DEPRECATED',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_STRICT => 'E_STRICT',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_NOTICE => 'E_NOTICE',
        E_PARSE => 'E_PARSE',
        E_WARNING => 'E_WARNING',
        E_ERROR => 'E_ERROR');
    $result = '';
    foreach($errorlevels as $number => $name)
    {
        if (($intval & $number) == $number) {
            $result .= ($result != '' ? $separator : '').$name; }
    }
    return $result;
}

fwrite(STDERR, 'Loading bootstrap file.' . PHP_EOL);
fwrite(STDERR, 'PHP Config File: ' . get_cfg_var('cfg_file_path') . PHP_EOL);
fwrite(STDERR, 'PHP Error Reporting: ' . error_level_tostring(error_reporting(), ',') . PHP_EOL);

include('mysql-shim.php');

/**
* Error handler, passes flow over the exception logger with new ErrorException.
*/
function log_error( $num, $str, $file, $line, $context = null )
{
    log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
}

/**
* Uncaught exception handler.
*/
function log_exception( Exception $e )
{
    $message = "Type: " . get_class( $e ) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
    if ($_SERVER['LOG_EXCEPTION']=='yes') {
        fwrite(STDERR, $message . PHP_EOL);
        //fwrite(STDERR, var_export(debug_backtrace(), true) . PHP_EOL);
    }
}

/**
* Checks for a fatal error, work around for set_error_handler not working on fatal errors.
*/
function check_for_fatal()
{
    $error = error_get_last();
    if (!is_null($error)) {
        $message = "Type: " . $error["type"] . "; Message: {$error["message"]}; File: {$error["file"]}; Line: {$error["line"]};";
        fwrite(STDERR, $message . PHP_EOL);
    }
    if ( $error["type"] == E_ERROR ) {
        log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
    }
}

register_shutdown_function( "check_for_fatal" );
set_error_handler( "log_error" );
set_exception_handler( "log_exception" );

fwrite(STDERR, 'Current Directory: ' . getcwd() . PHP_EOL);

