<?php

/* Основной класс */

require_once('DBConfig.php');
require_once('JSONErrors.php');

class DeliveryServiceAPI
{
	private static $connect = null;
	private $link;
	
	private const ORDER_ACTIVE = 1;
	private const ORDER_DONE = 2;
	private const ORDER_CANCELLED = 3;
	
    //////////////////////////
	/* Служебные методы класса */
	//////////////////////////
	
	/* Одиночка: если экземпляр класса уже есть - то возвращаем его, если нет - то создаем */
	public static function getInstance()
	{
		if (self::$connect != null)
			return self::$connect;
		else
			return new self;
	}
	
	/* Метод исправляет проблему с кириллицей после json_encode */
	public static function json_fix_cyr($json_str) {
		$cyr_chars = array (
			'\u0430' => 'а', '\u0410' => 'А',
			'\u0431' => 'б', '\u0411' => 'Б',
			'\u0432' => 'в', '\u0412' => 'В',
			'\u0433' => 'г', '\u0413' => 'Г',
			'\u0434' => 'д', '\u0414' => 'Д',
			'\u0435' => 'е', '\u0415' => 'Е',
			'\u0451' => 'ё', '\u0401' => 'Ё',
			'\u0436' => 'ж', '\u0416' => 'Ж',
			'\u0437' => 'з', '\u0417' => 'З',
			'\u0438' => 'и', '\u0418' => 'И',
			'\u0439' => 'й', '\u0419' => 'Й',
			'\u043a' => 'к', '\u041a' => 'К',
			'\u043b' => 'л', '\u041b' => 'Л',
			'\u043c' => 'м', '\u041c' => 'М',
			'\u043d' => 'н', '\u041d' => 'Н',
			'\u043e' => 'о', '\u041e' => 'О',
			'\u043f' => 'п', '\u041f' => 'П',
			'\u0440' => 'р', '\u0420' => 'Р',
			'\u0441' => 'с', '\u0421' => 'С',
			'\u0442' => 'т', '\u0422' => 'Т',
			'\u0443' => 'у', '\u0423' => 'У',
			'\u0444' => 'ф', '\u0424' => 'Ф',
			'\u0445' => 'х', '\u0425' => 'Х',
			'\u0446' => 'ц', '\u0426' => 'Ц',
			'\u0447' => 'ч', '\u0427' => 'Ч',
			'\u0448' => 'ш', '\u0428' => 'Ш',
			'\u0449' => 'щ', '\u0429' => 'Щ',
			'\u044a' => 'ъ', '\u042a' => 'Ъ',
			'\u044b' => 'ы', '\u042b' => 'Ы',
			'\u044c' => 'ь', '\u042c' => 'Ь',
			'\u044d' => 'э', '\u042d' => 'Э',
			'\u044e' => 'ю', '\u042e' => 'Ю',
			'\u044f' => 'я', '\u042f' => 'Я',
	 
			'\r' => '',
			'\n' => '<br />',
			'\t' => ''
		);
	 
		foreach ($cyr_chars as $cyr_char_key => $cyr_char) {
			$json_str = str_replace($cyr_char_key, $cyr_char, $json_str);
		}
		return $json_str;
	}
	
	
	/* Будем вызывать методы нашего API через этот метод. Он проверяети существование метода, парсит JSON с параметрами и возвращает результат в виде массива */
	public function callMethod ($method, $json_params = null) {
		
		if (method_exists($this,$method)) {
			
			if (!empty($json_params))
				$params = json_decode($json_params,true);
			else
				$params = null;
			
			try {
				$res = $this->$method($params);
			} catch (Error $e) {
				$res_err = JSONErrors::METHOD_ERR;
				$err = $e->getMessage();
				foreach ($res_err as $k => $v) { 
					$res[$k] = "$v $method: $err";
				}
			}
			
			if (is_array($res)) {
				$res_arr = $res;				
			} else {
				$res_arr["result"] = $res;
			}			
			
		} else {
			$res_arr = JSONErrors::WRONG_METHOD;
		}
		
		return $res_arr;
	}
	
	/* Приватный конструктор, вызывается из метода getInstance */
	
	private function __construct () {
		$this->link = new MySQLi(DBConfig::DB_HOST, DBConfig::DB_USER, DBConfig::DB_PASS, DBConfig::DB_NAME);
		$this->link->query("SET lc_time_names = 'ru_RU'");
		$this->link->query("SET NAMES 'utf8'");
	}
	
	
	//////////////////////////
	/* Методы с логикой API для взаимодействия */
	//////////////////////////
	
	/* Расчёт стоимости доставки */
	/* Будем считать, что id зоны доставки определяется по адресу где-то в другом месте и к нам приходит уже готовым */
	private function getDeliveryCost($params) {
		$delivery_zone_id = $this->getParam($params,'delivery_zone_id');
		$query = "select cost from delivery_zone where id = $delivery_zone_id";
		$res = $this->select($query);
		return $res[0]["cost"];;
	}
	
	/* Создание заказа */
	private function addOrder ($params) {
		$client_id = $params['client_id'];
	    $delivery_date = $params['delivery_date'];
	    $delivery_time_interval_id = $params['delivery_time_interval_id'];
	    $delivery_zone_id = $params['delivery_zone_id'];
	    $address = $params['address'];
	    $order_comment = $params['order_comment'];
	    $products = $params['products'];
			 
		$query = "insert into delivery_order (client_id, delivery_date, delivery_time_interval_id, delivery_zone_id, address, order_comment)
					values ($client_id, '$delivery_date', $delivery_time_interval_id, $delivery_zone_id, '$address', '$order_comment')";
		$this->link->query($query);
		$order_id = $this->link->insert_id;
		foreach ($products as $p) {
			$query = "insert into delivery_order_product (delivery_order_id, product_id, amount)
						values ($order_id, {$p['product_id']}, {$p['amount']})";
			$this->link->query($query);
		}
		return $order_id;
	}

	/* Получение ID заказов */
	/* По умолчанию возвращает ID всех заказов; также можно добавить ограничения в $params (пример см. ниже в методе getAvailableOrders) */
    private function getOrderIds ($params = null) {
		
		$query_params = $this->getParam($params,'query_params');
		
		$query = "select id from delivery_order where 1 = 1";
		
		if (!empty($query_params))
			foreach ($query_params as $p) {
				$query .= " and {$p['column']} {$p['operator']} {$p['value']} ";
			}
		return $this->select($query);
		
	}
	
	/* Получение подробной информации о заказе */
	private function getOrderInfo ($params) {
		$order_id = $this->getParam($params,'order_id');
		$query = "select * from order_info_vw where order_id = $order_id";
		return $this->select($query);
	}
	
	/* Получение списка товаров из заказа и их количества */
	private function getOrderProducts ($params) {
		$order_id = $this->getParam($params,'order_id');
		$query = "select * from order_products_vw where order_id = $order_id";
		return $this->select($query);
	}
	
	/* Получение суммарной стоимости товаров в заказе */
	private function getOrderCost ($params) {
		$order_id = $this->getParam($params,'order_id');
		$query = "select sum(cost*amount) total_cost from order_products_vw where order_id = $order_id";
		$res = $this->select($query);
		return $res[0]["total_cost"];
	}
	
	/* Метод для курьера - получение ID заказов, которые нужно доставить */
	private function getAvailableOrders () {
		
		$params['query_params'] = [['column' => 'courier_id', 'operator' => 'is', 'value' => 'null'],
								   ['column' => 'order_status_id', 'operator' => '=', 'value' => self::ORDER_ACTIVE],
								   ['column' => 'delivery_date', 'operator' => '>=', 'value' => 'sysdate()']
								  ];
								   
		return $this->getOrderIds($params);
	}	
	
	/* Метод для курьера - указываем, что заказ order_id доставляет курьер courier_id */
	private function takeOrder ($params) {
		$order_id = $this->getParam($params,'order_id');
		$courier_id = $this->getParam($params,'courier_id');
		
		$query = "update delivery_order set courier_id = $courier_id where id = $order_id";
		if ($this->link->query($query))
			return true;
		else
			return false;
	}
	
	/* Подтверждение, что заказ доставлен */
	private function submitOrder ($params) {
		$order_id = $this->getParam($params,'order_id');
		return $this->setOrderStatus ($order_id, self::ORDER_DONE);
	}
	
	/* Отмена заказа */
	private function cancelOrder ($params) {
		$order_id = $this->getParam($params,'order_id');
		return $this->setOrderStatus ($order_id, self::ORDER_CANCELLED);
	}

	
	//////////////////////////
	/* Вспомогательные методы */
	//////////////////////////
	
	/* Установка статуса заказа */
	private function setOrderStatus ($order_id, $order_status_id) {
		$query = "update delivery_order set order_status_id = $order_status_id where id = $order_id";
		if ($this->link->query($query))
			return true;
		else
			return false;
	}
	
	/* Возвращает значение параметра из списка по имени */
	private function getParam ($params, $param_name) {
		if (isset($params[$param_name]) and !empty($params[$param_name]))
			return $params[$param_name];
		else
			return null;
	}
	
	/* Возвращает результат выполнения запроса в виде ассоциативного массива */
	private function select ($query) {
		$res = $this->link->query($query);
		if (!$res)
			return false;
		else {
			$arr = [];
			while (($row = $res->fetch_assoc()) != false) {
			  $arr[] = $row;
			}
			return $arr;
		}
	}

}

?>