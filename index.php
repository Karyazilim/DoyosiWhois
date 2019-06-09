<?php

// Kickstart the framework
$f3=require('app/lib/base.php');
$db = new \DB\Jig ('data/',\DB\Jig::FORMAT_JSON);
new \DB\JIG\Session($db);
$f3->set('CACHE',true);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

$f3->route('GET /',	'Home->Display');
$f3->route('GET|POST /whois [ajax]', 'Home->Whois');
$f3->route('GET|POST /bulkwhois [ajax]', 'Home->BulkWhois');
$f3->run();
