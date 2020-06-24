<?php

/* Главная страница, на которую приходит GET-запрос с названием метода и параметрами в виде JSON */
error_reporting(E_ALL);
ini_set('display_errors','on');

require_once('JSONErrors.php');
require_once('Ratelimiter.php');
require_once('DeliveryServiceAPI.php');

Ratelimiter::check(10, 1);

if (count($_REQUEST) > 0) {
		
	foreach ($_REQUEST as $method => $params) {
		$ds_api = DeliveryServiceAPI::getInstance();		
		$res_arr = $ds_api->callMethod($method,$params);		
		break;
	}

} else {
	
	$res_arr = JSONErrors::NO_METHOD;
	
}

echo DeliveryServiceAPI::json_fix_cyr(json_encode($res_arr));

?>