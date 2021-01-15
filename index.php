
<?php
//simply uses the theme HTML
//available themes should be read from folders and presented on a list
//this logic provides the ability to have child themes

$config_filename = "/.htconfig.php";
$config_exists = file_exists(getcwd() . $config_filename);
//error_reporting(0);


if(!$config_exists) header("Location: setup.php");
require_once getcwd() . '/.htconfig.php'; //Database settings
require_once ROOT_DIR . '/vendor/autoload.php'; //Composer autoload
require_once ROOT_DIR .  '/themes//'.DEFAULT_THEME.'/index.php'; //specific theme stuff

?>
