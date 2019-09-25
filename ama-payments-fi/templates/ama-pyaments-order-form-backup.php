<?php
function amaPaymentsOrderForm($order)
{
	ob_start();

    global $sitepress;
    $lang_prefix = $sitepress->get_current_language();
    $options = get_option("ama_payments_options_" . $lang_prefix);
	$enable_soumipankki = $options['ama_enable_soumipankki'];
	$enable_polish_bank = $options['ama_enable_polish_bank'];
	$enable_invoice_order = $options['ama_enable_invoice_order'];
?>
<h2 class="pull-left heading"><?php _e("Order", "ama"); ?> </h2> <a href="javascript:;" class="btn pull-right goBackToPackages"><?php _e("Go back to package selection", "ama"); ?></a>
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

<h2><?php _e("Add customer details", "ama"); ?></h2>

<form action="" method="POST" class="form-horizontal order-form">
<input type="hidden" name="action" value="saveOrder" />
<input type="hidden" name="lang" value="<?php echo $order['lang']; ?>" />
<input type="hidden" name="order[package]" value="<?php echo $order['package']; ?>" />
<input type="hidden" name="order[packageName]" value="<?php  echo  $order['packageName']; ?>" />
<input type="hidden" name="order[packagePriceText]" value="<?php  echo  $order['packagePriceText']; ?>" />
<input type="hidden" name="order[extraUsers]" value="<?php   echo  $order['extraUsers']; ?>" />
<input type="hidden" name="order[total]" value="<?php  echo  $order['total']; ?>" />
<input type="hidden" name="order[type]" value="<?php   echo  $order['type']; ?>" />
<input type="hidden" name="order[redirectUrl]" value="<?php  echo  $order['redirectUrl']; ?>" />
<input type="hidden" name="order[package_price]" value="<?php echo $order['package_price']; ?>" />
<input type="hidden" name="order[price_vat]" value="<?php echo $order['price_vat']; ?>" />
<input type="hidden" name="order[vat]" value="<?php echo $order['vat']; ?>" />
<input type="hidden" name="order[extra_user_total]" value="<?php echo $order['extra_user_total']; ?>" />
<input type="hidden" id="formPayment" name="order[payment]" value="<?php if ( $enable_soumipankki == 1 ) :?>suomi<?php else: ?>invoice<?php endif; ?>"  checked="checked"/>


<div class="control-group">
	<label class="control-label" for="company"><?php _e("Company", "ama"); ?></label>
	<div class="controls">
		<input type="text" id="company" name="order[company]" class="input-xxlarge"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ytunnus"><?php _e("Y-tunnus", "ama"); ?></label>
	<div class="controls">
		<input type="text" id="ytunnus" name="order[ytunnus]" class="input-xxlarge"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label label-xlarge" for="name"><?php _e("Name", "ama"); ?> *</label>
	<div class="controls">
		<input type="text" id="name" name="order[name]" class="input-xxlarge required"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="street"><?php _e("Street", "ama"); ?> *</label>
	<div class="controls">
		<input type="text" id="street" name="order[street]" class="input-xxlarge required"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="index"><?php _e("Index", "ama"); ?></label>
	<div class="controls">
		<input type="text" id="index" name="order[index]" class="input-xxlarge"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="city"><?php _e("City", "ama"); ?></label>
	<div class="controls">
		<input type="text" id="city" name="order[city]" class="input-xxlarge"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="email"><?php _e("Email", "ama"); ?> *</label>
	<div class="controls">
		<input type="text" id="email" name="order[email]" class="input-xxlarge required"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="phone"><?php _e("Phone", "ama"); ?> *</label>
	<div class="controls">
		<input type="text" id="phone" name="order[phone]" class="input-xxlarge required"/>
	</div>
</div>

<div class="control-group">
<label class="control-label" for="phone">&nbsp;</label>
	<div class="controls">
    <i>* <?php _e("required fields", "ama"); ?></i>
    </div>
</div>

<h2><?php _e("Select payment method", "ama"); ?></h2>

<div class="control-group">
<div class="controls">

<?php if ( $enable_soumipankki == 1 ) :?>
<button class="btn btn-primary" type="submit" data-payment="suomi"  onclick="$j('#formPayment').val('suomi');"><?php _e("Pay now", "ama"); ?></button>
<?php endif; ?>

<?php if ( $enable_polish_bank == 1 ) :?>
<button class="btn btn-primary" type="submit"  data-payment="polish" onclick="$j('#formPayment').val('polish');"><?php _e("Pay now", "ama"); ?></button>
<?php endif; ?>

<?php if ( $enable_invoice_order == 1 ) :?>
<button class="btn btn-primary" type="submit"  data-payment="invoice" onclick="$j('#formPayment').val('invoice');"><?php _e("Order an invoice and pay later", "ama"); ?></button>
<?php endif; ?>
</div>
</div>

<div class="control-group">
	<div class="controls">
	<a href="javascript:;" class="btn pull-right goBackToPackages"><?php _e("Go back to package selection", "ama"); ?></a>
	</div>
</div>

</form>

<?php
	$return = ob_get_contents();
	ob_clean();
	return $return;
}