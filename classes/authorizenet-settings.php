<?php
/*
 * handles all the setting of the authorize net page
 * */

class AuthorizeNetSettings{
	
	static function init(){
		add_action('init', array(get_class(), 'initialize'));
	}
	
	//ini
	static function initialize(){
		if(is_admin()) :
			if(RGForms::get("page") == "gf_settings"){
				RGForms::add_settings_page("Authorize.Net Echeck", array(get_class(), "settings_page"), '');
			}
		endif;
	}
	
	//settings page]
	static function settings_page(){
		$settings = self::update_settings();
		$is_valid = self::is_valid_key();
		$message = "";
        if($is_valid)
            $message = "Valid API Login Id and Transaction Key.";
        else 
        	 $message = "Invalid API Login Id and/or Transaction Key. Please try again.";
		
		
		include self::get_base_dir() . '/includes/settings-page.php';
	}
	
	private static function update_settings(){
		if($_POST['authorizenet_settings_submitted'] == "Y") :
			$settings = array(
				'mode' => trim(esc_attr($_POST['gf_authorizenet_mode'])),
				'api_login_id' => trim(esc_attr($_POST['gf_authorizenet_login_id'])),
				'trans_key' => trim(esc_attr($_POST['gf_authorizenet_transaction_key'])),
				'arb_configured' => trim($_POST['gf_arb_configured'])
			);
			
			update_option('authorizenet_echeck_arb', $settings);
		endif;
		
		return self::get_authorizenet_options();
	}
	
	static function get_authorizenet_options(){
		$settings = get_option('authorizenet_echeck_arb');
		return $settings;
	}
	
	static function get_base_dir(){
		return GfAuthorizeNetEcheckARB;
	}
	
	
	static function get_base_url(){
		return GfAuthorizeNetEcheckARB_URL;
	}
	
	private static function is_valid_key(){
       
        $auth = AurhorizeNetEcheck::get_aim();

        $response = $auth->AuthorizeOnly();
        $failure = $response->error;
        $response_reason_code = $response->response_reason_code;
        if($failure && ($response_reason_code == 13 || $response_reason_code == 103) )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    
}