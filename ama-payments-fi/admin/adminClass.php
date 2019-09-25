<?php 

class AmaPaymentsAdmin 
{
	
	public function __construct() 
	{
		
		// Add options page and settings
		add_action('admin_menu', array(&$this, 'get_settings_menu'));
		add_action('admin_menu', array(&$this, 'register_settings'));
		
	}
	
	public function get_settings_menu()
	{
		add_options_page('AMA payments', 'AMA payments', 'administrator', 'ama-payments-options', array(&$this, 'get_settings_page'));
	}
	
	public function get_settings_page()
	{
		include(_PLUGIN_PATH_ . 'admin/admin_options.php');
	}
	
	public function register_settings()
	{
	    $lang_prefix = ICL_LANGUAGE_CODE;
        register_setting('ama-payments-options', 'ama_payments_options_' . $lang_prefix);
        
		/*register_setting('ama-payments-options', 'ama_default_price');
		register_setting('ama-payments-options', 'ama_order_email');
		register_setting('ama-payments-options', 'ama_enable_soumipankki');
		register_setting('ama-payments-options', 'ama_enable_invoice_order');
		register_setting('ama-payments-options', 'ama_order_kaupan_text');*/
	}
	
}

?>