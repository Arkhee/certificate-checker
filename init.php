<?php
define("__ROOT_FOLDER__",__DIR__);
if(!file_exists(__DIR__."/settings.php")) die("No settings file in the config folder, please use settings.sample.php as a template");
if(!file_exists(__DIR__."/urls.json")) die("No url list in the config folder, please use urls.sample.json as a template");
if(file_exists(__DIR__."/translations/".CertificateMonitorSettings::$lang.".php"))  require_once(__DIR__."/translations/".CertificateMonitorSettings::$lang.".php");
