<?php
/*
	$Id$
	
	add fraud checking details to admin / orders.php
	fraudlabs pro and some extra stuff
	
	author: John Ferguson @BrockleyJohn john@sewebsites.net
	
	osCommerce, Open Source E-Commerce Solutions
	http://www.oscommerce.com
	
	Copyright (c) 2017 osCommerce
	
	Released under the GNU General Public License
*/

	class hook_admin_orders_fraud_checking_tab {
			
		function load_language() {
			global $language;
			include_once(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/hooks/admin/' . basename(__FILE__));
		}
	
		function execute() {
			global $oID, $languages_id, $order;
			$this->load_language();
			
			$output = ''; $table = '';
			
			$fraudlabspro_query = tep_db_query("select f.*, countries_name from fraudlabs_pro f left join " . TABLE_COUNTRIES . " c on c.countries_iso_code_2 = f.ip_country left join sew_order_confirm s on s.flp_id = f.flp_id where f.order_id='" . tep_db_input($oID) . "'");

			if (tep_db_num_rows($fraudlabspro_query)){
				$flpResult = tep_db_fetch_array($fraudlabspro_query);

			switch($flpResult['fraudlabspro_status']){
				case 'REVIEW':
					$flpStatus = '<div style="color:#FFCC00;font-size:2.333em;"><b>'.$flpResult['fraudlabspro_status'].'</b></div>';
				break;

				case 'REJECT':
					$flpStatus = '<div style="color:#cc0000;font-size:2.333em;"><b>'.$flpResult['fraudlabspro_status'].'</b></div>';
				break;

				case 'APPROVE':
					$flpStatus = '<div style="color:#336600;font-size:2.333em;"><b>'.$flpResult['fraudlabspro_status'].'</b></div>';
				break;

				default:
					$flpStatus = '-';
			}

			$location = array(_case($flpResult['ip_continent']), $flpResult['countries_name'], _case($flpResult['ip_region']), _case($flpResult['ip_city']));
			$location = array_unique($location);
			
			$table = <<<EOT
		<table width="100%" border="1" bordercolor="AB1B1C" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
		<tr>
			<td class="smallText" colspan="7" bgcolor="#AB1B1C"><a href="//www.fraudlabspro.com/?r=oscommerce" target="_blank"><img src="//www.fraudlabspro.com/images/logo-small.png" width="163" height="20" border="0" align="absMiddle" /></a></td>
		</tr>
		<tr>
			<td rowspan="4" width="180" height="200" valign="center" class="smallText" align="center"><b>FraudLabs Pro Score</b> <a href="javascript:;" title="Risk score, 0 (low risk) - 100 (high risk).">[?]</a><br><img class="img-responsive" alt="" src="//fraudlabspro.hexa-soft.com/images/fraudscore/fraudlabsproscore{$flpResult['fraudlabspro_score']}.png" /></td>
			<td class="smallText" width="150"><b>Transaction ID</b> <a href="javascript:;" title="Unique identifier for a transaction screened by FraudLabs Pro system.">[?]</a></td>
			<td class="smallText" width="110"><a href="//www.fraudlabspro.com/merchant/transaction-details/{$flpResult['fraudlabspro_id']}" target="_blank">{$flpResult['fraudlabspro_id']}</a></td>
			<td class="smallText" width="150"><b>IP Address</b></td>
			<td class="smallText" width="110">{$flpResult['ip_address']}</td>
			<td class="smallText" width="150"><b>IP Net Speed</b> <a href="javascript:;" title="Connection speed.">[?]</a></td>
			<td class="smallText" width="110">{$flpResult['ip_netspeed']}</td>
		</tr>
		<tr>
			<td class="smallText"><b>IP Usage Type</b> <a href="javascript:;" title="Usage type of the IP address. E.g, ISP, Commercial, Residential.">[?]</a></td>
			<td class="smallText">{$flpResult['ip_usage_type']}</td>
			<td class="smallText"><b>IP ISP Name</b> <a href="javascript:;" title="ISP of the IP address.">[?]</a></td>
EOT;
			$table .= '			<td class="smallText" colspan="3">' . _case($flpResult['ip_isp_name']) . "</td>\n";
			$table .= <<<EOT
		</tr>
		<tr>
			<td class="smallText"><b>IP Domain</b> <a href="javascript:;" title="Domain name of the IP address.">[?]</a></td>
			<td class="smallText">{$flpResult['ip_domain']}</td>
			<td class="smallText"><b>IP Location</b> <a href="javascript:;" title="Location of the IP address.">[?]</a></td>
EOT;
			$table .= '			<td colspan="3" class="smallText">' . implode(', ', $location) . '<a href="//www.geolocation.com/' . $flpResult['ip_address'] . '" target="_blank">[Map]</a>' . "</td>\n";	
			$table .= <<<EOT
		</tr>
		<tr>
			<td class="smallText"><b>IP Time Zone</b> <a href="javascript:;" title="Time zone of the IP address.">[?]</a></td>
			<td class="smallText">{$flpResult['ip_timezone']}</td>
			<td class="smallText"><b>IP Distance</b> <a href="javascript:;" title="Distance from IP address to Billing Location.">[?]</a></td>
			<td class="smallText">{$flpResult['distance_in_km']} KM / {$flpResult['distance_in_mile']} Miles</td>
			<td class="smallText" colspan="2"></td>
		</tr>
		<tr>
			<td rowspan="4" height="200" valign="center" class="smallText" align="center"><b>FraudLabs Pro Status</b> <a href="javascript:;" title="FraudLabs Pro status.">[?]</a><br>$flpStatus</td>
			<td class="smallText"><b>IP Latitude</b> <a href="javascript:;" title="Latitude of the IP address.">[?]</a></td>
			<td class="smallText">{$flpResult['ip_latitude']}</td>
			<td class="smallText"><b>IP Longitude</b> <a href="javascript:;" title="Longitude of the IP address.">[?]</a></td>
			<td class="smallText">{$flpResult['ip_longitude']}</td>
			<td class="smallText" colspan="2"></td>
		</tr>
		<tr>
			<td class="smallText"><b>High Risk Country</b> <a href="javascript:;" title="Whether IP address country is in the latest high risk country list.">[?]</a></td>
EOT;
			$table .= '			<td class="smallText">' . ($flpResult['is_high_risk_country'] == 'Y' ? 'Yes' : ($flpResult['is_high_risk_country'] == 'N' ? 'No' : '-')) . '</td>
			<td class="smallText"><b>Free Email</b> <a href="javascript:;" title="Whether e-mail is from free e-mail provider.">[?]</a></td>' . "\n";
			$table .= '			<td class="smallText">' . ($flpResult['is_free_email'] == 'Y' ? 'Yes' : ($flpResult['is_free_email'] == 'N' ? 'No' : '-')) . '</td>
			<td class="smallText"><b>Ship Forward</b> <a href="javascript:;" title="Whether shipping address is a freight forwarder address.">[?]</a></td>
			<td class="smallText">' . ($flpResult['is_address_ship_forward'] == 'Y' ? 'Yes' : ($flpResult['is_address_ship_forward'] == 'N' ? 'No' : '-')) . '</td>
		</tr>
		<tr>
			<td class="smallText"><b>Using Proxy</b> <a href="javascript:;" title="Whether IP address is from Anonymous Proxy Server.">[?]</a></td>
			<td class="smallText">' . ($flpResult['is_proxy_ip_address'] == 'Y' ? 'Yes' : 'No') . '</td>
			<td class="smallText"><b>BIN Found</b> <a href="javascript:;" title="Whether the BIN information matches our BIN list.">[?]</a></td>
			<td class="smallText">' .  ($flpResult['is_bin_found'] == 'Y' ? 'Yes' : ($flpResult['is_bin_found'] == 'N' ? 'No' : '-')) . '</td>
			<td class="smallText" colspan="2"></td>
		</tr>
		<tr>
			<td class="smallText"><b>Email Blacklist</b> <a href="javascript:;" title="Whether the email address is in our blacklist database.">[?]</a></td>
			<td class="smallText">' . ($flpResult['is_email_blacklist'] == 'Y' ? 'Yes' : ($flpResult['is_email_blacklist'] == 'N' ? 'No' : '-')) . '</td>
			<td class="smallText"><b>Credit Card Blacklist</b> <a href="javascript:;" title="Whether the credit card is in our blacklist database.">[?]</a></td>
			<td class="smallText">' . ($flpResult['is_credit_card_blacklist'] == 'Y' ? 'Yes' : ($flpResult['is_credit_card_blacklist'] == 'N' ? 'No' : '-')) . '</td>
			<td class="smallText"><b>Balance</b> <a href="javascript:;" title="Balance of the credits available after this transaction.">[?]</a></td>
			<td class="smallText">' . $flpResult['fraudlabspro_credits'] . ' [<a href="//www.fraudlabspro.com/plan" target="_blank">Upgrade</a>]</td>
		</tr>
		<tr>
			<td class="smallText"><b>Message</b> <a href="javascript:;" title="FraudLabs Pro error message description.">[?]</a></td>
			<td class="smallText" colspan="6">' . ($flpResult['fraudlabspro_error_code'] ? $flpResult['fraudlabspro_error_code'] . ': ' : '') .  $flpResult['fraudlabspro_message'] . '</td>
		</tr>
		<tr>
			<td class="smallText" colspan="7">Please login to <a href="//www.fraudlabspro.com/merchant/login" target="_blank">FraudLabs Pro Merchant Area</a> for more information about this order.</td>
		</tr>' . "\n";
		if($flpResult['fraudlabspro_status'] == 'REVIEW'){ 
			$table2 .= <<<EOT
		<tr>
			<td class="smallText" colspan="7" align="center">
			<form id="flpForm" method="post">
				<input type="hidden" name="flpAction" id="flpAction" value="1" />
				<input type="hidden" name="flpAPIKey" value="{$flpResult['api_key']}" />
				<input type="hidden" name="flpOrderId" value="{$flpResult['order_id']}" />
				<input type="hidden" name="flpId" value="{$flpResult['fraudlabspro_id']}" />
			</form>

			<input type="button" name="flpApprove" value="Approve This Order" onclick="document.getElementById('flpForm').submit();" />
			<input type="button" name="flpReject" value="Reject This Order" onclick="if(confirm('Confirm to reject this order?')){ document.getElementById('flpAction').value=0;document.getElementById('flpForm').submit();} " />
			</td>
		</tr>
EOT;
		}
		$table2 .= '		</table>';
			
			$tab_title = addslashes(sprintf(TAB_FRAUD_CHECKING));
			$tab_link = substr(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params()), strlen($base_url)) . '#section_fraud_checking';

			$heading = ' <h3>' . FRAUD_CHECKING . '</h3>'."\n ";

			$output = <<<EOD
<script><!-- 
$(function() {
  $('#orderTabs ul').first().append('<li><a href="{$tab_link}">{$tab_title}</a></li>');
});
//--></script>

<div id="section_fraud_checking" style="padding: 10px;">
 $heading 
 {$table}
 {$table2}
</div>
EOD;

			}
			
			return $output;
		}
	
	} 

// helper function
    function _case($string){
        $string = ucwords(strtolower($string));
        $string = preg_replace_callback("/( [ a-zA-Z]{1}')([a-zA-Z0-9]{1})/s",create_function('$matches','return $matches[1].strtoupper($matches[2]);'),$string);
        return $string;
    }
