<?php
  $log_record .= '
  customer_id registered : ' . (tep_session_is_registered('customer_id')? 'yes' : 'no') . '
  customer_id            : ' . $customer_id . '
  payment registered     : ' . (tep_session_is_registered('payment')? 'yes' : 'no') . '
  payment                : ' . $payment . '
  cart->cartID set       : ' . (isset($cart->cartID)? 'yes' : 'no') . '
  cart->cartID           : ' . $cart->cartID . '
  cartID  registered     : ' . (tep_session_is_registered('cartID')? 'yes' : 'no') . '
  cartID                 : ' . $cartID . '
';
  
// check module installed and set up
	if (! defined('MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY') && strlen(MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY)) exit;

// check if the customer is logged on and in checkout
	if (!tep_session_is_registered('customer_id') && tep_session_is_registered('payment')) {
		exit;
	}

// avoid hack attempts during the checkout procedure by checking the internal cartID
	if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
		if ($cart->cartID != $cartID) {
			exit;
		}
	}
	
// work on the assumption that payment modules which create an order before payment create a session variable called cart_something
// which is a concatenation of cartID-orderID
	$order_id = '';
	foreach ($_SESSION as $key => $value) {
		if (substr($key,0,strlen('cart_')) == 'cart_' && strpos($value,'-')) {
			$order_id = substr($value,strpos($value,'-')+1);
			$log_record .= '     order_id            : ' . $order_id .'
';
			break;
		}
	}  
  
	if (is_object($order)) {
		$order_total = filter_var($order->info['total'],FILTER_SANITIZE_NUMBER_FLOAT);
	} else {
		include_once('includes/classes/order.php');
		$order = new order($order_id); // if order id set, read from database otherwise load from cart
		$order_total = filter_var($order->info['total'],FILTER_SANITIZE_NUMBER_FLOAT);
	}

	$ip = $_SERVER['REMOTE_ADDR'];
	if(isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)){
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	$sql_data_array = array(
		'session_id' => tep_session_id(),
		'payment_method' => $payment,
		'order_id' => (int)$order_id,
		'order_total' => $order_total,
		'ip_address' => $ip,
		'device_checksum' => (isset($_COOKIE['flp_checksum']) ? $_COOKIE['flp_checksum'] : ''),
		'customer_id' => $customer_id,
	);

	tep_db_perform('sew_order_confirm', $sql_data_array);
			