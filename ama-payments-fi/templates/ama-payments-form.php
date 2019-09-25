<?php function ama_payments_form() { ?>
<?php ob_start(); ?>
<?php
	global $post;

	loadJavascript();
	
	//payment.verkkomaksut.fi/js/payment-widget-v1.0.min.js

	//reset order
	unset($_SESSION['ama_order_id']);

    $lang_prefix = ICL_LANGUAGE_CODE;
    $options = get_option("ama_payments_options_" . $lang_prefix);

	$defaultPrice = $options['ama_default_price'];

    if(isset($_GET['payment']) && $_GET['payment']) {
    	$defaultPrice = $_GET['payment'];
    }
?>

<style>
.oldprice {
	color: #ccc;
	font-size:1.2rem;
}
</style>

	<main role="main">
         <div class="main-hero__section section__medium bg-checkout text-center">
            <div class="container">
               <div class="row">
				     <div class="col-xs-12">
                     <h1 class="sr-only"><?php the_title();  ?></h1>
                     <h2 class="page-title hero-title text-light"><?php echo get_post_meta( get_the_ID(), '_cmb_checkout_slogan', true ); ?></h2>
                  </div>
               </div>
            </div>
         </div>
      <script type="text/javascript">
		var packages = {};
      var initialState =  <?php echo ($defaultPrice == 1) ? 'true' : 'false'; ?>;
		</script>
<?php
$args = array(
	'post_type' => 'ama_packages',
	'orderBy' => 'id',
	'order' => 'ASC',
	'suppress_filters' => '0'
);

$packages = get_posts($args);
$first = true;
$i = 1;
$row = 0;

if (count($packages) > 0){
?>
	<div class="main-content__section main-content-checkout bg-light">
				<div class="container">
					<p><?php echo get_post_meta( get_the_ID(), '_cmb_pre_text', true ); ?></p>
				</div>
            <h3 class="sr-only"><?php _e('Packages','merit');?></h3>
            <div class="container">
            	<div class="row">
<?php
foreach ( $packages as $package ){
	$i = 1;
	$meta = get_post_meta($package->ID);
	$coupon = getCoupons($package->ID);
	#print_r($coupon);die();
	
	//prices
	$yearPrice = ($meta['year_price'][0])/12;
	$monthPrice = $meta['month_price'][0];
    $titleSize = $meta['package_title_size'][0];

	$displayPrice = '';
	
    //if ( empty($titleSize) ) $titleSize = '24';
	
	if ( empty($defaultPrice) || $defaultPrice == 1 ) {
		
		$price = $yearPrice;
		
		if ( empty($price) ) {
			$price = $monthPrice;
			//$price = calcNewMonthPrice($price);
		}
		if ( empty($price) ) $price = 0;
		if(!empty($coupon) && $coupon['type'] == 'discount'){
			$diff = ($price/100)*$coupon['value'];
			$displayPrice = number_format((float)$price-$diff, 2, '.', '');
		}
	}
	else {
		
		$price = $monthPrice;
		
		if ( !empty($price) )
		//$price = calcNewMonthPrice($price);

		if ( empty($price) ) {
			$price = $yearPrice;
		}
		if ( empty($price) ) $price = 0;
		
		if(!empty($coupon) && $coupon['type'] == 'discount'){
			$diff = ($price/100)*$coupon['value'];
			$displayPrice = number_format((float)$price-$diff, 2, '.', '');
		}

	}
	
	
	$yearExtraUserPrice = ($meta['year_extra_user_price'][0])/12;
	$monthExtraUserPrice = $meta['month_extra_user_price'][0];
	
	// lisakasutajatele ka kupongihind?

	$noExtraUsers = 0;
	if ( empty($yearExtraUserPrice) && empty($monthExtraUserPrice) ) $noExtraUsers = 1;

	//extras
	$lines = get_post_meta($package->ID, 'package_description');

	//free package
	$free_package = (int)$meta['free_package'][0];
	$file = $meta['file_url'][0];
	$color = $meta['color_class'][0];
	$package_info_url = $meta['package_info_url'][0];
	$month_rent = (int)$meta['month_rent'][0];
?>

<script type="text/javascript">
	packages[<?php echo $row; ?>] = {
		id : <?php echo $package->ID; ?>,
		couponValue : <?php echo !empty($coupon) && !empty($coupon['value']) && $coupon['type'] == 'discount'?$coupon['value']:0; ?>,
		yearPrice : <?php echo !empty($yearPrice)?$yearPrice:0; ?>,
		yearPriceText: "<?php echo preg_replace('/\s+/', ' ',$meta['year_price_text'][0]); ?>",
		yearExtraUserPrice: <?php echo !empty($yearExtraUserPrice)?$yearExtraUserPrice:0; ?>,
		monthPrice: <?php echo !empty($monthPrice)?$monthPrice:0; ?>,
		monthPriceText: "<?php echo preg_replace('/\s+/', ' ',$meta['month_price_text'][0]); ?>",
		monthExtraUserPrice: <?php echo !empty($monthExtraUserPrice)?$monthExtraUserPrice:0; ?>,
		freePackage: <?php echo $free_package; ?>,
		monthRent: <?php echo $month_rent; ?>,
		monthText: "<?php _e("month", "ama"); ?>",
		yearText: "<?php _e("year", "ama"); ?>"
	};
</script>

<form action="?package=<?php echo $package->ID; ?>&type=year" data-cleanurl="?package=<?php echo $package->ID; ?>&type=" method="POST" id="form<?php echo $row; ?>">
<input type="hidden" name="formAction" value="order" autocomplete="off"/>
<input type="hidden" name="action" value="postOrder"  autocomplete="off"/>
<input type="hidden" name="lang" value="<?php echo ICL_LANGUAGE_CODE; ?>"  autocomplete="off"/>
<input type="hidden" name="order[noExtraUsers]" value="<?php echo $noExtraUsers; ?>"  autocomplete="off"/>
<input type="hidden" name="order[package]" id="packageId" value="<?php echo $package->ID; ?>" autocomplete="off" />
<input type="hidden" name="order[packagePriceText]"  id="packagePriceText<?php echo $row; ?>" value="" autocomplete="off" />
<input type="hidden" name="order[packageName]" value="<?php echo $package->post_title; ?>"  autocomplete="off"/>
<input type="hidden" class="formType" id="formType" name="order[type]" value="<?php if ( empty($defaultPrice) || $defaultPrice == 1 ) : ?>1<?php else: ?>2<?php endif; ?>" autocomplete="off"/>
<input type="hidden" id="extraUsers<?php echo $row; ?>" class="extraUsersCount" name="order[extraUsers]" value="1" autocomplete="off" />
<input type="hidden" id="formTotal<?php echo $row; ?>" name="order[total]" value="<?php echo $price; ?>" autocomplete="off" />
<input type="hidden" name="order[redirectUrl]" value="<?php echo get_permalink($post->ID); ?>" autocomplete="off" />

					<?php
					$hasExtraUsersOption = (!empty($yearExtraUserPrice) && (empty($defaultPrice) || $defaultPrice == 1)) || ( !empty($monthExtraUserPrice) && $defaultPrice == 2 );
					$package_type = '';
					if ( $free_package==1){
						$package_type = 'free';
					} else if ($hasExtraUsersOption){
						$package_type = 'pro';
					} else {
						$package_type = 'standard';
					}
					?>

				  <div class="col-xs-12 col-md-4">
                     <figure class="checkout-section package-<?php echo $package_type; ?>" id="<?php echo $row; ?>">
                        <div class="checkout-section__header" data-mh="packageHeader">
                           <div class="checkout-section__header-inner">
                              <h4 class="checkout-section__title" <?php if (!empty($titleSize))echo ' style="font-size: '.$titleSize.'px"';?> >
                              <?php
                              if ($package_info_url && !empty($package_info_url)){
      									echo '<a href="'.$package_info_url.'" target="_blank">';
      								}
                              echo $package->post_title;
                              if ($package_info_url && !empty($package_info_url)){
      									echo '</a>';
      								}
                              ?>
                              </h4>
                              <span class="checkout-section__price" <?php if (!empty($titleSize))echo ' style="font-size: '.$titleSize.'px"'; ?> >
							  
									<?php if(!empty($displayPrice)):?>
										<span class="main_price" id="mainPrice<?php echo $row; ?>"<?php  if (!empty($titleSize))echo ' style="font-size: '.$titleSize.'px"'; ?>>
											<span class="realprice oldprice"><?php echo $price; ?> € </span> <?php echo $displayPrice; ?>
										</span>							  
									<?php else:?>
										<span class="main_price" id="mainPrice<?php echo $row; ?>"<?php  if (!empty($titleSize))echo ' style="font-size: '.$titleSize.'px"'; ?>>
											<span class="realprice"><?php echo $price; ?></span>
										</span>
									<?php endif;?>
							  
                                 
								 <span class="euro-char">
                                 <?php
                                 if($price==0)
                                    _e("€", "ama");
                                 else
                                    _e("€", "ama");
                                  ?>
								</span>
								<small><?php echo  _e("(+alv)/kuukausi", "ama");?></small>
                              </span>
                           </div>
                           <?php if ( $free_package==0){?>
                           <div class="checkout-section__header-inner">
                              <div class="btn-group radio-group" data-toggle="buttons">
                                 <label class="btn btn-radio alignleft changeTime <?php if ($defaultPrice == 2 ) echo 'active'; ?>">
                                    <input id="payMonthly" name="changeTimeRadio" type="radio" <?php if ($defaultPrice == 2 ) echo 'checked'; ?> data-type="month">
                                    <?php echo get_post_meta( get_the_ID(), '_cmb_checkout_monthly', true ); ?>
                                 </label>
                                 <label class="btn btn-radio alignright changeTime <?php if ( empty($defaultPrice) || $defaultPrice == 1 ) echo 'active'; ?>">
                                    <input id="payAnnually" name="changeTimeRadio" type="radio" <?php if ( empty($defaultPrice) || $defaultPrice == 1 ) echo 'checked'; ?> data-type="year">
                                    <?php echo get_post_meta( get_the_ID(), '_cmb_checkout_annually', true ); ?>
                                 </label>
                              </div>
                           </div>
                           <? } ?>
                        </div>
                        <div class="checkout-section__content<?php if ($hasExtraUsersOption){ echo ' text-light'; } ?>"  data-mh="packageBody">
                           <?php
                           if ( $free_package==1){
                           		if ( !empty($file) ){ ?>
                           <a href="<?php echo $file; ?>" target="_blank" class="btn btn-block btn-square btn-border-gray wayra" data-row="<?php echo $row; ?>"><span class="btn-text"><?php echo get_post_meta( get_the_ID(), '_cmb_checkout_btn_download', true ); ?></span></a>
                           <?php
								}
						   } else if ($hasExtraUsersOption){
						   ?>
							<div class="number-select">
								<div class="form-group number-select__input-helper">
								   <label class="control-label" for="usersNr">
                              <?php echo get_post_meta( get_the_ID(), '_cmb_checkout_users', true ); ?>
                           </label>
                           <div>
                              <button type="button" class="btn number-select__btn btn-minus"></button>
                              <input type="number" id="usersNr" class="form-control number-select__input" value="1" min="1" <?php /* max="5"*/ ?> data-row="<?php echo $row; ?>">
                              <button type="button" class="btn number-select__btn btn-plus"></button>
                           </div>
                        </div>
                           <div class="form-group number-select__submit">
                              <button class="btn btn-submit btn-square btn-pink border-white" data-row="<?php echo $row; ?>" data-toggle="collapse" data-target="#checkoutForm" aria-expanded="false" aria-controls="checkoutForm">
                                 <span class="btn-text"><?php echo get_post_meta( get_the_ID(), '_cmb_checkout_btn_order', true ); ?></span>
                                 <img src="<?=_PLUGIN_URL_?>/images/ajax-loader.gif" class="order-form-loader" style="display: none;">
                              </button>
                           </div>
                     </div>
                     <?php
                     } else {
						   ?>
                           <button class="btn btn-submit btn-block btn-square btn-border-pink wayra" data-row="<?php echo $row; ?>" data-toggle="collapse" data-target="#checkoutForm" aria-expanded="false" aria-controls="checkoutForm">
                              <span class="btn-text"><?php echo get_post_meta( get_the_ID(), '_cmb_checkout_btn_order', true ); ?></span>
                             <img src="<?=_PLUGIN_URL_?>/images/ajax-loader.gif" class="order-form-loader" style="display: none;">
                           </button>
                           <?php
						   }

                           if (count($lines) > 0){
								echo '<ul class="checkout-section__list">';
								foreach ($lines as $line){

                           if(substr($line, -1)=="*")
                              echo '<li class="inactive">'.substr($line, 0, -1).'</li>';
                           else
                              echo '<li>'.$line.'</li>';
								}
								echo '</ul>';
						   }
                           ?>

                           <?php if ($hasExtraUsersOption){ ?>
                           <div class="extraUsers">
	                           <p class="package-info year-price-text" style="<?php if($defaultPrice==2):?>display:none;<?php endif; ?>">
	                           		<span class="icon icon-info"></span>
				                     <?php echo $meta['package_extra_user_info_year'][0]; ?>
				               </p>
				               <p class="package-info month-price-text" style="<?php if($defaultPrice==1):?>display:none;<?php endif; ?>">
				                 	<span class="icon icon-info"></span>
				                    <?php echo $meta['package_extra_user_info_month'][0]; ?>
				               </p>
			               </div>
                           <?php } ?>
                        </div>
                     </figure>
                  </div>

</form>
<?php
	++$row;
	$first = false;
}
?>
			</div>
        </div>
</div>
<?php
}
?>

		        <div id="order_view">
				</div>

         <div class="bg-blue section__small text-light text-center">
            <div class="container">
               <div class="row">
                  <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                     <h3 class="section-title text-light"><?php echo get_post_meta( get_the_ID(), '_cmb_guarantee_title', true ); ?></h3>
                     <p><?php echo get_post_meta( get_the_ID(), '_cmb_guarantee_text', true ); ?></p>
                     <?php
                     $attachment_id = get_post_meta( get_the_ID(), '_cmb_guarantee_img_id', true );
                     echo wp_get_attachment_image( $attachment_id, array('class' => 'icon icon-moneyback'));
                     ?>

                  </div>
               </div>
            </div>
         </div>

        <?php /* ?>
         <div id="summary_view">

		</div>
		<?php */ ?>

      </main>




<script type="text/javascript">
var pluginUrl = '<?php echo _PLUGIN_URL_; ?>';
var translatePleaseWait = '<?php echo _e("Please wait..."); ?>';

<?php if(isset($_GET['type'])):?>
document.addEventListener("DOMContentLoaded", function() {
    //$("[data-row=<?=$_GET['type']?>]").addClass("lol");
	jQuery('[data-row="<?=$_GET['type']?>"]').click();
});
<?php endif;?>
</script>

<?php
	$return = ob_get_contents();
	ob_clean();
	return $return;
 ?>
<?php } ?>
