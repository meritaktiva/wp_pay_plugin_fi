<?php

function amaPaymentsPrepareOrderByParameters()
{
    global $post;

    $return = array();

    if(empty($_GET['package'])) {
        wp_die(__( "Wrong package id", "ama" ) );
    }

    $package = get_post($_GET['package']);
    if(empty($package)) {
        wp_die(__( "Wrong package id", "ama" ) );
    }

    $return['package'] = $package->ID;
    $return['packageName'] = $package->post_title;
    $return['type'] = $_GET['type']=='year'?1:2;
    $return['redirectUrl'] = get_permalink($post->ID);

    $meta = get_post_meta($package->ID);
   	$yearPrice = $meta['year_price'][0];
	$monthPrice = $meta['month_price'][0];
	$yearExtraUserPrice = $meta['year_extra_user_price'][0];
	$monthExtraUserPrice = $meta['month_extra_user_price'][0];
    $yearPriceText =  preg_replace('/\s+/', ' ',$meta['year_price_text'][0]);
    $monthPriceText = preg_replace('/\s+/', ' ',$meta['month_price_text'][0]);


    $noExtraUsers = 0;
	if ( empty($yearExtraUserPrice) && empty($monthExtraUserPrice) ) $noExtraUsers = 1;

    $return['noExtraUsers'] = $noExtraUsers;
    if(!empty($_GET['extraUsers']) && (int)$_GET['extraUsers']!=0) {
    $return['extraUsers'] = (int)$_GET['extraUsers'];
    }

    if($return['type']==2) {
        $return['packagePriceText'] = $monthPriceText;
        $return['total'] = $monthPrice;
        if($return['extraUsers']) {
             $return['total'] += ($monthExtraUserPrice*$return['extraUsers']);
        }
    }
    else {
        $return['packagePriceText'] = $yearPriceText;
        $return['total'] = $yearPrice;
        if($return['extraUsers']) {
             $return['total'] += ($yearExtraUserPrice*$return['extraUsers']);
        }
    }

    if($return['extraUsers']) {
       $return['extraUsers'] += 1;
    }

    return $return;
}

function amaPaymentsOrderFormBefore()
{
    global $sitepress;
    $current_lang = $sitepress->get_current_language();
    $_POST['order']['lang']  = $current_lang;

    if(!empty($_GET['package']) && !isset($_POST['order']['package'])) {
        $_POST['order'] = amaPaymentsPrepareOrderByParameters();
    }
    else {
        if(empty($_POST['order']['package']) ) {
            wp_die(__( "Wrong package id", "ama" ) );
        }
    }

    $_POST['order']['orig_total'] = $_POST['order']['total'];

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

    $total =  calcAlv($_POST['order']['total'], $current_lang);
    $_POST['order']['total'] = number_format($total['price'],2,',','');
    $_POST['order']['vat'] = $total['vat'];
    $_POST['order']['price_vat'] = $total['price_vat'];
    $_POST['order']['extra_user_total'] = ($extraUserPrice*($_POST['order']['extraUsers']-1));
    $_POST['order']['total_without_tax'] = $_POST['order']['extra_user_total']+$_POST['order']['package_price'];
     if ( $_POST['order']['type'] == 2) {
        $_POST['order']['extra_user_total'] = calcNewMonthPrice($_POST['order']['extra_user_total']);
    }
}
function amaPaymentsOrderForm($order)
{
	ob_start();

    loadJavascript();

    global $sitepress;
    $lang_prefix = $sitepress->get_current_language();
    $options = get_option("ama_payments_options_" . $lang_prefix);
	$enable_soumipankki = $options['ama_enable_soumipankki'];
	$enable_polish_bank = $options['ama_enable_polish_bank'];
	$enable_invoice_order = $options['ama_enable_invoice_order'];
?>

<?php if (!isset($_REQUEST['ajaxRequest'])){ ?>
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
<?php } ?>

		<div class="bg-white section__small" id="checkoutForm">
            <div class="container">
               <div class="row">
                  <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
                     <form id="paymentForm" role="form" action="?package=<?php echo $_GET['package']; ?>&type=<?php echo $_GET['type']; ?>&extraUsers=<?php echo $_GET['extraUsers']; ?>&payment=invoice" data-cleanurl="?package=<?php echo $_GET['package']; ?>&type=<?php echo $_GET['type']; ?>&extraUsers=<?php echo $_GET['extraUsers']; ?>&payment=" method="POST" class="order-form">
						<input type="hidden" name="action" value="saveOrder" />
						<input type="hidden" name="lang" value="<?php echo $order['lang']; ?>" />
						<input type="hidden" name="order[package]" value="<?php echo $order['package']; ?>" />
						<input type="hidden" id="activePackageId" name="order[package]" value="<?php echo $order['package']; ?>" />
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
						<input type="hidden" name="order[orig_total]" value="<?php  echo  $order['orig_total']; ?>" />
						<input type="hidden" id="formPayment" name="order[payment]" value="<?php if ( $enable_soumipankki == 1 ) :?>suomi<?php else: ?>invoice<?php endif; ?>"  checked="checked"/>
						<input id="coupongValue" type="hidden" name="order[coupong]" value="<?php if(isset($_SESSION['coupong']) && $_SESSION['coupong']):?><?php  echo  $_SESSION['coupong']['id']; ?><?php endif;?>" />
						<input type="hidden" name="order[discount]" value="<?php if(isset($_POST['order']['discount']) && $_POST['order']['discount']):?><?php  echo  $_POST['order']['discount']; ?><?php endif;?>" />
						<input type="hidden" name="order[coupongValue]" value="<?php if(isset($_SESSION['coupong']['value']) && $_SESSION['coupong']['value']):?><?php  echo  $_SESSION['coupong']['value']; ?><?php endif;?>" />
						<input type="hidden" id="coupongDefault" name="order[coupongDefault]" value="<?php if(isset($_SESSION['coupong']['default']) && $_SESSION['coupong']['default']):?><?php  echo  $_SESSION['coupong']['default']; ?><?php endif;?>" />
						<input type="hidden" name="order[activeFormType]" id="activeFormType" value="<?php echo $_POST['order']['type'];?>" />

                        <h3 class="section-title text-center"><?php _e('Add customer details','merit');?></h3>
                        <div class="row">
                           <div class="col-xs-12 col-sm-6">
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['company']) && $_SESSION['order']['company']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="company">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Company", "ama"); ?>
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="text" id="company" name="order[company]" class="form-control form-control-jiro" value="<?php if(isset($_SESSION['order']['company']) && $_SESSION['order']['company']) echo $_SESSION['order']['company']; else echo $order['company']; ?>">
                              </div>
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['ytunnus']) && $_SESSION['order']['ytunnus']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="ytunnus">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Y-tunnus", "ama"); ?>
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="text" id="ytunnus" name="order[ytunnus]" class="form-control form-control-jiro" value="<?php if(isset($_SESSION['order']['ytunnus']) && $_SESSION['order']['ytunnus']) echo $_SESSION['order']['ytunnus']; else echo $order['ytunnus']; ?>">
                              </div>
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['name']) && $_SESSION['order']['name']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="name">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Name", "ama"); ?>*</label>
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="text" id="contactName" name="order[contactName]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['name']) && $_SESSION['order']['name']) echo $_SESSION['order']['name']; else echo $order['name']; ?>">
                              </div>
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['street']) && $_SESSION['order']['street']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="street">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Street", "ama"); ?>*
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="text" id="street" name="order[street]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['street']) && $_SESSION['order']['street']) echo $_SESSION['order']['street']; else echo $order['street']; ?>">
                              </div>
                           </div>
                           <div class="col-xs-12 col-sm-6">
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['index']) && $_SESSION['order']['index']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="index">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Index", "ama"); ?>*
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="text" id="index" name="order[index]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['index']) && $_SESSION['order']['index']) echo $_SESSION['order']['index']; else echo $order['index']; ?>">
                              </div>
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['city']) && $_SESSION['order']['city']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="city">
                                  <span class="control-label-jiro-content">
                                    <?php _e("City", "ama"); ?>*
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="text" id="city" name="order[city]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['city']) && $_SESSION['order']['city']) echo $_SESSION['order']['city']; else echo $order['city']; ?>">
                              </div>
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['phone']) && $_SESSION['order']['phone']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="phone">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Phone", "ama"); ?>*
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="phone" id="phone" name="order[phone]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['phone']) && $_SESSION['order']['phone']) echo $_SESSION['order']['phone']; else echo $order['phone']; ?>">
                              </div>
                           </div>
                        </div>
												<div class="row">
                           <div class="col-xs-12 col-sm-12">
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['email']) && $_SESSION['order']['email']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="email">
                                  <span class="control-label-jiro-content">
                                    <?php _e("E-mail for contacting you and to send invoices", "ama"); ?>*
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="email" id="email" name="order[email]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['email']) && $_SESSION['order']['email']) echo $_SESSION['order']['email']; else echo $order['email']; ?>">
                              </div>
                           </div>
                           <div class="col-xs-12 col-sm-12">
                              <div class="form-group form-group-jiro <?php if(isset($_SESSION['order']['loginEmail']) && $_SESSION['order']['loginEmail']) echo 'input-filled';?>">
                                <label class="control-label control-label-jiro" for="email">
                                  <span class="control-label-jiro-content">
                                    <?php _e("Your e-mail address to log into the program", "ama"); ?>*
                                  </span>
                                </label>
                                <input onchange="saveToSession(this);" type="email" id="loginEmail" name="order[loginEmail]" class="form-control form-control-jiro" required value="<?php if(isset($_SESSION['order']['loginEmail']) && $_SESSION['order']['loginEmail']) echo $_SESSION['order']['loginEmail']; else echo $order['loginEmail']; ?>">
                              </div>
	                        </div>
                        </div>
                        <h3 class="section-title text-center"><?php _e('Your order','ama');?></h3>
                        <p><?php if($_POST['order']['type'] == 2):?><?php echo $order['packagePriceText'];?><?php endif;?></p>
                        <div class="row">
                        	<?php if(haveCopongs($order['package'])):?>
	                        <div class="col-xs-12 col-sm-12">
	                        	<table class="table borderless">
		                        	<tr>
		                        		<td style="width: 100%;">
			                           	<div class="form-group form-group-jiro <?php if(isset($_SESSION['coupong']) && $_SESSION['coupong']):?>input-filled<?php endif;?>">
			                           		<label class="control-label control-label-jiro" for="email">
																			<span class="control-label-jiro-content"> <?php _e("Enter coupong if you have it.", 'ama')?> </span>
																		</label>
				                           	<input type="text" onkeyup="showValidateButton()" id="coupongCode" value="<?php if(isset($_SESSION['coupong']) && $_SESSION['coupong']):?><?=$_SESSION['coupong']['code']?><?php endif;?>" class="form-control form-control-jiro">
					                        </div>
		                           	</td>
		                           <td>
				  	                      <a href="javascript:void(0);" id="validateCoupong" onclick="validateCoupong();" class="btn btn-small btn-square btn-border-pink btn-table <?php if(!isset($_SESSION['coupong']) || empty($_SESSION['coupong'])):?>hidden<?php endif;?>"><span class="btn-text"><?php _e('Käytä', 'ama');?></span></a>
		                           </td>
		                        	</tr>
		                        	<tr>
		                           <td>
				                          <div id="error"></div>
		                           </td>
		                           <td></td>
		                        	</tr>
	                        	</table>
	                         </div>
	                         <?php endif;?>
                           <div class="col-xs-12">
                              <table class="table" id="checkoutTable">
                                 <thead>
                                    <tr>
                                       <th><?php _e('Package','ama');?></th>
                                       <th><?php _e('Period','ama');?></th>
                                       <th><?php _e('Users','ama');?></th>
                                       <th class="text-right"><?php _e("Price", "ama"); ?></th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <tr>
                                       <td><?php echo $order['packageName']; ?>  <?php if ($order['type']==1 ) :?><?php _e("annualy", "ama"); ?><?php else: ?><?php _e("monthly", "ama"); ?><?php endif; ?></td>
										<?php if($order['type'] == 1):?>
											<?php $date = date('Y-m-d');?>
											<?php $tomorrow = date('d-m-Y', strtotime('+1 day', strtotime($date)));?>
											<?php $nextYear = date('d-m-Y', strtotime('+1 year -1 day', strtotime($tomorrow)));?>
											<?php $value = str_replace('-', '.', $tomorrow) . ' - ' . str_replace('-', '.', $nextYear);?>
											<td>
												<input name="order[orderPeriod]" type="hidden" value="<?=$value?>">
												<?=$value?>
											</td>
										<?php else:?>
											<?php $date = date('d-m-Y');?>
											<?php $tomorrow = date('d-m-Y', strtotime('+1 day', strtotime($date)));?>
											<?php
												$dayNum = strtolower(date("d",strtotime($date)));
												$dayNumber = floor($dayNum);
												$base = strtotime(date('Y-m',time()) . '-01 00:00:01');
												if($dayNumber < 14) {
													$nextMonth = date("t-m-Y", strtotime("now"));
												} else {
													$nextMonth = date("t-m-Y", strtotime("+1 month", $base));
												}
												$value = str_replace('-', '.', $tomorrow) . ' - ' . str_replace('-', '.', $nextMonth);
											?>
											<td>
												<input name="order[orderPeriod]" type="hidden" value="<?=$value?>">
												<?=$value?>
											</td>
										<?php endif;?>
                                       <td>1</td>
                                       <td class="text-right">
                                       	<input type="hidden" name="order[packagePrice]" value="<?php echo number_format($order['package_price'], 2, ',', ''); ?>">
                                       	<?php echo number_format($order['package_price'], 2, ',', ''); ?> <?php _e("€", "ama"); ?>
                                       </td>
                                    </tr>
                                    <?php if ( $order['noExtraUsers'] == 0 && $order['extraUsers'] > 1 ) : ?>
                                    <tr>
                                       <td><?php _e("Extra user", "ama"); ?></td>
                                       <td></td>
                                       <td><?php echo ($order['extraUsers']-1); ?></td>
                                       <td class="text-right">
	                                       <input type="hidden" name="order[extraUserTotal]" value="<?php echo number_format($order['extra_user_total'], 2, ',', ''); ?>">
                                         <?php echo number_format($order['extra_user_total'], 2, ',', ''); ?> <?php _e("€", "ama"); ?>
                                       </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if(isset($_SESSION['coupong']) && $_SESSION['coupong']):?>
                                    	<?php if($_SESSION['coupong']['type'] == 'discount'):?>
		                                    <tr>
		                                    	<td><?=$_SESSION['coupong']['name']?></td>
		                                    	<td></td>
   	                                      <td></td>
		                                    	<td class="text-right">
			                                    	<input type="hidden" name="order[discount]" value="<?=$_POST['order']['discount']?>">
			                                    	- <?=$_POST['order']['discount']?> <?php _e("€", "ama"); ?>
		                                    	</td>
		                                    </tr>
	                                    <?php else:?>
		                                    <?php if(isset($nextYear) && $nextYear) {
												$start = date("d-m-Y", strtotime($nextYear . " +1day"));
												$end = date("d-m-Y", strtotime($start . "+" . $_SESSION['coupong']['value']  . " month -1day"));
												$value = str_replace('-', '.', $start) . ' - ' . str_replace('-', '.', $end);
											} else {
												$start = date("d-m-Y", strtotime($nextMonth . " +1day"));
												$end = date("d-m-Y", strtotime($start . "+" . $_SESSION['coupong']['value']  . " month -1day"));
												$value = str_replace('-', '.', $start) . ' - ' . str_replace('-', '.', $end);
											}?>
		                                    <tr>
		                                    	<td><?=$_SESSION['coupong']['name']?></td>
		                                    	<td><input name="order[orderDiscountPeriod]" type="hidden" value="<?=$value?>"><?=$value?></td>
	   	                                        <td></td>
		                                    	<td></td>
		                                    </tr>
	                                    <?php endif;?>
                                    <?php endif;?>
                                    <tr>
                                       <td><?php _e("Without vat", "ama"); ?></td>
                                       <td></td>
                                       <td></td>
                                       <td class="text-right">
                                       	<input type="hidden" name="order[withoutTax]" value="<?php echo number_format($order['total_without_tax'], 2, ',', ''); ?>">
                                       	<?php echo number_format($order['total_without_tax'], 2, ',', ''); ?> <?php _e("€", "ama"); ?>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td><?php _e("Vat", "ama"); ?> <?php echo $order['vat']; ?>%</td>
                                       <td></td>
                                       <td></td>
                                       <td class="text-right">
	                                       <input type="hidden" name="order[tax]" value="<?php echo number_format($order['price_vat'], 2, ',', ''); ?>">
	                                       <?php echo number_format($order['price_vat'], 2, ',', ''); ?> <?php _e("€", "ama"); ?>
                                       </td>
                                    </tr>
                                 </tbody>
                                 <tfoot>
                                    <tr>
                                       <td><?php _e("Total", "ama"); ?></td>
                                       <td></td>
                                       <td></td>
                                       <td class="text-right"><?php echo $order['total']; ?> <?php _e("€", "ama"); ?></td>
                                    </tr>
                                 </tfoot>
                              </table>
                           </div>
                        </div>
                        <?php if(isset($_SESSION['coupong']) && $_SESSION['coupong']) {unset($_SESSION['coupong']);}?>
                        <h3 class="section-title text-center"><?php _e('Pay with','ama');?></h3>
                        <div class="row">
                        	<?php if ( $enable_invoice_order == 1 ) :?>
                           <div class="col-xs-12 col-sm-6 col-md-5 col-md-offset-1 col-lg-4 col-lg-offset-2">
                                 <button class="btn btn-block btn-square btn-border-gray wayra" type="submit"  data-payment="invoice" onclick="setFormPayment('invoice');$j('#formPayment').val('invoice');"><span class="btn-text"><?php _e('Invoice','ama');?></span></button>
                           </div>
                           <?php endif; ?>
                           <?php if ( $enable_soumipankki == 1) :?>
                           <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                                 <button class="btn btn-block btn-square btn-border-gray wayra" type="submit" data-payment="suomi" onclick="setFormPayment('suomi');$j('#formPayment').val('suomi');"><span class="btn-text"><?php _e('Bank link','ama');?></span></button>
                           </div>
                           <?php endif; ?>
                           <?php if ( $enable_polish_bank == 1) :?>
                           <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                           		<button class="btn btn-block btn-square btn-border-gray wayra" type="submit" data-payment="polish" onclick="setFormPayment('polish');$j('#formPayment').val('polish');"><span class="btn-text"><?php _e('Bank link','ama');?></span></button>
												   </div>
												   <?php endif; ?>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>

<?php if (!isset($_REQUEST['ajaxRequest'])){ ?>
         <div class="bg-blue section__small text-light text-center">
            <div class="container">
               <div class="row">
                  <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                     <h3 class="section-title text-light"><?php echo get_post_meta( get_the_ID(), '_cmb_guarantee_title', true ); ?></h3>
                     <p><?php echo get_post_meta( get_the_ID(), '_cmb_guarantee_text', true ); ?></p>
                     <span class="icon icon__moneyback"></span>
                  </div>
               </div>
            </div>
         </div>
</main>
<?php } ?>

<?php /* ?>
<script>jQuery(function(){
    jQuery.validator.messages.required = translator.thisFieldIsRequired;
	jQuery('.order-form').validate({
	rules: {
    		'order[email]': {
    			required: true,
    			email: true
    		}
	},
	messages: {
    		'order[email]': translator.wrongEmailAddress
	},
	submitHandler : function (form) {
        jQuery('.order-form').sumbit();
		}
		});

});</script>
<?php */ ?>

<?php
	$return = ob_get_contents();
	ob_clean();
	return $return;
}