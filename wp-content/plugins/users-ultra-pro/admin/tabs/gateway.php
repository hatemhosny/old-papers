<h3><?php _e('Payment Gateways Settings','xoousers'); ?></h3>
<form method="post" action="">
<input type="hidden" name="update_settings" />

<div class="user-ultra-sect ">
  <h3><?php _e('PayPal','xoousers'); ?></h3>
  
  <p><?php _e('Here you can configure PayPal if you wish to accept paid registrations','xoousers'); ?></p>
  
  
  <table class="form-table">
<?php 

$this->create_plugin_setting(
                'checkbox',
                'gateway_paypal_active',
                __('Activate PayPal','xoousers'),
                '1',
                __('If checked, PayPal will be activated as payment method','xoousers'),
                __('If checked, PayPal will be activated as payment method','xoousers')
        ); 

$this->create_plugin_setting(
	'select',
	'uultra_send_ipn_to_admin',
	__('The Paypal IPN response will be sent to the admin','xoousers'),
	array(
		'no' => __('No','xoousers'), 
		'yes' => __('Yes','xoousers'),
		),
		
	__("If 'yes' the admin will receive the whole Paypal IPN response. This helps to troubleshoot issues.",'xoousers'),
  __("If 'yes' the admin will receive the whole Paypal IPN response. This helps to troubleshoot issues.",'xoousers')
       );

$this->create_plugin_setting(
        'input',
        'gateway_paypal_email',
        __('PayPal Email Address','xoousers'),array(),
        __('Enter email address associated to your PayPal account.','xoousers'),
        __('Enter email address associated to your PayPal account.','xoousers')
);

$this->create_plugin_setting(
        'input',
        'gateway_paypal_sandbox_email',
        __('Paypal Sandbox Email Address','xoousers'),array(),
        __('This is not used for production, you can use this email for testing.','xoousers'),
        __('This is not used for production, you can use this email for testing.','xoousers')
);

$this->create_plugin_setting(
        'input',
        'gateway_paypal_currency',
        __('Currency','xoousers'),array(),
        __('Please enter the currency, example USD.','xoousers'),
        __('Please enter the currency, example USD.','xoousers')
);

$this->create_plugin_setting(
	'select',
	'gateway_paypal_mode',
	__('Mode','xoousers'),
	array(
		1 => __('Production Mode','xoousers'), 
		2 => __('Test Mode (Sandbox)','xoousers')
		),
		
	__('.','xoousers'),
  __('.','xoousers')
       );




		
?>
</table>

  
</div>


<div class="user-ultra-sect ">
  <h3><?php _e('Bank Deposit/Cash Other','xoousers'); ?></h3>
  
  <p><?php _e('Here you can configure an alternative payment method for your clients.','xoousers'); ?></p>
  
  
  <table class="form-table">
<?php 

$this->create_plugin_setting(
                'checkbox',
                'gateway_bank_active',
                __('Activate Bank Deposit','xoousers'),
                '1',
                __('If checked, Bank/Cash will be activated as payment method','xoousers'),
                __('If checked, Bank/Cash will be activated as payment method','xoousers')
        ); 


$this->create_plugin_setting(
        'input',
        'gateway_bank_label',
        __('Custom Label','xoousers'),array(),
        __('Example: Bank Deposit , Cash, Wire etc.','xoousers'),
        __('Example: Bank Deposit , Cash, Wire etc.','xoousers')
);


$this->create_plugin_setting(
        'textarea',
        'gateway_bank_payment_information',
        __('Payment Details','xoousers'),array(),
        __('Input here all the relevant information that will be sent to the client, for example: the bank account details.','xoousers'),
        __('Input here all the relevant information that will be sent to the client, for example: the bank account details.','xoousers')
);

		
?>
</table>

  
</div>



<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','xoousers'); ?>"  />
	
</p>

</form>