	  <tr>
		<td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
	  </tr>
      <tr>
		<td>
		<?php
		$fraudlabspro_query = tep_db_query("select * from fraudlabs_pro where order_id='" . tep_db_input($oID) . "'");

		if (tep_db_num_rows($fraudlabspro_query)){
			$flpResult = tep_db_fetch_array($fraudlabspro_query);

			/*if($flpResult['fraudlabspro_score'] > 80){
				$flpScore = '<div style="color:#FF0000;font-size:30px;"><b>'.$flpResult['fraudlabspro_score'].'</b></div>';
			}
			elseif($flpResult['fraudlabspro_score'] > 60){
				$flpScore = '<div style="color:#FFCC00;font-size:30px;"><b>'.$flpResult['fraudlabspro_score'].'</b></div>';
			}
			elseif($flpResult['fraudlabspro_score'] > 40){
				$flpScore = '<div style="color:#ffc166;font-size:30px;"><b>'.$flpResult['fraudlabspro_score'].'</b></div>';
			}
			elseif($flpResult['fraudlabspro_score'] > 20){
				$flpScore = '<div style="color:#66CC66;font-size:30px;"><b>'.$flpResult['fraudlabspro_score'].'</b></div>';
			}
			else{
				$flpScore = '<div style="color:#33CC00;font-size:30px;"><b>'.$flpResult['fraudlabspro_score'].'</b></div>';
			}*/

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

			$countryName = '';
			$countries_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $flpResult['ip_country'] . "'");
			if (tep_db_num_rows($countries_query)){
				$countries = tep_db_fetch_array($countries_query);
				$countryName = $countries['countries_name'];
			}

			$location = array(_case($flpResult['ip_continent']), $countryName, _case($flpResult['ip_region']), _case($flpResult['ip_city']));
			$location = array_unique($location);
		?>
		<table width="100%" border="1" bordercolor="AB1B1C" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
		<tr>
			<td class="smallText" colspan="7" bgcolor="#AB1B1C"><a href="http://www.fraudlabspro.com/?r=oscommerce" target="_blank"><img src="http://www.fraudlabspro.com/images/logo-small.png" width="163" height="20" border="0" align="absMiddle" /></a></td>
		</tr>
		<tr>
			<td rowspan="4" width="180" height="200" valign="center" class="smallText" align="center"><b>FraudLabs Pro Score</b> <a href="javascript:;" title="Risk score, 0 (low risk) - 100 (high risk).">[?]</a><br><img class="img-responsive" alt="" src="//fraudlabspro.hexa-soft.com/images/fraudscore/fraudlabsproscore<?php echo $flpResult['fraudlabspro_score']; ?>.png" /></td>
			<td class="smallText" width="150"><b>Transaction ID</b> <a href="javascript:;" title="Unique identifier for a transaction screened by FraudLabs Pro system.">[?]</a></td>
			<td class="smallText" width="110"><a href="http://www.fraudlabspro.com/merchant/transaction-details/<?php echo $flpResult['fraudlabspro_id']; ?>" target="_blank"><?php echo $flpResult['fraudlabspro_id']; ?></a></td>
			<td class="smallText" width="150"><b>IP Address</b></td>
			<td class="smallText" width="110"><?php echo $flpResult['ip_address']; ?></td>
			<td class="smallText" width="150"><b>IP Net Speed</b> <a href="javascript:;" title="Connection speed.">[?]</a></td>
			<td class="smallText" width="110"><?php echo $flpResult['ip_netspeed']; ?></td>
		</tr>
		<tr>
			<td class="smallText"><b>IP Usage Type</b> <a href="javascript:;" title="Usage type of the IP address. E.g, ISP, Commercial, Residential.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['ip_usage_type']; ?></td>
			<td class="smallText"><b>IP ISP Name</b> <a href="javascript:;" title="ISP of the IP address.">[?]</a></td>
			<td class="smallText" colspan="3"><?php echo _case($flpResult['ip_isp_name']); ?></td>
		</tr>
		<tr>
			<td class="smallText"><b>IP Domain</b> <a href="javascript:;" title="Domain name of the IP address.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['ip_domain']; ?></td>
			<td class="smallText"><b>IP Location</b> <a href="javascript:;" title="Location of the IP address.">[?]</a></td>
			<td colspan="3" class="smallText"><?php echo implode(', ', $location) ?> <a href="http://www.geolocation.com/<?php echo $flpResult['ip_address']; ?>" target="_blank">[Map]</a></td>
		</tr>
		<tr>
			<td class="smallText"><b>IP Time Zone</b> <a href="javascript:;" title="Time zone of the IP address.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['ip_timezone']; ?></td>
			<td class="smallText"><b>IP Distance</b> <a href="javascript:;" title="Distance from IP address to Billing Location.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['distance_in_km']; ?> KM / <?php echo $flpResult['distance_in_mile']; ?> Miles</td>
			<td class="smallText" colspan="2"></td>
		</tr>
		<tr>
			<td rowspan="4" height="200" valign="center" class="smallText" align="center"><b>FraudLabs Pro Status</b> <a href="javascript:;" title="FraudLabs Pro status.">[?]</a><br><?php echo $flpStatus; ?></td>
			<td class="smallText"><b>IP Latitude</b> <a href="javascript:;" title="Latitude of the IP address.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['ip_latitude']; ?></td>
			<td class="smallText"><b>IP Longitude</b> <a href="javascript:;" title="Longitude of the IP address.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['ip_longitude']; ?></td>
			<td class="smallText" colspan="2"></td>
		</tr>
		<tr>
			<td class="smallText"><b>High Risk Country</b> <a href="javascript:;" title="Whether IP address country is in the latest high risk country list.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_high_risk_country'] == 'Y') ? 'Yes' : (($flpResult['is_high_risk_country'] == 'N') ? 'No' : '-'); ?></td>
			<td class="smallText"><b>Free Email</b> <a href="javascript:;" title="Whether e-mail is from free e-mail provider.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_free_email'] == 'Y') ? 'Yes' : (($flpResult['is_free_email'] == 'N') ? 'No' : '-'); ?></td>
			<td class="smallText"><b>Ship Forward</b> <a href="javascript:;" title="Whether shipping address is a freight forwarder address.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_address_ship_forward'] == 'Y') ? 'Yes' : (($flpResult['is_address_ship_forward'] == 'N') ? 'No' : '-'); ?></td>
		</tr>
		<tr>
			<td class="smallText"><b>Using Proxy</b> <a href="javascript:;" title="Whether IP address is from Anonymous Proxy Server.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_proxy_ip_address'] == 'Y') ? 'Yes' : 'No'; ?></td>
			<td class="smallText"><b>BIN Found</b> <a href="javascript:;" title="Whether the BIN information matches our BIN list.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_bin_found'] == 'Y') ? 'Yes' : (($flpResult['is_bin_found'] == 'N') ? 'No' : '-'); ?></td>
			<td class="smallText" colspan="2"></td>
		</tr>
		<tr>
			<td class="smallText"><b>Email Blacklist</b> <a href="javascript:;" title="Whether the email address is in our blacklist database.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_email_blacklist'] == 'Y') ? 'Yes' : (($flpResult['is_email_blacklist'] == 'N') ? 'No' : '-'); ?></td>
			<td class="smallText"><b>Credit Card Blacklist</b> <a href="javascript:;" title="Whether the credit card is in our blacklist database.">[?]</a></td>
			<td class="smallText"><?php echo ($flpResult['is_credit_card_blacklist'] == 'Y') ? 'Yes' : (($flpResult['is_credit_card_blacklist'] == 'N') ? 'No' : '-'); ?></td>
			<td class="smallText"><b>Balance</b> <a href="javascript:;" title="Balance of the credits available after this transaction.">[?]</a></td>
			<td class="smallText"><?php echo $flpResult['fraudlabspro_credits']; ?> [<a href="http://www.fraudlabspro.com/plan" target="_blank">Upgrade</a>]</td>
		</tr>
		<tr>
			<td class="smallText"><b>Message</b> <a href="javascript:;" title="FraudLabs Pro error message description.">[?]</a></td>
			<td class="smallText" colspan="6"><?php echo (($flpResult['fraudlabspro_error_code']) ? $flpResult['fraudlabspro_error_code'] . ': ' : '') .  $flpResult['fraudlabspro_message']; ?></td>
		</tr>
		<tr>
			<td class="smallText" colspan="7">Please login to <a href="https://www.fraudlabspro.com/merchant/login" target="_blank">FraudLabs Pro Merchant Area</a> for more information about this order.</td>
		</tr>
		<?php if($flpResult['fraudlabspro_status'] == 'REVIEW'){ ?>
		<tr>
			<td class="smallText" colspan="7" align="center">
			<form id="flpForm" method="post">
				<input type="hidden" name="flpAction" id="flpAction" value="1" />
				<input type="hidden" name="flpAPIKey" value="<?php echo $flpResult['api_key']; ?>" />
				<input type="hidden" name="flpOrderId" value="<?php echo $flpResult['order_id']; ?>" />
				<input type="hidden" name="flpId" value="<?php echo $flpResult['fraudlabspro_id']; ?>" />
			</form>

			<input type="button" name="flpApprove" value="Approve This Order" onclick="document.getElementById('flpForm').submit();" />
			<input type="button" name="flpReject" value="Reject This Order" onclick="if(confirm('Confirm to reject this order?')){ document.getElementById('flpAction').value=0;document.getElementById('flpForm').submit();} " />
			</td>
		</tr>
		<?php } ?>
		</table>
		<?php
		}
		function _case($string){
			$string = ucwords(strtolower($string));
			$string = preg_replace_callback("/( [ a-zA-Z]{1}')([a-zA-Z0-9]{1})/s",create_function('$matches','return $matches[1].strtoupper($matches[2]);'),$string);
			return $string;
		}
		?>
		<td>
	  </tr>