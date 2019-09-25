<?php
include_once "../../../wp-config.php";

if ( !session_id() ) session_start();

global $sitepress;
$sitepress->switch_lang($_REQUEST['lang'], true);
define('ICL_LANGUAGE_CODE', $_REQUEST['lang']);

switch($_REQUEST['action']) {
	case "postOrder"    : postOrder();    break;
	case "saveOrder"    : saveOrder();    break;
	case "saveToSession"  : saveToSession();  break;
	case "validateCoupong"  : validateCoupong();  break;
}


function postOrder()
{
	$status = 1;
	$url = '';
	if ( empty($_REQUEST['order']) ) {
		header('Content-Type: application/json');
		echo json_encode(array('status' => 0, 'html' => ''));
		exit;
	}

    global $sitepress;
    $current_lang = $sitepress->get_current_language();
    $_POST['order']['lang']  = $current_lang;

    $price_type = 'year_price';
    $extra_user_price_type = 'year_extra_user_price';
    if ( $_POST['order']['type']==2 ) {
    $price_type = 'month_price';
    $extra_user_price_type = 'month_extra_user_price';
    }

    $_POST['order']['package_price'] = number_format(get_post_meta($_POST['order']['package'],$price_type, true),2,',','');
    $extraUserPrice = get_post_meta($_POST['order']['package'],$extra_user_price_type, true);

    if ( $_POST['order']['type'] == 2) {
        $_POST['order']['package_price'] = calcNewMonthPrice($_POST['order']['package_price']);
    	$_POST['order']['total'] = calcNewMonthPrice($_POST['order']['total']);
    }

    doHaveDefaultCoupong($_POST['order']);
    if(isset($_SESSION['coupong']) && $_SESSION['coupong']['type'] == 'discount') {
    	$_POST['order']['discount'] = sprintf("%.2f",($_POST['order']['total']/100)*$_SESSION['coupong']['value']);
    	$_POST['order']['total'] = $_POST['order']['total']-$_POST['order']['discount'];
    }
   	$_POST['order']['total_without_tax'] = $_POST['order']['total'];
    $total =  calcAlv($_POST['order']['total'], $current_lang);
    $_POST['order']['total'] = number_format($total['price'],2,',','');
    $_POST['order']['vat'] = $total['vat'];
    $_POST['order']['price_vat'] = $total['price_vat'];
    $_POST['order']['extra_user_total'] = ($extraUserPrice*($_POST['order']['extraUsers']-1));
    if ( $_POST['order']['type'] == 2) {
      $_POST['order']['extra_user_total'] = calcNewMonthPrice($_POST['order']['extra_user_total']);
    }

	include_once _PLUGIN_PATH_.'/templates/ama-payments-order-form.php';
	$html = amaPaymentsOrderForm($_POST['order']);

	header('Content-Type: application/json');
	echo json_encode(array('status' => $status, 'html' => $html, 'url' => $url));
	exit;
}

function saveOrder()
{
	$status = 1;
	if ( empty($_REQUEST['order']) ) {
		header('Content-Type: application/json');
		echo json_encode(array('status' => 0, 'html' => ''));
		exit;
	}

    global $sitepress;
    $current_lang = $sitepress->get_current_language();
    $_POST['order']['lang']  = $current_lang;

	$orderId = amaSaveOrder($_REQUEST['order']);

 	$_REQUEST['order']['bankForm'] = generateBankForm($_REQUEST['order']['payment'], $_REQUEST['order'], $orderId);

	switch ( $_REQUEST['order']['payment'] ) {
		case "suomi" :
		case "polish" :
			include_once _PLUGIN_PATH_.'/templates/ama-payments-summary-form.php';
			$html = amaPaymentsSummaryForm($_REQUEST['order']);
		break;
		case "invoice" :
			include_once _PLUGIN_PATH_.'/templates/ama-payments-invoice-form.php';
			$html = amaPaymentsInvoiceForm($_REQUEST['order']);
		break;
	}

	header('Content-Type: application/json');
	echo json_encode(array('status' => $status, 'html' => $html));
	exit;
}

function validateCaptcha()
{
	if (empty($_SESSION['captcha']) || strtolower(trim($_REQUEST['code'])) != $_SESSION['captcha']) {
	    echo "false";
	}
	else {
		echo "true";
	}
}

function saveToSession() {
	session_start();
	$name = $_REQUEST['name'];
	$value = $_REQUEST['value'];
	$_SESSION['order'][$name] = $value;
}

function validateCoupong() {
	if(isset($_REQUEST['code']) && $_REQUEST['code'] && isset($_REQUEST['action']) && $_REQUEST['action'] == 'validateCoupong') {//Kupongide osa
		$packages = new WP_Query(array('post_type' => 'ama_packages','post_status' => 'publish'));
		$coupongs = new WP_Query(array('post_type' => 'ama_coupongs','post_status' => 'publish'));
		$code = $_REQUEST['code'];
		$formType = $_REQUEST['formType'];//1 == aasta, 2 == kuu
		$date = date("d-m-Y");
		$packageId = $_REQUEST['packageId'];
		$return = [];
		$return['type'] = '';
		$return['value'] = '';
		$return['error'] = '';

		foreach($coupongs->posts as $coupong) {
			$meta = get_post_meta($coupong->ID);
			if($meta['code'][0] == $code) {
				$return['error'] = '';
				if(isset($meta['package-'.$packageId][0]) && $meta['package-'.$packageId][0] == 1) {
					if (empty($meta['start'][0]) && empty($meta['end'][0]) || strtotime($date) >= strtotime($meta['start'][0]) && strtotime($date) <= strtotime($meta['end'][0])) {
						if($formType == 2 && $meta['monthly'][0]) {
							$return['error'] = '';
							$return['coupong'] = $coupong;
						} else if($formType == 1 && $meta['yearly'][0]) {
							$return['error'] = '';
							$return['coupong'] = $coupong;
						} else {
							$return['error'] = __('Not for this payment type','ama');
						}
					} else {
						$return['error'] = __('Date problem','ama');
					}
				} else {
					$return['error'] = __('Not this package code','ama');
				}
				break;
			} else {
				$return['error'] = __('Wrong code','ama');
			}
		}

		if($return['error']) {
			print_r(json_encode($return));die;
		}

		$meta = get_post_meta($return['coupong']->ID);
		$discount = $meta['discount'][0];
		$freemonths = $meta['freemonths'][0];

		if($freemonths) {
			$return['type'] = 'freemonths';
			$return['value'] = $freemonths;
		}
		if($discount) {
			$return['type'] = 'discount';
			$return['value'] = $discount;
		}

		$_SESSION['coupong']['id'] = $coupong->ID;
		$_SESSION['coupong']['name'] = $meta['name'][0];
		$_SESSION['coupong']['code'] = $code;
		$_SESSION['coupong']['type'] = $return['type'];
		$_SESSION['coupong']['value'] = $return['value'];

		print_r(json_encode($return));die;
	}
}

function doHaveDefaultCoupong($postData) {
	$coupongs = new WP_Query(array('post_type' => 'ama_coupongs','post_status' => 'publish'));
	$formType = $postData['type'];//1 == aasta, 2 == kuu
	$date = date("d-m-Y");
	$packageId = $postData['package'];
	$return = [];
	$code = '';
	$return['type'] = '';
	$return['value'] = '';
	$return['error'] = '';

	foreach($coupongs->posts as $coupong) {
		$meta = get_post_meta($coupong->ID);
		$return['error'] = '';
		if($meta['default'][0]) {
			if(isset($meta['package-'.$packageId][0]) && $meta['package-'.$packageId][0] == 1) {
				if (empty($meta['start'][0]) && empty($meta['end'][0]) || strtotime($date) >= strtotime($meta['start'][0]) && strtotime($date) <= strtotime($meta['end'][0])) {
					if($formType == 2 && $meta['monthly'][0]) {
						$return['error'] = '';
						$return['coupong'] = $coupong;
						$code = $meta['code'][0];
						break;
					} else if($formType == 1 && $meta['yearly'][0]) {
						$return['error'] = '';
						$return['coupong'] = $coupong;
						$code = $meta['code'][0];
						break;
					} else {
						$return['error'] = __('Not for this payment type','ama');
					}
				} else {
					$return['error'] = __('Date problem','ama');
				}
			} else {
			}
		}
	}

	$meta = get_post_meta($return['coupong']->ID);
	$discount = $meta['discount'][0];
	$freemonths = $meta['freemonths'][0];

	if($freemonths) {
		$return['type'] = 'freemonths';
		$return['value'] = $freemonths;
	}
	if($discount) {
		$return['type'] = 'discount';
		$return['value'] = $discount;
	}

	if($code) {
		$_SESSION['coupong']['id'] = $coupong->ID;
		$_SESSION['coupong']['name'] = $meta['name'][0];
		$_SESSION['coupong']['code'] = $code;
		$_SESSION['coupong']['type'] = $return['type'];
		$_SESSION['coupong']['value'] = $return['value'];
		$_SESSION['coupong']['default'] = 1;
	}
}

?>