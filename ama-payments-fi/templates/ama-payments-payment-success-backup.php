<?php
function amaPaymentSuccess()
{
	ob_start();

	$meta = get_post_meta($_REQUEST['order']);
	$order = fixOrderArray($meta);

?>
<div class="alert alert-success">
<strong><?php _e("Payment was successful!", "ama"); ?></strong>
<br />
<?php _e("We have have received your order. We will contact u shortly.", "ama"); ?>
</div>
<h2><?php _e("Order details", "ama"); ?></h2>
<div class="clear"></div>
<table class="table">
	<th><?php _e("Package", "ama"); ?></th>
	<th><?php _e("Users", "ama"); ?></th>
	<th align="right" class="text-to-right"><?php _e("Price", "ama"); ?></th>
    <tr>
		<td><?php echo $order['packageName']; ?>  <?php if ($order['type']==1 ) :?><?php _e("annualy", "ama"); ?><?php else: ?><?php _e("monthly", "ama"); ?><?php endif; ?></td>
		<td>1</td>
		<td align="right" class="text-to-right"><?php echo number_format($order['package_price'], 2, '.', ''); ?> <?php _e("€", "ama"); ?></td>
	</tr>
    <?php if ( $order['noExtraUsers'] == 0 && $order['extraUsers'] > 1 ) : ?>
    <tr>
        <td><?php _e("Extra user", "ama"); ?></td>
        <td><?php echo ($order['extraUsers']-1); ?></td>
        <td  align="right" class="text-to-right"><?php echo $order['extra_user_total']; ?> <?php _e("€", "ama"); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
		<td><?php _e("Vat", "ama"); ?> <?php echo $order['vat']; ?>%</td>
		<td></td>
		<td align="right" class="text-to-right"><?php echo number_format($order['price_vat'], 2, '.', ''); ?> <?php _e("€", "ama"); ?></td>
	</tr>
	<tr>
		<td><?php _e("Total", "ama"); ?></td>
		<td></td>
		<td align="right" class="text-to-right"><?php echo $order['total']; ?> <?php _e("€", "ama"); ?></td>
	</tr>
	<tr>
		<td colspan="3"><?php  echo  $order['packagePriceText']; ?></td>
	</tr>
</table>

<h2><?php _e("Customer details", "ama"); ?></h2>

<table class="table">
	<tr>
	    <td><strong><?php _e("Name", "ama"); ?></strong></td>
	    <td><?php echo $order['name']; ?></td>
 	</tr>
 	<tr>
	    <td><strong><?php _e("Y-tunnus", "ama"); ?></strong></td>
	    <td><?php echo $order['ytunnus']; ?></td>
 	</tr>
    <tr>
	    <td><strong><?php _e("Company", "ama"); ?></strong></td>
	    <td><?php echo $order['company']; ?></td>
 	</tr>
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


<?php
	$return = ob_get_contents();
	ob_clean();
	return $return;
}