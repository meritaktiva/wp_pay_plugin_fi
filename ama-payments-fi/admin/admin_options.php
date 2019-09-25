<style>
.form-table th { width:300px;}
.form-table td textarea { width:300px; height:200px;}
.form-table td a { vertical-align:top;}
</style>
<h2><?php _e('Ama payements settings', 'ama');?></h2>	
<form method="post" action="options.php" enctype="multipart/form-data">
<?php settings_fields('ama-payments-options' ); ?>
<?php 
$lang_prefix = ICL_LANGUAGE_CODE;
$options = get_option('ama_payments_options_' . $lang_prefix);
?>

<table class="form-table">
<tr>
	<th scope="row" align="right"><?php _e('Choose default payment type', 'ama');?>:</th>
	<td>
		<select name="ama_payments_options_<?php echo $lang_prefix; ?>[ama_default_price]">
			 	<option value="1" <?php if ( $options['ama_default_price'] == 1 ) : ?>selected="selected"<?php endif; ?>><?php _e("Annually", "ama"); ?></option>
			 	<option value="2" <?php if ( $options['ama_default_price'] == 2 ) : ?>selected="selected"<?php endif; ?>><?php _e("Monthly", "ama"); ?></option>
		</select>
	</td>
		<tr>
			<th scope="row" align="right"><?php _e('Order email', 'ama');?>:</th>
			<td><input type="text" name="ama_payments_options_<?php echo $lang_prefix; ?>[ama_order_email]" value="<?php echo $options['ama_order_email']; ?>" /> <i><?php _e("All orders will be sent to this email.", "ama"); ?></i></td>
		</tr>
	</tr>
	
	<tr>
		<td colspan="2"><h2><?php _e("Payments"); ?></h2></td>
	</tr>
	<tr>
		<th scope="row" align="right"><?php _e('Enable soumipankki', 'ama');?>:</th>
		<td><input type="checkbox" name="ama_payments_options_<?php echo $lang_prefix; ?>[ama_enable_soumipankki]" value="1"  <?php echo amachecked('1',  $options['ama_enable_soumipankki']); ?>/></td>
	</tr>
	
	<tr>
		<th scope="row" align="right"><?php _e('Kaupan toimtusehdot text', 'ama');?>:</th>
		<td><textarea name="ama_payments_options_<?php echo $lang_prefix; ?>[ama_order_kaupan_text]" style="width:500px;height:400px;"><?php echo $options['ama_order_kaupan_text']; ?></textarea></td>
	</tr>
	
	<tr>
		<th scope="row" align="right"><?php _e('Enable invoice order', 'ama');?>:</th>
		<td><input type="checkbox" name="ama_payments_options_<?php echo $lang_prefix; ?>[ama_enable_invoice_order]" value="1"  <?php echo amachecked('1',  $options['ama_enable_invoice_order']); ?>/></td>
	</tr>
	
	<tr>
		<th scope="row" align="right"><?php _e('Enable Polish bank', 'ama');?>:</th>
		<td><input type="checkbox" name="ama_payments_options_<?php echo $lang_prefix; ?>[ama_enable_polish_bank]" value="1"  <?php echo amachecked('1',  $options['ama_enable_polish_bank']); ?>/></td>
	</tr>
	
	
</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>

	