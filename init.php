<?php
const __ROOT_FOLDER__ = __DIR__;
if(!file_exists(__DIR__."/config/settings.php")) die("No settings file in the config folder, please use settings.sample.php as a template");
if(!file_exists(__DIR__."/config/urls.json")) die("No url list in the config folder, please use urls.sample.json as a template");
