<?php
/*
 * adds a way to add differnet gravity form fields
 * */
if (class_exists ( 'authorizenet_GF_Cfields' ))
	return;

class authorizenet_GF_Cfields {
		
	static $tooltips = array (
		'first_name' => array ("First Name", "This field is mendatory so make it Required and pushes to authoirze.net" ), 
		'last_name' => array ("Last Name", "This field is mendatory so make it Required and pushes to authorize.net" ), 
	//	'donation_purpose' => array ("Purpose Of Donation", "This field is not required, This informatin will be pushed to authorize.net" ), 
	//	'donor_ip' => array ("IP address", "This field accepts ip address"),
		'zip' => array("Zip Code", "This field accepts Zip code"),
		'email' => array('Email', "Email address of the donor"),
		'state' => array("State", "State"),
		'address' => array('Address', 'address of the donor'),
		'country' => array('Country', 'country of the donor'),
		'phone' => array('Phone', 'Phone number'),
		'company' => array('Company', 'Name of the company'),
		'city' => array('City', 'Name of the city'),
		'fax' => array('Fax', 'Fax number')
	);
		
	//static $tooltips = array ('don_purpose' => array ("Purpose Of Donation", "This field is not required, This informatin will be pushed to authorize.net" ) );
	
	/*
	 * contains necessary hooks
	 */
	static function init() {
		//adding new fileds in advanced setting section
		add_action ( 'gform_advanced_settings', array (get_class (), 'gform_advanced_settings' ) );
	
		//add extra tool tips
		add_filter('gform_tooltips', array(get_class(), 'gform_tooltips'));				
	}
	
	/*
	 * adding new settings fields with the Form in admin panel
	 */
	static function gform_advanced_settings($position, $form_id = '') {
		if ($position != 800)
			return;
		if (isset ( $form_id ) != $_GET ['id'])
			return;
		
		echo '<li><input type="checkbox" onclick="ToggleauthorizeNet();" id="gform_authorizeNet" /> ';
		echo '<label for="gform_authorizeNet" id="">';
		_e ( "Enable Authorize.net" ); gform_tooltip ( "authorizenet_enabled" );
		
		echo '</label></li>';
		echo '<li id="gform_authorizeNet_container" style="display:none">';
		self::gfauthorizeNet_form_options ( $_GET ['id'] );
		echo '</li>';
		
		?>

<script>
			function ToggleauthorizeNet(isInit)
			{
				var speed = isInit ? "" : "slow";
				if(jQuery("#gform_authorizeNet").is(":checked")) 
					jQuery("#gform_authorizeNet_container").show(speed);		
				else
					jQuery("#gform_authorizeNet_container").hide(speed);
					form.authorizeNet_enabled = jQuery("#gform_authorizeNet").is(":checked");
			}
			
			function ChangeAuthorizeNetfield(field_name) 
			{
				//alert(jQuery("#"+field_name).val());
				eval('form.'+field_name+' = jQuery("#"+field_name).val();');
				//alert(form.customcrm_person_email);
			}
			jQuery("#gform_authorizeNet").attr("checked", form.authorizeNet_enabled ? true : false);
			ToggleauthorizeNet(true);
			
		</script>

<?php
	}
	
	/*
	 * Custom Form Fields
	 */
	static function gfauthorizeNet_form_options($form_id) {
		// load the form for the field merge tag generators
		
		?>
		
		<h4>Integrate Authorize Net</h4>
		<table cellspacing="2" cellpadding="2">
			<?php 
				foreach(self::$tooltips as $key => $value) :
				$key_1 = 'authorizenet_' . $key;
			?>
			
			<tr>
				<td align="right"><?php echo $value[0]?></td>
				<td><?php echo self::get_field_selector($form_id, $key_1) ?></td>
			</tr>
			
			<?php endforeach;?>
		</table>
		
		<?php		
				
	}
	
	/*
	 *Add extra tooltips to show for this addon 
	 */
	static function gform_tooltips($gf_tooltips) {
		
		$gf_tooltips ["authorizenet_enabled"] = "<h6>" . __ ( "Authorize.net" ) . "</h6>" . __ ( "Check this box to integrate this form with Authorize.net. When this form is submitted successfulling the data will be pushed to the authorize.net. For work properly please make the every field required" );
		
		foreach ( self::$tooltips as $key => $value ) {
			$gf_tooltips ['authorizenet_' . $key] = '<h6>' . __ ( $value [0] ) . '</h6>' . __ ( $value [1] );
		}
		
		return $gf_tooltips;
	}
	
	/*
	 * Selector fields
	 */
	public static function get_field_selector($form_id, $field_name, $selected_field = null) {
		$form_fields = self::get_form_fields($form_id);
		$str = '<select id="'.$field_name.'" size="1" onchange=\'ChangeAuthorizeNetfield("'.$field_name.'");\'>';
		$str .= '<option value="">Choose</option>'."\n";
		foreach($form_fields as $_field) 
		{
			$str .= '<option value="'.$_field[0].'"';
			if($selected_field && $_field[0] == $selected_field) $str .= ' selected';
			$str .= '>'.$_field[1].'</option>'."\n";
		}
		$str .= '</select>'."\n";
		$str .= '<script> jQuery("#'.$field_name.'").val( form.'.$field_name.'); </script>'."\n";
		return $str;
	}
	
/*
	 * statif cuntions to return fields
	 */
	public static function get_form_fields($form_id){
		$form = RGFormsModel::get_form_meta($form_id);
		$fields = array();
		
		if(is_array($form["fields"])){
			foreach($form["fields"] as $field){
				if(is_array(rgar($field, "inputs"))){					
					
					foreach($field["inputs"] as $input)
						$fields[] =  array($input["id"], GFCommon::get_label($field, $input["id"]));
				}
				else if(!rgar($field,"displayOnly")){
					$fields[] =  array($field["id"], GFCommon::get_label($field));
				}
			}
		}
		return $fields;
	}
	
	
	
}