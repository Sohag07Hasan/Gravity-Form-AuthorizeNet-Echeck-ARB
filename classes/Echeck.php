<?php
/*
 * Controls the Echeck payment
 * */

class AurhorizeNetEcheck{
	
	static $tooltips = array(
		'echeck' => array("Echeck", 'Echeck'),
		'arb' => array('ARB', 'ARB')
	);
	
	static $echeck_paid = false;
	static $aim_response = false;
		
	static function init(){
		add_filter('gform_field_type_title', array(get_class(), 'gform_field_type_title'));
		add_filter('gform_add_field_buttons', array(get_class(), 'add_new_fieds_button'));
		add_filter('gform_tooltips', array(get_class(), 'gform_tooltips'));
		add_action('gform_editor_js_set_default_values', array(get_class(), 'set_default_values'));
		
		add_filter( "gform_field_input" , array(get_class(), "add_echck_input_fields"), 10, 5 );
		
		add_filter('gform_validation', array(get_class(), 'validate'), 1000);
		
		//add javascript
		add_action( "gform_editor_js", array(get_class(), "authorizenet_gform_editor_js"));
		
	//	add_action( "gform_field_advanced_settings" , array(get_class(), "authorizenet_advanced_settings" , 10, 2 ));
	}
	
	
	/*
	 * add new fields in the gravity form
	 * */
	static function add_new_fieds_button($field_groups){
		 $pricing_fields = array(
                                  array("class"=>"button", "value" => GFCommon::get_field_type_title("echeck"), "onclick" => "StartAddField('echeck');"),
                                  array("class"=>"button", "value" => GFCommon::get_field_type_title("arb"), "onclick" => "StartAddField('arb');"),
                         );
		
		foreach ($field_groups as &$group){
			if( $group["name"] == "pricing_fields" ){
				$group['fields'][] = array(
					'class' => 'button',
					'value' => __("Echeck", "gravityforms"),
					'onclick' => "add_authorize_fields('echeck')"
				);
				
				$group['fields'][] = array(
					'class' => 'button',
					'value' => __("ARB", "gravityforms"),
					'onclick' => "add_authorize_fields('arb')"
				);
			}
		}                         
       
	
		return $field_groups;
	}
	
	static function gform_field_type_title($type){
		switch($type){
			case 'echeck' : 
				return __("Authorize.net Echeck", "gravityforms");
			case 'arb' :
				return __("Authorize.net ARB", 'gravityforms');
		}
	}

	static function gform_tooltips($tooltips){
		$tooltips['form_echeck_fields'] = "<h6>Echeck and ARB</h6>";
		return $tooltips;
	}
	
	
	
	/*
	 * input fields for echeck 
	 * */
	static function add_echck_input_fields($input, $field, $value, $lead_id, $form_id){
		
		$id = $field["id"];
        $field_id = IS_ADMIN || $form_id == 0 ? "input_$id" : "input_" . $form_id . "_$id";
        $form_id = IS_ADMIN && empty($form_id) ? rgget("id") : $form_id;
		
		$size = rgar($field, "size");
        $disabled_text = (IS_ADMIN && RG_CURRENT_VIEW != "entry") ? "disabled='disabled'" : "";
        $class_suffix = RG_CURRENT_VIEW == "entry" ? "_admin" : "";
        $class = $size . $class_suffix;
		
		
		if($field['type'] == 'echeck') :
			$routing_no = '';
			$account_no = '';
			$bank_name = '';
			$account_name = '';
			$account_type = '';
			$recurring_interval = 0;
			
			$prefix = "<div class='ginput_complex ginput_container authorizenet_echeck_fields'>";
			
			if(is_array($value)) :
				$routing_no = esc_attr(rgpost("input_" . $field["id"] . "_1"));
				$account_no = esc_attr(rgpost("input_" . $field["id"] . "_2"));
				$bank_name = esc_attr(rgpost("input_" . $field["id"] . "_3"));
				$account_name = esc_attr(rgpost("input_" . $field["id"] . "_4"));
				$account_type = esc_attr(rgpost("input_" . $field["id"] . "_5"));				
			endif;

			$tabindex = GFCommon::get_tabindex();
			$routing_no_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_1_container'><input type='text' name='input_%d_1' id='%s_1.1' value='%s' {$tabindex} %s /> <label for='%s_1.1' id='{$field_id}_1_label'>" . apply_filters("authorisenet_routing_number_{$form_id}", apply_filters("authorisenet_routing_number",__("Checking Account Routing Number", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $routing_no, $disabled_text, $field_id);
			
			$tabindex = GFCommon::get_tabindex();
			$account_no_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_2_container'><input type='text' name='input_%d_2' id='%s_1.2' value='%s' {$tabindex} %s /> <label for='%s_1.2' id='{$field_id}_2_label'>" . apply_filters("authorisenet_account_number_{$form_id}", apply_filters("authorisenet_acount_number",__("Checking Account Number", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $account_no, $disabled_text, $field_id);
			
			$tabindex = GFCommon::get_tabindex();
			$bank_name_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_3_container'><input type='text' name='input_%d_3' id='%s_1.3' value='%s' {$tabindex} %s /> <label for='%s_1.3' id='{$field_id}_3_label'>" . apply_filters("authorisenet_bank_name_{$form_id}", apply_filters("authorisenet_bank_name",__("Bank Name", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $bank_name, $disabled_text, $field_id);
			
			$tabindex = GFCommon::get_tabindex();
			$account_name_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_4_container'><input type='text' name='input_%d_4' id='%s_1.4' value='%s' {$tabindex} %s /> <label for='%s_1.4' id='{$field_id}_4_label'>" . apply_filters("authorisenet_account_name_{$form_id}", apply_filters("authorisenet_account_name",__("Account Name", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $account_name, $disabled_text, $field_id);
			
			//$account_type = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_3_container'><input type='text' name='input_%d.3' id='%s_1.3' value='%s' {$tabindex} %s /> <label for='%s_1.3' id='{$field_id}_3_label'>" . apply_filters("authorisenet_bank_name_{$form_id}", apply_filters("authorisenet_bank_name",__("Customer Bank Name", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $account_no, $disabled_text, $field_id);
			$tabindex = GFCommon::get_tabindex();
			$account_type_input = "<span class='ginput_full{$class_suffix}' id='{$field_id}_5_container' >" .
									
									"<select $disabled_text name='input_{$id}_5' id='{$field_id}_1.5' >" . 
									self::get_bank_account_types($account_type) . 									
									"</select>" .
									
									"<label id='{$field_id}_4_label' for='{$field_id}_1.5'> Account Type </label>" .
									"</span>";
			
			$suffix = "</div>";
			return $prefix . $routing_no_input . $account_no_input . $bank_name_input . $account_name_input . $account_type_input . $suffix;
			
		endif;
		
		//arb button
		if($field['type'] == 'arb') :

			$prefix = "<div class='ginput_complex ginput_container authorizenet_echeck_fields'>";		
			
			$arb_days = esc_attr(rgpost("input_" . $field["id"] . "_1"));
			$arb_occurances = esc_attr(rgpost("input_" . $field["id"] . "_2"));			
						
			$tabindex = GFCommon::get_tabindex();
			$arb_interval_input = "<span class='ginput_left{$class_suffix}' id='{$field_id}_container' >" .
									
									"<select $disabled_text name='input_{$id}_1' id='{$field_id}.1' >" . 
									self::get_arb_intervals($arb_days) . 									
									"</select>" .
									
									"<label id='{$field_id}_4_label' for='{$field_id}_1'> select a recurring interval </label>" .
									"</span>";
			$arb_occurance_limit = "<span class='ginput_right{$class_suffix}' id='{$field_id}_container' >" .
									
									"<select $disabled_text name='input_{$id}_2' id='{$field_id}.2' >" . 
									self::get_arb_occurances($arb_occurances) . 									
									"</select>" .
									
									"<label id='{$field_id}_5_label' for='{$field_id}_2'> Total occurrances </label>" .
									"</span>";
			
			$suffix = "</div>";
			
			return $prefix . $arb_interval_input . $arb_occurance_limit . $suffix;
			
		endif;
		
		return $input;
	}
	
	
	static function get_arb_occurances($arb_occurances){
		$selected = ($arb_occurances == -1) ? "selected='selected'" : "";
		
		$options = "<option value='-1' {$selected}>Unlimited</option>";
		for($i=2; $i<151; $i++){
			$selected = ($arb_occurances == $i) ? "selected='selected'" : "";
			$options .= "<option value='{$i}' {$selected}>{$i}</option>";
		}
		
		return $options;
	}
	
//setting the default values
	static function set_default_values(){
		?>
			case "echeck" :
				if(!field.label)
                field.label = "<?php _e("Authorize.net Echeck", "gravityforms"); ?>";
                
                field.inputs = [new Input(field.id + 0.1, '<?php echo esc_js(apply_filters("authorisenet_routing_number_" . rgget("id"), apply_filters("authorisenet_routing_number",__("Checking Account Routing Number", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                			new Input(field.id + 0.2, '<?php echo esc_js(apply_filters("authorisenet_account_number_" . rgget("id"), apply_filters("authorisenet_account_number",__("Checking Account Number", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.3, '<?php echo esc_js(apply_filters("authorisenet_bank_name_" . rgget("id"), apply_filters("authorisenet_bank_name",__("Bank Name", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.4, '<?php echo esc_js(apply_filters("authorizenet_account_name_" . rgget("id"), apply_filters("authorizenet_account_name",__("Account Name", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.5, '<?php echo esc_js(apply_filters("authorizenet_account_type_" . rgget("id"), apply_filters("authorizenet_account_type",__("Account Type", "gravityforms"), rgget("id")), rgget("id"))); ?>')];
                
                break;
                
                
           case "arb" :
           		if(!field.label) {
           			field.label = "<?php _e("Is this a recurring gift?", "gravityfroms") ?>"
           		} 
           		
           		field.inputs = [new Input(field.id + 0.1, '<?php echo esc_js(apply_filters("authorisenet_arb_intervals" . rgget("id"), apply_filters("authorisenet_arb_intervals",__("Recurring interval", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
           						new Input(field.id + 0.2, '<?php echo esc_js(apply_filters("authorisenet_arb_occurances" . rgget("id"), apply_filters("authorisenet_arb_occurances",__("Total Occurances", "gravityforms"), rgget("id")), rgget("id"))); ?>')]
           		
           	   break;
		<?php
	}
	
	//retrun the bank account types
	static function get_bank_account_types($type){
		$ac_types = array('CHECKING', 'BUSINESSCHECKING', 'SAVINGS');
		$options = '';
		foreach($ac_types as $ac_type){
			$selected = ($ac_type == $type) ? "selected='selected'" : "";
			$options .= "<option value='{$ac_type}' {$selected} >{$ac_type}</option>";
		}
		
		return $options;
	}
	
	
	/*
	 * validate results
	 * */
	static function validate($validation_result){
		
		$is_valid = $validation_result['is_valid'];
		$echeck_verified = false;
		$recurring_interval = 0;
		
		// 2 - Get the form object from the validation result
		$form = $validation_result["form"];		
		// 3 - Get the current page being validated
		$current_page = rgpost('gform_source_page_number_' . $form['id']) ? rgpost('gform_source_page_number_' . $form['id']) : 1;
		//loop thouth the form fields
		
		$creditcard_verified = false;
		
		
		foreach ($form['fields'] as &$field){
			// 6 - Get the field's page number
			$field_page = $field['pageNumber'];
			
			// 7 - Check if the field is hidden by GF conditional logic
			$is_hidden = RGFormsModel::is_field_hidden($form, $field, array());

			// 8 - If the field is not on the current page OR if the field is hidden, skip it
			if($field_page != $current_page || $is_hidden) continue;
			
			//now original validation occurs
			switch(RGFormsModel::get_input_type($field)){
				case 'echeck' :					
					if($field["isRequired"]) :
					
						$routing_number = trim($_POST["input_" . $field["id"] . "_1"]);
						$account_number = trim($_POST["input_" . $field["id"] . "_2"]);
						$bank_name = trim($_POST["input_" . $field["id"] . "_3"]);
						$account_name = trim($_POST["input_" . $field["id"] . "_4"]);
						$account_type = trim($_POST["input_" . $field["id"] . "_5"]);
						if(empty($routing_number) || empty($account_name) || empty($bank_name) || empty($account_name)){
							$field["failed_validation"] = true;
							$field["validation_message"] = empty($field["errorMessage"]) ? __("Echeck field is required. There should not be any empty field.", "gravityforms") : $field["errorMessage"];
							$is_valid = false;
						}
						else{
							if($is_valid){
								$response = self::make_echeck_payment($validation_result);
								if($response){
									$is_valid = true;
									self::start_echeck_subscription($validation_result);
								}
								else{
									$field["failed_validation"] = true;
									$field["validation_message"] = self::get_echeck_error_message();
									$is_valid = false;
								}
							}
						}
						
					endif;
					
				break;
								
				case 'creditcard' :
					if($field["isRequired"]) :
						if($is_valid){
							self::start_creditcard_subscription($validation_result);
						}						
					endif;
				break;
					
			}
		}
				
		
		$validation_result['form'] = $form;
		$validation_result['is_valid'] = $is_valid;
		return $validation_result;
	}	
	
	
	/*
	 * return echeck error message
	 * */
	static function get_echeck_error_message(){
		if(is_object(self::$aim_response)){
			return self::$aim_response->response_reason_text;
		}
	}
	
	//make the payment
	private static function make_echeck_payment($validation_result){
		$form_data = self::get_form_data($validation_result);
				
		
		extract($form_data, EXTR_SKIP);
		
		if($amount == 0) return true;
		
		$sale = self::get_aim();
		$sale->amount = $amount;
		$sale->setECheck($bank_aba_code, $bank_acct_num, $bank_acct_type, $bank_name, $bank_acct_name, $echeck_type);
		$response  = $sale->authorizeAndCapture();
		
		self::$aim_response = $response;
		if($response->approved == true){
			return true;
		}

		return false;
		
	}
	
	
	/*
	 * start reucrring subscription
	 * */
	static function start_echeck_subscription($validation_result){
		$arb_info = self::get_arb_info($validation_result);
		$form_data = self::get_form_data($validation_result);
				
		
		extract($arb_info, EXTR_SKIP);
		extract($form_data, EXTR_SKIP);
		
		if($interval_length == 0) return;
		if($amount == 0) return;
		
		$subscription_start_date = gmdate("Y-m-d", strtotime("+ " . $interval_length . $interval_unit));
		$occurances = ($occurances > 0) ? $occurances - 1 : 9999; //first time one payment is done
		
		$subscription = self::get_subscription();
		
		$subscription->intervalLength = $interval_length;
		$subscription->intervalUnit = $interval_unit;
		$subscription->startDate = $subscription_start_date;
		$subscription->totalOccurrences = $occurances;
		$subscription->amount = $amount;
		$subscription->bankAccountAccountType = $bank_acct_type;
		$subscription->bankAccountAccountNumber = $bank_acct_num;
		$subscription->bankAccountRoutingNumber = $bank_aba_code;
		$subscription->bankAccountEcheckType = $echeck_type;
		$subscription->bankAccountBankName = $bank_name;
		$subscription->bankAccountNameOnAccount = $bank_acct_name;
		$subscription->orderInvoiceNumber = self::$aim_response->invoice_number;
		
		$arb = self::get_arb();
		$arb->createSubscription($subscription);
		
	}
	
	
	//
	static function start_creditcard_subscription($validation_result){
		$form_data = self::get_creditcard_info($validation_result);
		$arb_fields = self::get_arb_info($validation_result);
		$amount_fields = self::get_amount_info($validation_result["form"]);
		
		
		extract($arb_fields, EXTR_SKIP);
		extract($amount_fields, EXTR_SKIP);
		
		if($amount == 0) return;
		if($interval_length == 0) return;
		
		$subscription_start_date = gmdate("Y-m-d", strtotime("+ " . $interval_length . $interval_unit));
		$occurances = ($occurances > 0) ? $occurances - 1 : 9999;
		
		$subscription = self::get_subscription();
		$subscription->intervalLength = $interval_length;
		$subscription->intervalUnit = $interval_unit;
		$subscription->startDate = $subscription_start_date;
		$subscription->totalOccurrences = $occurances;
		$subscription->amount = $amount;
		
		$exp_date = $form_data["expiration_date"][1] . "-" . str_pad($form_data["expiration_date"][0], 2, "0", STR_PAD_LEFT);
		
		$subscription->name = $form_data["first_name"] . " " . $form_data["last_name"];
		$subscription->creditCardCardNumber = $form_data["card_number"];
        $exp_date = $form_data["expiration_date"][1] . "-" . str_pad($form_data["expiration_date"][0], 2, "0", STR_PAD_LEFT);
        $subscription->creditCardExpirationDate = $exp_date;
        $subscription->creditCardCardCode = $form_data["security_code"];
        var_dump($subscription);
        $arb = self::get_arb();
		$arb->createSubscription($subscription);
		
		
	}
	
	static function get_creditcard_info($validation_result){
		$form_data = array();
		$form = $validation_result["form"];
		
		$card_field = self::get_creditcard_field($form);
        $form_data["card_number"] = rgpost("input_{$card_field["id"]}_1");
        $form_data["expiration_date"] = rgpost("input_{$card_field["id"]}_2");
        $form_data["security_code"] = rgpost("input_{$card_field["id"]}_3");
        $form_data["card_name"] = rgpost("input_{$card_field["id"]}_5");
        
		$names = explode(" ", $form_data["card_name"]);
        $form_data["first_name"] = rgar($names,0);
        $form_data["last_name"] = "";
        if(count($names) > 0){
            unset($names[0]);
            $form_data["last_name"] = implode(" ", $names);
        }
		
        return $form_data;
	}
	
	static function get_creditcard_field($form){
		$fields = GFCommon::get_fields_by_type($form, array("creditcard"));
        return empty($fields) ? false : $fields[0];
	}
	
	/*
	 * return form data basically price
	 * */
	static function get_form_data($validation_result){
		$form_data = array();
		$form = $validation_result['form'];		

        $echeck_field = self::get_echeck_fields($form);
       
        $form_data['bank_aba_code'] = rgpost("input_{$echeck_field["id"]}_1");
        $form_data['bank_acct_num'] = rgpost("input_{$echeck_field["id"]}_2");
        $form_data['bank_name'] = rgpost("input_{$echeck_field["id"]}_3");
        $form_data['bank_acct_name'] = rgpost("input_{$echeck_field["id"]}_4");
        $form_data['bank_acct_type'] = rgpost("input_{$echeck_field["id"]}_5");
        $form_data['echeck_type'] = "WEB";

        $amount_info = self::get_amount_info($form);
        $form_data['amount'] = $amount_info['amount'];
        
        return $form_data;        
	}
	
	
	static function get_echeck_fields($form){
		$fields = GFCommon::get_fields_by_type($form, array("echeck"));
        return empty($fields) ? false : $fields[0];
	}
	
	static function get_arb_info($validation_result){
		$form_data = array();
		
		$form = $validation_result["form"];
		$recurring_field = self::get_arb_field($form);
		
		$form_data['interval_length'] = rgpost("input_{$recurring_field["id"]}_1");
        $form_data['occurances'] = rgpost("input_{$recurring_field["id"]}_2");
        $form_data["interval_unit"] = "days";
               
        return $form_data;
	}
	
	static function get_amount_info($form){
		$form_data = array();
		$tmp_lead = RGFormsModel::create_lead($form);
        $products = GFCommon::get_product_fields($form, $tmp_lead);
        $order_info = self::get_order_info($products);
        $form_data['amount'] = $order_info['amount'];
        return $form_data;
	}
	
	static function get_arb_field($form){
		$fields = GFCommon::get_fields_by_type($form, array("arb"));
        return empty($fields) ? false : $fields[0];
	}
	
	//return the aim object
	static function get_aim(){
		$settings = self::get_authorizenet_options();
		if(!class_exists('AuthorizeNetRequest')){
            require_once self::get_base_dir() . "/anet_php_sdk/AuthorizeNet.php";
		}
		
		$is_sandbox = $settings['mode'] == 'test';
		
		$aim = new AuthorizeNetAIM($settings["api_login_id"], $settings["trans_key"]);
		$aim->setSandbox($is_sandbox);
		return $aim;
	}
	
	
	static function get_subscription(){
		
		if(!class_exists('AuthorizeNetRequest')){
            require_once self::get_base_dir() . "/anet_php_sdk/AuthorizeNet.php";
		}
						
		$subscription = new AuthorizeNet_Subscription();
		
		return $subscription;
	}
	
	static function get_authorizenet_options(){
		return AuthorizeNetSettings::get_authorizenet_options();
	}
	
	//js 
	static function authorizenet_gform_editor_js(){
		?>
		
		<script type='text/javascript'>
	
			jQuery(document).ready(function($) {
				//Add all default settings"
				
				fieldSettings["echeck"] = ".label_setting, .description_setting, .admin_label_setting, .size_setting, .error_message_setting, .css_class_setting, .visibility_setting, .conditional_logic_field_setting, .rules_setting"; //this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.
				fieldSettings["arb"] = ".label_setting, .description_setting, .admin_label_setting, .size_setting, .error_message_setting, .css_class_setting, .visibility_setting, .conditional_logic_field_setting, .rules_setting";

											
			});

			//allow the echeck and ARB to be added once in a form
			function add_authorize_fields(type){
				switch(type){
					case "echeck" :
						 if(GetFieldsByType(["echeck"]).length > 0){
				                alert("<?php _e("Only one Echeck field can be added to the form", "gravityforms") ?>");
				                return false;
				            }
				            else{
								StartAddField("echeck");
							}
					break;
					case "arb" :
						 if(GetFieldsByType(["arb"]).length > 0){
				                alert("<?php _e("Only one ARB field can be added to the form", "gravityforms") ?>");
				                return false;
				            }
				            else{
								StartAddField("arb");
							}
					break;
						 
				}
			}
		</script>
		
		<?php 	
	}
	
	static function authorizenet_advanced_settings($position, $form_id){
		if($position == 50) :
			
		endif;
	}
	
	
	/*
	 * get arb intervals
	 * */
	static function get_arb_intervals($selected_interval){
		$intervals = array(0=>'No, one-time only', 7=>'Weekly', 30=>'Monthly', 365=>'Yearly');
		$options = '';
		foreach($intervals as $key=>$interval){
			$selected = ($selected_interval == $key) ? "selected='selected'" : "";
			$options .= "<option value={$key} {$selected} >{$interval}</option>";
		}
		
		return $options;
	}
	
	//return the base dir
	static function get_base_dir(){
		return GfAuthorizeNetEcheckARB;
	}
	
	private static function get_order_info($products){
        $amount = 0;
        $line_items = array();
        $item = 1;
        foreach($products["products"] as $field_id => $product)
        {            
            $quantity = $product["quantity"] ? $product["quantity"] : 1;
            $product_price = GFCommon::to_number($product['price']);

            $options = array();
            if(is_array($product["options"])){
                foreach($product["options"] as $option){
                    $options[] = $option["option_label"];
                    $product_price += $option["price"];
                }
            }

            $amount += $product_price * $quantity;

            $description = "";
            if(!empty($options))
                $description = __("options: ", "gravityformsauthorizenet") . " " . implode(", ", $options);

            if($product_price >= 0){
                $line_items[] = array("item_id" =>'Item ' . $item, "item_name"=>$product["name"], "item_description" =>$description, "item_quantity" =>$quantity, "item_unit_price"=>$product["price"], "item_taxable"=>"Y");
                $item++;
            }
        }

        if(!empty($products["shipping"]["name"]) && !is_numeric($recurring_field)){
            $line_items[] = array("item_id" =>'Item ' . $item, "item_name"=>$products["shipping"]["name"], "item_description" =>"", "item_quantity" =>1, "item_unit_price"=>$products["shipping"]["price"], "item_taxable"=>"Y");
            $amount += $products["shipping"]["price"];
        }

        return array("amount" => $amount, "line_items" => $line_items);
    }
    
    //rgrequest
    static function rgrequest($name){
    	if(isset($_POST[$name]))
        return $do_stripslashes ? stripslashes_deep($_POST[$name]) : $_POST[$name];

    return "";
    }
    
    //return the areb object
	private static function get_arb(){
        $settings = self::get_authorizenet_options();
        $is_sandbox = $settings['mode'] == "test";
		
		if(!class_exists('AuthorizeNetRequest')){
            require_once self::get_base_dir() . "/anet_php_sdk/AuthorizeNet.php";
		}
        
        $arb = new AuthorizeNetARB($settings["api_login_id"], $settings["trans_key"]);
        $arb->setSandbox($is_sandbox);
        return $arb;
    }
}
