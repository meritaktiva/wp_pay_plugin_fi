<?php
function amaPaymentSuccess()
{
	ob_start();

	$meta = get_post_meta($_REQUEST['order']);
	$order = fixOrderArray($meta);
	$coupong = get_post_meta($order['coupong']);
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


		<div class="bg-white section__small" id="checkoutForm">
            <div class="container">
               <div class="row">

<div class="alert alert-success">
<strong><?php _e("Payment was successful!", "ama"); ?></strong>
<br />
<?php _e("We have have received your order. We will contact u shortly.", "ama"); ?>
</div>
<h2><?php _e("Order details", "ama"); ?></h2>
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
	    <td><strong><?php _e("Index", "ama"); ?></strong></td>
	    <td><?php echo $order['index']; ?></td>
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
	    <td><strong><?php _e("Phone", "ama"); ?></strong></td>
	    <td><?php echo $order['phone']; ?></td>
 	</tr>
		<tr>
	    <td><strong><?php _e("Your e-mail address to log into the program", "ama"); ?></strong></td>
	    <td><?php echo $order['loginEmail']; ?></td>
	</tr>
</table>

</div>
				
				<div class="row">
					<?php include_once( 'buytracking.php' ); ?>
				</div>
				
			</div>
		</div>
</main>

<?php
	$return = ob_get_contents();
	ob_clean();
	return $return;
}