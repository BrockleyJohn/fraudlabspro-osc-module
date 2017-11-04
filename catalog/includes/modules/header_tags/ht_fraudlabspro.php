<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class ht_fraudlabspro {
    var $code = 'ht_fraudlabspro';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = MODULE_HEADER_TAGS_FRAUDLABSPRO_TITLE;
      $this->description = MODULE_HEADER_TAGS_FRAUDLABSPRO_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_FRAUDLABSPRO_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $languages_id, $order_id, $flpro_id;

      if ( basename($PHP_SELF) == 'checkout_confirmation.php' ){
// insert flabs agent script and add ajax submit to confirmation 
        $text = <<<EOT
<form id="ajax_form">
  <input name="action" type="hidden" value="fraudlabspro">
</form>
<script type="text/javascript">
  (function(){
		function s() {
			var e = document.createElement('script');
			e.type = 'text/javascript';
			e.async = true;
			e.src = ('https:' === document.location.protocol ? 'https://' : 'http://') + 'cdn.fraudlabspro.com/s.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(e, s);
		}			  
		(window.attachEvent) ? window.attachEvent('onload', s) : window.addEventListener('load', s, false);
	})();

  $(document).ready(function() {
    $("form[name='checkout_confirmation']").on('submit', function(e){

		var params = $('#ajax_form').serializeArray();
		$.ajax({
			beforeSend : function() {
			//	$("#indic").addClass('loadingActive');
			},
			type: "POST",
			dataType: "html", 
			url: "sew_ajax.php",
			data: params,
			success: function(data) {
			/*	$("#indic").removeClass('loadingActive');
				var dataObj = $.parseJSON(data);
				$("#result").empty();
				$("#result").append(dataObj.msg);
				if (dataObj.status != 'OK') {
					$("#indic").addClass('failed');
				} else {
				  $("#indic").addClass('success');
				} */
			},
			error: function(data) {
			/*	$("#result").empty();
				$("#result").append(data);
				$("#indic").removeClass('loadingActive');
				$("#indic").addClass('failed'); */
			}
		});
	    e.preventDefault();
	});  
  });  
</script>
EOT;
       $oscTemplate->addBlock($text . PHP_EOL, $this->group);
      } elseif ( basename($PHP_SELF) == 'checkout_success.php' ) {
        if (tep_session_is_registered('flpro_id')) {
          $fl_query = tep_db_query('select * from fraudlabs_pro WHERE flp_id = "' . $flpro_id . '"');
          while ($row = tep_db_fetch_array($fl_query)) {
            if ($row['order_id'] == 0 && $order_id > 0) {
              $sql_data_array = array('order_id' => $order_id);
              tep_db_perform('fraudlabs_pro','update','flp_id = "' . $flpro_id . '"');
            }
          }
        }
      }
      
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS');
    }

    function install() {
      $this->checkDB();
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable FraudLabs Pro', 'MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS', 'True', 'Do you want to add FraudLabs Pro™ Credit Card Fraud Prevention to checkout?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('FraudLabs API Key', 'MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY', '', 'Paste the API key from your <a href=\"http://www.fraudlabspro.com/\" target=\"_blank\">FraudLabs Pro account</a>.', '6', '2', '', '')");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_FRAUDLABSPRO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
    }
    
    function checkDB() {
      $query = tep_db_query('SHOW TABLES LIKE "fraudlabs_pro"');
      if (tep_db_num_rows($query) < 1) {
          tep_db_query(
            'CREATE TABLE `fraudlabs_pro` (
                `flp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `api_key` CHAR(32) NULL DEFAULT NULL,
                `is_country_match` CHAR(2) NULL DEFAULT NULL,
                `is_high_risk_country` CHAR(2) NULL DEFAULT NULL,
                `distance_in_km` INT(10) NULL DEFAULT NULL,
                `distance_in_mile` INT(10) NULL DEFAULT NULL,
                `ip_address` VARCHAR(15) NULL DEFAULT NULL,
                `ip_country` CHAR(2) NULL DEFAULT NULL,
                `ip_continent` VARCHAR(10) NULL DEFAULT NULL,
                `ip_region` VARCHAR(50) NULL DEFAULT NULL,
                `ip_city` VARCHAR(50) NULL DEFAULT NULL,
                `ip_latitude` DECIMAL(14,6) NULL DEFAULT \'0.000000\',
                `ip_longitude` DECIMAL(14,6) NULL DEFAULT \'0.000000\',
                `ip_timezone` VARCHAR(6) NULL DEFAULT NULL,
                `ip_elevation` INT(10) NULL DEFAULT NULL,
                `ip_domain` VARCHAR(100) NULL DEFAULT NULL,
                `ip_mobile_mnc` VARCHAR(50) NULL DEFAULT NULL,
                `ip_mobile_mcc` CHAR(2) NULL DEFAULT NULL,
                `ip_mobile_brand` VARCHAR(50) NULL DEFAULT NULL,
                `ip_netspeed` VARCHAR(10) NULL DEFAULT NULL,
                `ip_isp_name` VARCHAR(50) NULL DEFAULT NULL,
                `ip_usage_type` VARCHAR(50) NULL DEFAULT NULL,
                `is_free_email` VARCHAR(2) NULL DEFAULT NULL,
                `is_new_domain_name` VARCHAR(2) NULL DEFAULT NULL,
                `is_proxy_ip_address` VARCHAR(2) NULL DEFAULT NULL,
                `is_bin_found` VARCHAR(2) NULL DEFAULT NULL,
                `is_bin_country_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_bin_name_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_bin_phone_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_bin_prepaid` VARCHAR(2) NULL DEFAULT NULL,
                `is_address_ship_forward` VARCHAR(2) NULL DEFAULT NULL,
                `is_bill_ship_city_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_bill_ship_state_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_bill_ship_country_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_bill_ship_postal_match` VARCHAR(2) NULL DEFAULT NULL,
                `is_ip_blacklist` VARCHAR(2) NULL DEFAULT NULL,
                `is_email_blacklist` VARCHAR(2) NULL DEFAULT NULL,
                `is_credit_card_blacklist` VARCHAR(2) NULL DEFAULT NULL,
                `is_device_blacklist` VARCHAR(2) NULL DEFAULT NULL,
                `is_user_blacklist` VARCHAR(2) NULL DEFAULT NULL,
                `user_order_id` INT(10) NULL DEFAULT NULL,
                `user_order_memo` VARCHAR(100) NULL DEFAULT NULL,
                `fraudlabspro_score` INT(10) NULL DEFAULT \'0\',
                `fraudlabspro_distribution` INT(10) NULL DEFAULT \'0\',
                `fraudlabspro_status` VARCHAR(10) NULL DEFAULT NULL,
                `fraudlabspro_id` VARCHAR(15) NULL DEFAULT NULL,
                `fraudlabspro_version` VARCHAR(5) NULL DEFAULT NULL,
                `fraudlabspro_error_code` VARCHAR(3) NULL DEFAULT NULL,
                `fraudlabspro_message` VARCHAR(50) NULL DEFAULT NULL,
                `fraudlabspro_credits` VARCHAR(10) NULL DEFAULT NULL,
                `order_id` INT(11) NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`flp_id`),
                INDEX `idx_order_id` (`order_id`)
            )'      );
		}
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS', 'MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY', 'MODULE_HEADER_TAGS_FRAUDLABSPRO_SORT_ORDER');
    }
  }
  