<?php
/*
Plugin Name: Ama payments
Plugin URI: http://www.artmedia.ee
Description: Payment system for merit
Author: Artmedia | Modified by Tanel Unt (OKIA)
Version: 1.0
Author URI: http://www.artmedia.ee
*/
ini_set('display_errors', 0);
error_reporting(0);
if ( !function_exists( 'add_action' ) )
	wp_die( 'You are trying to access this file in a manner not allowed.', 'Direct Access Forbidden', array( 'response' => '403' ) );

/** -------------------------------------
Setup plugins settings and include files
--------------------------------------**/
if ( !session_id() ) session_start();

define('_PLUGIN_',      'ama-payments');
define('_PLUGIN_PATH_', plugin_dir_path(__FILE__));
define('_PLUGIN_',      plugin_basename(__FILE__));
define('_PLUGIN_URL_', plugin_dir_url(__FILE__)); //'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/plugins/' . _PLUGIN_); //plugins_url(_PLUGIN_));

define('TAX_FI', 1.24);//tax
define('TAX_PL', 1.23);//tax

$test = 0;
if($_SERVER["REMOTE_ADDR"]=='87.98.44.201') {
  $polishTest = 0;
}
else {
$polishTest = 0;
}


// OKIA DEV
if (preg_match('/okia.ee/', $_SERVER['HTTP_HOST'])){
	$test = 1;
	$polishTest = 1;
}

if ( $test == 1 ) {
define('MERCHANT_ID', 13466);
define('MERCHANT_SECRET', '6pKF4jkv97zmqBJ3ZL8gUw5DfT2NMQ');
define('MERCHANT_RETURN_ADDRESS', _PLUGIN_URL_);
}
else {
define('MERCHANT_ID', 22260);
define('MERCHANT_SECRET', 'kxzzDiPi3WEMaqw8P5E5QYPgNdXBcV');
define('MERCHANT_RETURN_ADDRESS', SITE_URL);
}

//polandBank
if ( $polishTest == 1 ) {
define("POLISH_MERCHANT_ID", '25770');
define("POLISH_CRC_KEY", '89bb316e717322ca');
define("POLISH_IS_TEST", true);
}
else {
define("POLISH_MERCHANT_ID", '25770');
define("POLISH_CRC_KEY", '5ec1f7db9509ad5f');
define("POLISH_IS_TEST", false);
}


define("WSDL_USER","25770");
define("WSDL_PASSWORD", "2276d670eef8925c07cdde114e1f6d22");
define("WSDL_LANG", "pl");

add_action('wp_enqueue_scripts','ama_payments_register_script');
add_action('admin_enqueue_scripts', 'ama_payments_register_script');

include_once _PLUGIN_PATH_.'custom_functions.php';
include_once _PLUGIN_PATH_.'admin/adminClass.php';

add_action( 'save_post', 'ama_product_save_post', 10, 2 );

add_action( 'init', 'ama_packages_custom_post_type' );
add_action( 'add_meta_boxes', 'ama_package_custom_post_type_fields');

add_action( 'init', 'ama_coupongs_custom_post_type' );
add_action( 'add_meta_boxes', 'ama_coupongs_custom_post_type_fields');

add_action( 'init', 'ama_orders_custom_post_type' );
add_action( 'add_meta_boxes', 'ama_orders_custom_post_type_fields');

add_action('post_edit_form_tag', 'add_post_enctype');

add_action('init', 'myStartSession', 1);
add_action('wp_head', 'addTombstoneDefaults');
add_action('admin_head', 'addTombstoneDefaults');

//add_filter('manage_posts_columns', 'add_img_column');
//add_filter('manage_posts_custom_column', 'manage_img_column', 10, 2);

//add_image_size('ama-post-featured-image', 60, 60);


//admin
$AmaHinnapakkumine = new AmaPaymentsAdmin();

//little fix
$wp_rewrite = new WP_Rewrite();

//form
add_shortcode("ama-payments",'showForm' );
function showForm() {
	global $post;
    if (isset($_POST) && !isset($_GET['action'])) {
        $_SESSION['oi_postData'] = $_POST;
    }

    if ( isset($_SESSION['oi_postData'])) {
        $_POST = $_SESSION['oi_postData'];
    }

    if(isset($_GET['action']) && !empty($_GET['action'])) {
        $_REQUEST['action'] = $_GET['action'];

        if( $_REQUEST['action'] == 'postOrder' ) {
            $_POST['order']['total'] = $_POST['order']['orig_total'];
        }
    }

    if(isset($_GET['show'])) {
        $_REQUEST['action'] = 'postOrder';
    }

    //var_dump($_POST);

	switch ( $_REQUEST['action'] ) {
    case "saveOrder":
        global $sitepress;
            $current_lang = $sitepress->get_current_language();
            $_POST['order']['lang']  = $current_lang;

            if (empty($_POST['order']['email']) && empty($_POST['order']['name'])) {
                return '<div class="alert alert-danger">'.__("Something went wrong!", "ama").'</div>';
            }

        	$orderId = amaSaveOrder($_REQUEST['order']);

         	$_REQUEST['order']['bankForm'] = generateBankForm($_REQUEST['order']['payment'], $_REQUEST['order'], $orderId);

        	switch ( $_REQUEST['order']['payment'] ) {
        		case "suomi" :
                    include_once _PLUGIN_PATH_.'/templates/ama-payments-summary-form.php';
          			$html = amaPaymentsSummaryForm($_REQUEST['order']);
                break;
        		case "polish" :
                     echo goToPolishPayment($_REQUEST['order'], $orderId);
                     exit;
        		break;
        		case "invoice" :
        			include_once _PLUGIN_PATH_.'/templates/ama-payments-invoice-form.php';
        			$html = amaPaymentsInvoiceForm($_REQUEST['order']);
        		break;
        	}
            return $html;
     break;
	case "postOrder":
		if(empty($_GET['package']) && isset($_POST['order']['package'])) {
			$_GET['package'] = $_POST['order']['package'];
			$_GET['type'] = $_POST['order']['type'];
		}

		include_once _PLUGIN_PATH_.'/templates/ama-payments-order-form.php';
        $html = amaPaymentsOrderFormBefore();
		return amaPaymentsOrderForm($_POST['order']);
	break;
	case "orderCancel":
		include_once _PLUGIN_PATH_.'/templates/ama-payments-payment-cancel.php';
		return amaPaymentCancel();
	break;
	case "orderNotify":
		include_once _PLUGIN_PATH_.'/templates/ama-payments-payment-notify.php';
		return amaPaymentNotify();
	break;
	case "orderSuccess":
		$myFile = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/ama-payments/log.txt";
		$file = fopen($myFile,"a+") or die("can't open file");
		$oldContent = file_get_contents($file);
		$newContent = date("Y-m-d H:i:s") . ' orderSuccess: orderSuccess: ' . json_encode($_REQUEST);
		fwrite($file, $newContent."\n".$oldContent);
		
		makeContract($_SESSION['ama_order_id']); // Jätku arve puhul ei tohi teha, kuidas seda eristame???
		registerUser($contactName, $loginEmail);
		amaOrderSuccess();
	break;
	case "success":
		registerUser($contactName, $loginEmail);
		include_once _PLUGIN_PATH_.'/templates/ama-payments-payment-success.php';
		return amaPaymentSuccess();
	break;
	default:
		include_once _PLUGIN_PATH_.'/templates/ama-payments-form.php';
		return ama_payments_form();
	break;
	}
}


if ( $_REQUEST['formAction'] == 'send') sendForm();
if ( $_REQUEST['formAction'] == 'save') saveForm();

?>
