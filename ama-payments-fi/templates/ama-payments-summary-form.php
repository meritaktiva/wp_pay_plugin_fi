<?php
function amaPaymentsSummaryForm($order)
{
	ob_start();
    loadJavascript();
	global $sitepress;
    $lang_prefix = $sitepress->get_current_language();
    $options = get_option("ama_payments_options_" . $lang_prefix);
	$enable_soumipankki = $options['ama_enable_soumipankki'];
	$enable_polish_bank = $options['ama_enable_polish_bank'];
?>

<main role="main">
	<div class="main-hero__section section__medium bg-checkout">
            <div class="container">
               <div class="row">
                  <div class="col-xs-12 col-sm-8 col-lg-9">
                     <h2 class="page-title hero-title text-light"><?php echo get_post_meta( get_the_ID(), '_cmb_checkout_slogan', true ); ?></h2>
                  </div>
               </div>
             </div>
    </div>
	<div class="main-content__section main-content-checkout bg-light">
            <div class="container">
<?php if ( false ) :?>
<h2><?php _e("Summary", "ama"); ?>: <a href="javascript:;" class="btn pull-right goBackToOrder"><?php _e("Go back to order data", "ama"); ?></a></h2>
<div class="clear"></div>
<table class="table">
	<tr>
		<td><?php _e("Package", "ama"); ?></td>
		<td><?php echo $order['packageName']; ?></td>
	</tr>
	<tr>
		<td><?php _e("Period", "ama"); ?></td>
		<td><?=$order['orderPeriod']?></td>
	</tr>
	<tr>
		<td><?php if ( $order['noExtraUsers'] == 0 ) : ?><?php _e("Users", "ama"); ?><?php else : ?>&nbsp;<?php endif; ?></td>
		<td><?php if ( $order['noExtraUsers'] == 0 ) : ?><?php echo $order['extraUsers']; ?><?php else: ?>&nbsp;<?php endif; ?></td>
	</tr>
	<?php if(isset($order['extraUserTotal']) && $order['extraUserTotal']):?>
	<tr>
		<td><?php _e("Extra users price", "ama"); ?></td>
		<td><?=$order['extraUserTotal']?> <?php _e("€", "ama"); ?></td>
	</tr>
	<?php endif;?>
	<?php if(isset($coupong) && $coupong):?>
		<?php if($coupong['discount'][0]):?>
			<tr>
				<td><?=$coupong['name'][0]?></td>
				<td>-<?=$order['discount']?> <?php _e("€", "ama"); ?></td>
			</tr>
		<?php else:?>
			<tr>
				<td><?=$coupong['name'][0]?></td>
				<td><?=$order['orderDiscountPeriod']?></td>
			</tr>
		<?php endif;?>
	<?php endif;?>
	<tr>
		<td><?php _e("Without vat", "ama"); ?></td>
		<td><?=$order['withoutTax']?> <?php _e("€", "ama"); ?></td>
	</tr>
	<tr>
		<td><?php _e("Vat", "ama"); ?></td>
		<td><?=$order['tax']?> <?php _e("€", "ama"); ?></td>
	</tr>
	<tr>
		<td><?php _e("Price", "ama"); ?></td>
		<td><?php echo $order['total']; ?> <?php _e("€", "ama"); ?> <?php if ($order['type']==1 ) :?><?php _e("annualy", "ama"); ?><?php else: ?><?php _e("monthly", "ama"); ?><?php endif; ?></td>
	</tr>
	<?php if($our && $order['payment'] != 'invoice') : ?>
	<tr>
		<td><?php _e("Payed with: ", "ama"); ?></td>
		<td> <?php echo $order['payment']; ?> <?php _e("bank", "ama"); ?></td>
	</tr>
	<?php endif; ?>
</table>

<h2><?php _e("Customer details", "ama"); ?></h2>

<table class="table">
    <tr>
	    <td><strong><?php _e("Company", "ama"); ?></strong></td>
	    <td><?php echo $order['company']; ?></td>
 	</tr>
 	<tr>
	    <td><strong><?php _e("Y-tunnus", "ama"); ?></strong></td>
	    <td><?php echo $order['ytunnus']; ?></td>
 	</tr>
 	<?php if(isset($order['contactName']) && $order['contactName']):?>
	<tr>
	    <td><strong><?php _e("Contact name", "ama"); ?></strong></td>
	    <td><?php echo $order['contactName']; ?></td>
	</tr>
 	<?php endif;?>
   <tr>
	    <td><strong><?php _e("Street", "ama"); ?></strong></td>
	    <td><?php echo $order['street']; ?></td>
 	</tr>
    <tr>
	    <td><strong><?php _e("City", "ama"); ?></strong></td>
	    <td><?php echo $order['city']; ?></td>
 	</tr>
 	<?php if(isset($order['regNr']) && $order['regNr']):?>
    <tr>
	    <td><strong><?php _e("Reg nr", "ama"); ?></strong></td>
	    <td><?php echo $order['regNr']; ?></td>
 	</tr>
 	<?php endif;?>
    <tr>
	    <td><strong><?php _e("Email", "ama"); ?></strong></td>
	    <td><?php echo $order['email']; ?></td>
 	</tr>
 	<tr>
	    <td><strong><?php _e("Index", "ama"); ?></strong></td>
	    <td><?php echo $order['index']; ?></td>
 	</tr>
    <tr>
	    <td><strong><?php _e("Phone", "ama"); ?></strong></td>
	    <td><?php echo $order['phone']; ?></td>
 	</tr>
		<tr>
	    <td><strong><?php _e("Your e-mail address to log into the program", "ama"); ?></strong></td>
	    <td><?php echo $order['loginEmail']; ?></td>
	</tr>
</table>

<h2><?php _e("Choose bank", "ama"); ?></h2>
<?php endif; ?>
<div class="bank_form <?php if ( $enable_polish_bank && false ) : ?>polish<?php endif; ?>">
<?php if ( $enable_polish_bank && false ) : ?>
<h2><?php _e("Choose bank", "ama"); ?></h2>
<?php
include_once _PLUGIN_PATH_ . "include/polishBank.class.php";
$polish = new polishBank;
$paymentMethods = $polish->getPaymentMethods();
$template_dir = get_template_directory_uri().'/img/bank/';
$i = 0;
?>
<table id="banks">
<?php foreach ( $paymentMethods  as $method ) : ?>
	<?php

		if ( empty($method->status) || $method->status == 0 ) continue;

		if ( $i == 1 ) {
			echo '</tr><tr>';
		}

		if ( $i == 0 ) {
			$i = 1;
			echo '<tr>';
		}
	?>
	<td><a href="javascript:;" onclick="chooseBankAndPay('<?php echo $method->id; ?>')"><img src="<?php echo $template_dir .'logo_'. $method->id.'.gif'; ?>" alt="<?php echo $method->name; ?>" /></a></td>
	<?php
		if( $i == 6 ) {
			$i = 0;
		}
		$i++;
	?>
<?php endforeach; ?>
</table>
<script>
$j = jQuery.noConflict();
function chooseBankAndPay(method)
{
	$j('#p24_method').val(method);
	$j('#paymentForm').submit();
}
</script>
<?php endif; ?>
    <?php if ( $enable_polish_bank) : ?>
        <div style="width:100%; text-align:center;">
            <img src="<?=_PLUGIN_URL_?>/images/logo_p24.png" alt="" />
    <?php endif; ?>
	<?php echo $order['bankForm']; ?>
     <?php if ( $enable_polish_bank) : ?>
        </div>
     <?php endif; ?>
</div>
<div class="clear"></div>
<br />
<?php /* ?>
<a href="?package=<?php echo $_GET['package']; ?>&type=<?php echo $_GET['type']; ?>&action=postOrder&extraUsers=<?php echo $_GET['extraUsers']; ?>" class="btn pull-left"><?php _e("Go back", "ama"); ?></a>
<?php */ ?>
<a href="?package=<?php echo $_GET['package']; ?>&type=<?php echo $_GET['type']; ?>&action=postOrder&extraUsers=<?php echo $_GET['extraUsers']; ?>" class="btn pull-left <?php
/*if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) == parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)){ echo 'go-back'; } */
?>">
	<?php _e("Go back", "ama"); ?></a>

<?php if ( $enable_soumipankki && !empty($options['ama_order_kaupan_text'])) : ?>
<a href="javascript:;" class="pull-right showInfo"><?php _e("Kaupan toimtusehdot", "ama"); ?></a>


<div style="display:none;">
<div id="terms112">
<div style="width:500px">
<?php echo $options['ama_order_kaupan_text']; ?>
</div>
</div>
</div>

<?php endif; ?>
		</div>
	</div>
</main>
<?php
	$return = ob_get_contents();
	ob_clean();
	return $return;
}