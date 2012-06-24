<?php
/*
 * Controls the Echeck payment
 * */

class AurhorizeNetEcheck{
	
	static $tooltips = array(
		'echeck' => array("Echeck", 'Echeck'),
		'arb' => array('ARB', 'ARB')
	);
	
	static function init(){
		add_filter('gform_field_type_title', array(get_class(), 'gform_field_type_title'));
		add_filter('gform_add_field_buttons', array(get_class(), 'add_new_fieds_button'));
		add_filter('gform_tooltips', array(get_class(), 'gform_tooltips'));
		add_action('gform_editor_js_set_default_values', array(get_class(), 'set_default_values'));
		
		add_filter( "gform_field_input" , array(get_class(), "add_echck_input_fields"), 10, 5 );
		
		add_filter('gform_validation', array(get_class(), 'validate'), 100);
		
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
					'onclick' => "StartAddField('echeck')"
				);
				
				$group['fields'][] = array(
					'class' => 'button',
					'value' => __("ARB", "gravityforms"),
					'onclick' => "StartAddField('arb')"
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
				$routing_no = esc_attr(rgget($field["id"] . ".1",$value));
				$account_no = esc_attr(rgget($field["id"] . ".2",$value));
				$bank_name = esc_attr(rgget($field["id"] . ".3",$value));
				$account_name = esc_attr(rgget($field["id"] . ".4",$value));
				$account_type = esc_attr(rgget($field["id"] . ".5",$value));				
			endif;

			$tabindex = GFCommon::get_tabindex();
			$routing_no_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_1_container'><input type='text' name='input_%d.1' id='%s_1.1' value='%s' {$tabindex} %s /> <label for='%s_1.1' id='{$field_id}_1_label'>" . apply_filters("authorisenet_routing_number_{$form_id}", apply_filters("authorisenet_routing_number",__("Checking Account Routing Number", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $routing_no, $disabled_text, $field_id);
			
			$tabindex = GFCommon::get_tabindex();
			$account_no_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_2_container'><input type='text' name='input_%d.2' id='%s_1.2' value='%s' {$tabindex} %s /> <label for='%s_1.2' id='{$field_id}_2_label'>" . apply_filters("authorisenet_account_number_{$form_id}", apply_filters("authorisenet_acount_number",__("Checking Account Number", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $account_no, $disabled_text, $field_id);
			
			$tabindex = GFCommon::get_tabindex();
			$bank_name_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_3_container'><input type='text' name='input_%d.3' id='%s_1.3' value='%s' {$tabindex} %s /> <label for='%s_1.3' id='{$field_id}_3_label'>" . apply_filters("authorisenet_bank_name_{$form_id}", apply_filters("authorisenet_bank_name",__("Bank Name", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $bank_name, $disabled_text, $field_id);
			
			$tabindex = GFCommon::get_tabindex();
			$account_name_input = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_4_container'><input type='text' name='input_%d.4' id='%s_1.4' value='%s' {$tabindex} %s /> <label for='%s_1.4' id='{$field_id}_4_label'>" . apply_filters("authorisenet_account_name_{$form_id}", apply_filters("authorisenet_account_name",__("Account Name", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $account_name, $disabled_text, $field_id);
			
			//$account_type = sprintf("<span class='ginput_full{$class_suffix}' id='{$field_id}_3_container'><input type='text' name='input_%d.3' id='%s_1.3' value='%s' {$tabindex} %s /> <label for='%s_1.3' id='{$field_id}_3_label'>" . apply_filters("authorisenet_bank_name_{$form_id}", apply_filters("authorisenet_bank_name",__("Customer Bank Name", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $account_no, $disabled_text, $field_id);
			$tabindex = GFCommon::get_tabindex();
			$account_type_input = "<span class='ginput_full{$class_suffix}' id='{$field_id}_5_container' >" .
									
									"<select $disabled_text name='input_{$id}.5' id='{$field_id}_1.5' >" . 
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
			
			$arb_days = esc_attr(rgget($field["id"],$value));			

			$tabindex = GFCommon::get_tabindex();
			$account_type_input = "<span class='ginput_full{$class_suffix}' id='{$field_id}_container' >" .
									
									"<select $disabled_text name='input_{$id}' id='{$field_id}_1' >" . 
									self::get_bank_account_types($account_type) . 									
									"</select>" .
									
									"<label id='{$field_id}_4_label' for='{$field_id}_1'> Account Type </label>" .
									"</span>";
			
			$suffix = "</div>";
			

			
		endif;
		
		return $input;
	}
	
	
//setting the default values
	static function set_default_values(){
		?>
			case "echeck" :
				if(!field.label)
                field.label = "<?php _e("Authorize.net Echeck", "gravityforms"); ?>";
                
                field.inputs = [new Input(field.id + 0.1, '<?php echo esc_js(apply_filters("authorisenet_routing_number_" . rgget("id"), apply_filters("authorisenet_routing_number",__("Checking Account Routing Number", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.2, '<?php echo esc_js(apply_filters("authorisenet_acount_number_" . rgget("id"), apply_filters("authorisenet_acount_number",__("Checking Account Number", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.3, '<?php echo esc_js(apply_filters("authorisenet_bank_name_" . rgget("id"), apply_filters("authorisenet_bank_name",__("Bank Name", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.4, '<?php echo esc_js(apply_filters("authorizenet_account_name_" . rgget("id"), apply_filters("authorizenet_account_name",__("Account Name", "gravityforms"), rgget("id")), rgget("id"))); ?>'),
                            new Input(field.id + 0.5, '<?php echo esc_js(apply_filters("authorizenet_account_type_" . rgget("id"), apply_filters("authorizenet_account_type",__("Account Type", "gravityforms"), rgget("id")), rgget("id"))); ?>')];
                
                break;
		<?php
	}
	
	//retrun the bank account types
	static function get_bank_account_types($type){
		$ac_types = array('CHECKING', 'BUSINESSCHECKING', 'SAVINGS');
		$options = '';
		foreach($ac_types as $ac_type){
			$selected = ($ac_type == $type) ? "selected='selected'" : "";
			$options .= "<option value={$ac_type} {$selected} >{$ac_type}</option>";
		}
		
		return $options;
	}
	
	
	/*
	 * validate results
	 * */
	static function validate($validation_result){
		$form = $validation_result["form"];
		var_dump($form['fields']);
		exit;
	}
	
	
	//js 
	static function authorizenet_gform_editor_js(){
		?>
		
		<script type='text/javascript'>
	
			jQuery(document).ready(function($) {
				//Add all textarea settings to the "TOS" field plus custom "tos_setting"
				// fieldSettings["tos"] = fieldSettings["textarea"] + ", .tos_setting"; // this will show all fields that Paragraph Text field shows plus my custom setting
		
				// from forms.js; can add custom "tos_setting" as well
				fieldSettings["echeck"] = ".label_setting, .description_setting, .admin_label_setting, .size_setting, .default_value_textarea_setting, .error_message_setting, .css_class_setting, .visibility_setting, .echeck_setting"; //this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.
		
				//binding to the load field settings event to initialize the checkbox
				$(document).bind("gform_load_field_settings", function(event, field, form){
					jQuery("#field_tos").attr("checked", field["field_tos"] == true);
					$("#field_tos_value").val(field["tos"]);
				});
			});
		
		</script>
		
		<?php 	
	}
	
	static function authorizenet_advanced_settings($position, $form_id){
		if($position == 50) :
			
		endif;
	}
}
