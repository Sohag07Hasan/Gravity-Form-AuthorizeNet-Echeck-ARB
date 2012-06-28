 <style>
  .valid_credentials{color:green;}
  .invalid_credentials{color:red;}
  .size-1{width:400px;}
</style>


<div class="wrap">
	
	<?php 
		if($_POST['authorizenet_settings_submitted'] == "Y") :
			echo "<div class='updated'><p>Options are saved</p></div>";
		endif;
	?>
	
	 <h3><?php _e("Authorize.Net Account Information", "gravityformsauthorizenet") ?></h3>
     <p style="text-align: left;">
        <?php _e(sprintf("Authorize.Net is a payment gateway for merchants. Use Gravity Forms to collect payment information and automatically integrate to your client's Authorize.Net account. If you don't have a Authorize.Net account, you can %ssign up for one here%s", "<a href='http://www.authorizenet.com' target='_blank'>" , "</a>"), "gravityformsauthorizenet") ?>
     </p>
     
     <form action="" method="post">
		 
		 <input type="hidden" name="authorizenet_settings_submitted" value="Y" />
		 
     	<table class="form-table">
     		<tr>
                 <th scope="row" nowrap="nowrap"><label for="gf_authorizenet_mode"><?php _e("Mode", "gravityformsauthorizenet"); ?></label> </th>
                 <td width="88%">
                     <input type="radio" name="gf_authorizenet_mode" id="gf_authorizenet_mode_production" value="production" <?php checked($settings['mode'], 'production'); ?>/>
                     <label class="inline" for="gf_authorizenet_mode_production"><?php _e("Production", "gravityformsauthorizenet"); ?></label>
                        &nbsp;&nbsp;&nbsp;
                 	 <input type="radio" name="gf_authorizenet_mode" id="gf_authorizenet_mode_test" value="test" <?php checked($settings['mode'], 'test') ?>/>
                    <label class="inline" for="gf_authorizenet_mode_test"><?php _e("Test", "gravityformsauthorizenet"); ?></label>
                  </td>
            </tr>
			<tr>
				<th scope="row" nowrap="nowrap"><label for="gf_authorizenet_username"><?php _e("API Login ID", "gravityformsauthorizenet"); ?></label> </th>
				<td width="88%">
					<input class="size-1" id="gf_authorizenet_login_id" name="gf_authorizenet_login_id" value="<?php echo esc_attr($settings['api_login_id']); ?>" />
					<img src="<?php echo self::get_base_url() ?>/images/<?php echo $is_valid ? "tick.png" : "stop.png" ?>" border="0" alt="<?php $message ?>" title="<?php echo $message ?>" style="display:<?php echo empty($message) ? 'none;' : 'inline;' ?>" />
					<br/>
					<small><?php _e("You can find your unique <strong>API Login ID</strong> by clicking on the 'Account' link at the Authorize.Net Merchant Interface. Then click 'API Login ID and Transaction Key'. Your API Login ID will be displayed.", "gravityformsauthorizenet") ?></small>
				</td>
			</tr> 
			<tr>
                <th scope="row" nowrap="nowrap"><label for="gf_authorizenet_username"><?php _e("Transaction Key", "gravityformsauthorizenet"); ?></label> </th>
				<td width="88%">
					<input type="text" class="size-1" id="gf_authorizenet_transaction_key" name="gf_authorizenet_transaction_key" value="<?php echo esc_attr($settings['trans_key']); ?>" />
					<img src="<?php echo self::get_base_url() ?>/images/<?php echo $is_valid ? "tick.png" : "stop.png" ?>" border="0" alt="<?php $message ?>" title="<?php echo $message ?>" style="display:<?php echo empty($message) ? 'none;' : 'inline;' ?>" />
					<br/>
					<small><?php _e("You can find your unique <strong>Transaction Key</strong> by clicking on the 'Account' link at the Authorize.Net Merchant Interface. Then click 'API Login ID and Transaction Key'. For security reasons, you cannot view your Transaction Key, but you will be able to generate a new one.", "gravityformsauthorizenet") ?></small>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<h3><?php _e("Automated Recurring Billing Setup", "gravityformsauthorizenet") ?></h3>
					<p style="text-align: left;">
						<?php _e("To create recurring payments, you must have Automated Recurring Billing (ARB) setup in your Authorize.Net account.", "gravityformsauthorizenet") ?>
					</p>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="checkbox" value="Y" name="gf_arb_configured" id="gf_arb_configured" <?php checked($settings['arb_configured'], "Y") ?>/>
					<label for="gf_arb_configured" class="inline"><?php _e("ARB is setup in my Auhorize.Net account.", "gravityformsauthorizenet") ?></label>
				</td>
			</tr>
			<tr>
				<td colspan="2" ><input type="submit" name="gf_authorizenet_submit" class="button-primary" value="<?php _e("Save Settings", "gravityformsauthorizenet") ?>" /></td>
			</tr>

			    	
     	</table>
     </form>

</div>
