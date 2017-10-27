<?php
// FraudLabs license key
$flpData['key'] = 'YOUR_LICENSE_KEY_HERE';

$ip = $_SERVER['REMOTE_ADDR'];

if(isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)){
	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)){
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$flpData['format'] = 'json';
$flpData['ip'] = $ip;
$flpData['first_name'] = $sql_data_array['customers_name'];
$flpData['last_name'] = " ";

$flpData['bill_city'] = $sql_data_array['billing_city'];
$flpData['bill_state'] = $sql_data_array['billing_state'];
$flpData_country_query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $sql_data_array['billing_country'] . "'");
$flpData_country = tep_db_fetch_array($flpData_country_query);
$flpData['bill_zip_code'] = $sql_data_array['billing_postcode'];
$flpData['bill_country'] = $flpData_country['countries_iso_code_2'];
$flpData['user_phone'] = $sql_data_array['customers_telephone'];

$flpData['ship_addr'] = $sql_data_array['delivery_street_address'] . $sql_data_array['delivery_suburb'];
$flpData['ship_city'] = $sql_data_array['delivery_city'];
$flpData['ship_state'] = $sql_data_array['delivery_state'];
$flpData['ship_zip_code'] = $sql_data_array['delivery_postcode'];
$flpData_country_query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $sql_data_array['delivery_country'] . "'");
$flpData_ship_country = tep_db_fetch_array($flpData_country_query);
$flpData['ship_country'] = $flpData_ship_country['countries_iso_code_2'];

$flpData['email'] = $sql_data_array['customers_email_address'];
$flpData['email_domain'] = substr($sql_data_array['customers_email_address'],strpos ($sql_data_array['customers_email_address'], '@') + 1);
//$flpData['email_hash'] = hash_string($sql_data_array['customers_email_address']);

$flpData['bin_no'] = substr(str_replace(array('-',' '), '', $sql_data_array['cc_number']), 0, 6);
$flpData['user_order_id'] = $insert_id;
$flpData['amount'] = $order->info['total'];
$flpData['quantity'] = sizeof($order->products);
$flpData['currency'] = $sql_data_array['currency'];
$flpData['payment_mode'] = $paymentMode;

$flpData['source'] = 'oscommerce';
$flpData['source_version'] = '1.3.1';

$queries = '';
foreach($flpData as $key=>$value){
	$queries .= $key . '=' . urlencode($value) . '&';
}

$url = 'https://api.fraudlabspro.com/v1/order/screen?' . rtrim($queries, '&');

for($i=0; $i<3; $i++){
	$response = @file_get_contents($url);

	if(is_null($json = json_decode($response)) === FALSE){
		$sql_data_array = array(
			'is_country_match' => $json->is_country_match,
			'is_high_risk_country' => $json->is_high_risk_country,
			'distance_in_km' => $json->distance_in_km,
			'distance_in_mile' => $json->distance_in_mile,
			'ip_address' => $flpData['ip'],
			'ip_country' => $json->ip_country,
			'ip_continent' => $json->ip_continent,
			'ip_region' => $json->ip_region,
			'ip_city' => $json->ip_city,
			'ip_latitude' => $json->ip_latitude,
			'ip_longitude' => $json->ip_longitude,
			'ip_timezone' => $json->ip_timezone,
			'ip_elevation' => $json->ip_elevation,
			'ip_domain' => $json->ip_domain,
			'ip_mobile_mnc' => $json->ip_mobile_mnc,
			'ip_mobile_mcc' => $json->ip_mobile_mcc,
			'ip_mobile_brand' => $json->ip_mobile_brand,
			'ip_netspeed' => $json->ip_netspeed,
			'ip_isp_name' => $json->ip_isp_name,
			'ip_usage_type' => $json->ip_usage_type,
			'is_free_email' => $json->is_free_email,
			'is_new_domain_name' => $json->is_new_domain_name,
			'is_proxy_ip_address' => $json->is_proxy_ip_address,
			'is_bin_found' => $json->is_bin_found,
			'is_bin_country_match' => $json->is_bin_country_match,
			'is_bin_name_match' => $json->is_bin_name_match,
			'is_bin_phone_match' => $json->is_bin_phone_match,
			'is_bin_prepaid' => $json->is_bin_prepaid,
			'is_address_ship_forward' => $json->is_address_ship_forward,
			'is_bill_ship_city_match' => $json->is_bill_ship_city_match,
			'is_bill_ship_state_match' => $json->is_bill_ship_state_match,
			'is_bill_ship_country_match' => $json->is_bill_ship_country_match,
			'is_bill_ship_postal_match' => $json->is_bill_ship_postal_match,
			'is_ip_blacklist' => $json->is_ip_blacklist,
			'is_email_blacklist' => $json->is_email_blacklist,
			'is_credit_card_blacklist' => $json->is_credit_card_blacklist,
			'is_device_blacklist' => $json->is_device_blacklist,
			'is_user_blacklist' => $json->is_user_blacklist,
			'user_order_id' => $json->user_order_id,
			'user_order_memo' => $json->user_order_memo,
			'fraudlabspro_score' => $json->fraudlabspro_score,
			'fraudlabspro_distribution' => $json->fraudlabspro_distribution,
			'fraudlabspro_status' => $json->fraudlabspro_status,
			'fraudlabspro_id' => $json->fraudlabspro_id,
			'fraudlabspro_version' => $json->fraudlabspro_version,
			'fraudlabspro_error_code' => $json->fraudlabspro_error_code,
			'fraudlabspro_message' => $json->fraudlabspro_message,
			'fraudlabspro_credits' => $json->fraudlabspro_credits,
			'order_id' => $insert_id,
			'api_key' => $flpData['key']
		);

		tep_db_perform('fraudlabs_pro', $sql_data_array);
		break;
	}
}
?>