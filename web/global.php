<?php
// global.php
// put things here that will be included in every file

// __autoload allows classes to be loaded on-demand
function __autoload($class_name) {
    require_once strtolower($class_name) . '.php';
}

// $renderstart can be refercenced at the end of the page to check the render time
$renderstart = microtime(true);

// I live in America/New_York, so all my times should be displayed here
// Since PHP 5.1.0, this is required
date_default_timezone_set('America/New_York');

?>
