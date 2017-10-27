<?php
if(tep_not_null($action) && $action == 'update_order'){
	if($status == 2 || $status == 3){
		$flp_query = tep_db_query("select * from fraudlabs_pro where order_id='" . (int)tep_db_prepare_input($HTTP_GET_VARS['oID']) . "'");
		$flp_result = tep_db_fetch_array($flp_query);

		if($flp_result['fraudlabspro_status'] == 'REVIEW'){
			// Callback to FraudLabs Pro API
			$flpData['key'] = $HTTP_POST_VARS['flpAPIKey'];
			$flpData['action'] = 'APPROVE';
			$flpData['id'] = $HTTP_POST_VARS['flpId'];
			$flpData['format'] = 'json';

			$queries = '';
			foreach($flpData as $key=>$value){
				$queries .= $key . '=' . urlencode($value) . '&';
			}

			$url = 'https://api.fraudlabspro.com/v1/order/feedback?' . rtrim($queries, '&');

			// Trying to connect FraudLabs Pro Web Service for 3 times
			for($i=0; $i<3; $i++){
				$response = @file_get_contents($url);

				if(is_null($json = json_decode($response)) === FALSE){
					tep_db_query("update fraudlabs_pro set `status` = 1,fraudlabspro_status='APPROVE' where order_id = '" . (int)tep_db_prepare_input($HTTP_GET_VARS['oID']) . "'");
					break; // Get out from the FOR loop once attempt succeed
				}
			}
		}
	}
}
if(isset($HTTP_POST_VARS['flpOrderId'])){
	// Callback to FraudLabs Pro API
	$flpData['key'] = $HTTP_POST_VARS['flpAPIKey'];
	$flpData['action'] = ($HTTP_POST_VARS['flpAction'] == 0) ? 'REJECT' : 'APPROVE';
	$flpData['id'] = $HTTP_POST_VARS['flpId'];
	$flpData['format'] = 'json';

	$queries = '';
	foreach($flpData as $key=>$value){
		$queries .= $key . '=' . urlencode($value) . '&';
	}

	$q = 'https://api.fraudlabspro.com/v1/order/feedback?' . rtrim($queries, '&');
	$parse_url = parse_url($q);
	$host = $parse_url['host'];
	$path = $parse_url['path'] . '?' . $parse_url['query'];

	// Trying to connect FraudLabs Pro Web Service for 3 times
	for($i=0; $i<3; $i++){
		$fp = fsockopen($host, 80, $errno, $errstr, 5);
		if($fp){
			$buffer = '';
			fputs($fp, 'GET ' . $path . ' HTTP/1.1' . "\r\n" . 'Host: ' . $host . "\r\n\r\n");
			while(!feof($fp)) $buffer .= fgets($fp, 512);
			fclose($fp);

			list($headers, $output) = explode("\r\n\r\n", $buffer);

			if(is_null($json = json_decode($output)) === FALSE){
				tep_db_query("update fraudlabs_pro set fraudlabspro_status='" . $flpData['action'] . "' where order_id = '" . (int)$HTTP_POST_VARS['flpOrderId'] . "'");
				break; // Get out from the FOR loop once attempt succeed
			}
		}
	}
}
?>