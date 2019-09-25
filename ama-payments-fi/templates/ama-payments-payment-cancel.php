<?php
function amaPaymentCancel()
{
	ob_start();
	
	
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
               
					<div class="alert alert-warning">
					<strong><?php _e("Payment was canceled!", "ama"); ?></strong>
					<br />
					<?php _e("We are sorry that you canceled your order.", "ama"); ?>
					</div>
					
				</div>
			</div>
		</div>
</main>

<?php 	 
	$return = ob_get_contents();
	ob_clean();
	return $return;
}