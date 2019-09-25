<?php
function addTombstoneDefaults()
{
    echo '<script>
	var translator = {
		thisFieldIsRequired : "' . __("Tämä kenttä on pakollinen!", "ama") . '",
		wrongEmailAddress: 	"' . __("Virheellinen sähköpostiosoite!", "ama") . '"
	};
	</script>';
}

function add_post_enctype() {
    echo ' enctype="multipart/form-data"';
}

function haveCopongs($postId) {
    $coupongs = new WP_Query(array('post_type' => 'ama_coupongs','post_status' => 'publish'));
    foreach($coupongs->posts as $coupong) {
        $meta = get_post_meta($coupong->ID);
        if(isset($meta['package-'.$postId][0]) && $meta['package-'.$postId][0] == 1) {
            return true;
        }
    }
    return false;
}
#unset($_SESSION['coupong']);
function getCoupons($postId) { // apply by default
    $coupons = new WP_Query(array('post_type' => 'ama_coupongs','post_status' => 'publish'));
	$date = date("d-m-Y");

    foreach($coupons->posts as $coupon) {
        $meta = get_post_meta($coupon->ID);
        if(isset($meta['package-'.$postId][0]) && $meta['package-'.$postId][0] == 1 && !empty($meta['default'][0])) {
            if (empty($meta['start'][0]) && empty($meta['end'][0]) || strtotime($date) >= strtotime($meta['start'][0]) && strtotime($date) <= strtotime($meta['end'][0])) {
                $return['coupong'] = $coupon;
				$code = $meta['code'][0];
                break;
            }
        }
    }

	$meta = get_post_meta($return['coupong']->ID);

	if($code) {
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

        /*$_SESSION['coupong']['id'] = $coupon->ID;
        $_SESSION['coupong']['name'] = $meta['name'][0];
        $_SESSION['coupong']['code'] = $code;
        $_SESSION['coupong']['type'] = $return['type'];
        $_SESSION['coupong']['value'] = $return['value'];
        $_SESSION['coupong']['default'] = 1;*/

		return $return;
    }

    return false;
}

function ama_product_save_post($post_id, $post)
{

    if ( !isset( $_POST['ama_product_mark_nonce'] ) || !wp_verify_nonce( $_POST['ama_product_mark_nonce'], basename( __FILE__ ) ) )
        return $post_id;


    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    if ( $post_type->rewrite['slug'] == 'ama_packages' ) {
        saveAmaPackages($post_id, $post);
    }

    if ( $post_type->rewrite['slug'] == 'ama_orders' ) {
        saveAmaOrders($post_id, $post);
    }

    if ( $post_type->rewrite['slug'] == 'ama_coupongs' ) {
        saveAmaCoupongs($post_id, $post);
    }

}

function saveAmaPackages($post_id, $post)
{
    if ( empty($_POST['package']['is_salary']) ) $_POST['package']['is_salary'] = 0;
    if ( empty($_POST['package']['free_package']) ) $_POST['package']['free_package'] = 0;
    if ( empty($_POST['package']['month_rent']) ) $_POST['package']['month_rent'] = 0;

    delete_post_meta($post_id, 'package_description');

    foreach ( $_POST['package'] as $meta_key => $new_meta_value ) {

        if ( $meta_key == 'package_description' ) continue;

        /* Get the meta value of the custom field key. */
        $meta_value = get_post_meta( $post_id, $meta_key, true );

        /* If a new meta value was added and there was no previous value, add it. */
        if ( $new_meta_value && '' == $meta_value )
            add_post_meta( $post_id, $meta_key, $new_meta_value, true );

        /* If the new meta value does not match the old value, update it. */
        elseif ( $new_meta_value && $new_meta_value != $meta_value )
            update_post_meta( $post_id, $meta_key, $new_meta_value );

        /* If there is no new meta value but an old value exists, delete it. */
        elseif ( '' == $new_meta_value && $meta_value )
            delete_post_meta( $post_id, $meta_key, $meta_value );

    }

    foreach ( $_POST['package']['package_description'] as $just_key => $new_meta_value ) {

        if ( empty($new_meta_value) ) continue;

        $meta_key = 'package_description';
        add_post_meta( $post_id, $meta_key, $new_meta_value, false );
    }
}

function saveAmaCoupongs($post_id, $post)
{
    $packages = new WP_Query(array('post_type' => 'ama_packages','post_status' => 'publish'));
    $packages = $packages->posts;
    $meta = get_post_meta($post_id);

    foreach($packages as $package) {
        update_post_meta( $post_id, 'package-'.$package->ID, 0 );
    }
    update_post_meta( $post_id, 'monthly', 0 );
    update_post_meta( $post_id, 'yearly', 0 );
    update_post_meta( $post_id, 'default', 0 );

    foreach ( $_POST['coupongs'] as $meta_key => $new_meta_value ) {

        /* Get the meta value of the custom field key. */
        $meta_value = get_post_meta( $post_id, $meta_key, true );

        /* If a new meta value was added and there was no previous value, add it. */
        if ( $new_meta_value && '' == $meta_value )
            add_post_meta( $post_id, $meta_key, $new_meta_value, true );

        /* If the new meta value does not match the old value, update it. */
        elseif ( $new_meta_value && $new_meta_value != $meta_value )
            update_post_meta( $post_id, $meta_key, $new_meta_value );

        /* If there is no new meta value but an old value exists, delete it. */
        elseif ( '' == $new_meta_value && $meta_value )
            delete_post_meta( $post_id, $meta_key, $meta_value );
    }

}

function saveAmaOrders($post_id, $post)
{
    if (!isset($_POST['order']['payed']) || empty($_POST['order']['payed']) ) $_POST['order']['payed'] = 0;

    foreach ( $_POST['order'] as $meta_key => $new_meta_value ) {

        /* Get the meta value of the custom field key. */
        $meta_value = get_post_meta( $post_id, $meta_key, true );

        /* If a new meta value was added and there was no previous value, add it. */
        if ( $new_meta_value && '' == $meta_value )
            add_post_meta( $post_id, $meta_key, $new_meta_value, true );

        /* If the new meta value does not match the old value, update it. */
        elseif ( $new_meta_value && $new_meta_value != $meta_value )
            update_post_meta( $post_id, $meta_key, $new_meta_value );

        /* If there is no new meta value but an old value exists, delete it. */
        elseif ( '' == $new_meta_value && $meta_value )
            delete_post_meta( $post_id, $meta_key, $meta_value );

    }
}




function createFileArray($files, $key) {
    return array(
        'name' => $files['name'][$key],
        'type' => $files['type'][$key],
        'tmp_name' => $files['tmp_name'][$key],
        'error' => $files['error'][$key],
        'size' => $files['size'][$key],
    );
}


function ama_package_custom_post_type_fields()
{
    add_meta_box( 'ama_packages_custom_fields3',
        'Package description',
        'ama_packages_fields_callback3',
        'ama_packages', 'normal', 'default'
    );

    add_meta_box( 'ama_packages_custom_fields',
        'Extra text',
        'ama_packages_fields_callback2',
        'ama_packages', 'normal', 'default'
    );

    add_meta_box( 'ama_packages_custom_fields2',
        'Price Info',
        'ama_packages_fields_callback',
        'ama_packages', 'normal', 'default'
    );
}

function ama_coupongs_custom_post_type_fields()
{
    add_meta_box( 'ama_coupongs_custom_fields',
        'Description',
        'ama_coupongs_fields_callback',
        'ama_coupongs', 'normal', 'default'
    );
}

function ama_packages_custom_post_type()
{
    register_post_type( 'ama_packages',
        array(
            'labels' => array(
                'name' => 'Packages',
                'singular_name' => 'Package',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Package',
                'edit' => 'Edit',
                'edit_item' => 'Edit Package',
                'new_item' => 'New Package',
                'view' => 'View',
                'view_item' => 'View Package',
                'search_items' => 'Search Package',
                'not_found' => 'No Package found',
                'not_found_in_trash' => 'No Packages found in Trash',
                'parent' => 'Parent Package'
            ),

            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title' ),
            'show_in_nav_menus' => false,
            'taxonomies' => array( '' ),
            #'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),

            'has_archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => false
        )
    );
}

function ama_coupongs_custom_post_type()
{
    register_post_type( 'ama_coupongs',
        array(
            'labels' => array(
                'name' => 'Coupongs',
                'singular_name' => 'Coupongs',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Coupong',
                'edit' => 'Edit',
                'edit_item' => 'Edit Coupong',
                'new_item' => 'New Coupong',
                'view' => 'View',
                'view_item' => 'View Coupong',
                'search_items' => 'Search Coupong',
                'not_found' => 'No Package found',
                'not_found_in_trash' => 'No Coupong found in Trash',
                'parent' => 'Parent Coupong'
            ),

            'public' => true,
            'supports' => array( 'title' ),
            'show_in_menu'  => 'edit.php?post_type=ama_packages',
            'show_in_nav_menus' => false,
            'taxonomies' => array( '' ),

            'has_archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => false
        )
    );
}

function ama_packages_fields_callback3()
{
    global $post;
    wp_nonce_field( basename( __FILE__ ), 'ama_product_mark_nonce' );

    $meta  = get_post_meta($post->ID,'package_description');
    ?>
    <table style="width: 100%;">
        <?php foreach ( $meta as $val ) : ?>
            <tr>
                <td><input type="text" name="package[package_description][]" style="width: 100%;" value="<?php echo $val; ?>"/></td>
            </tr>
        <?php endforeach;?>
        <tr class="first_line">
            <td><input type="text" name="package[package_description][]" style="width: 100%;"/></td>
        </tr>
        <tr id="add_new_line">
            <td><button type="button" class="button"><?php _e("Add new line", "ama"); ?></button></td>
        </tr>
    </table>
    <?php
}

function ama_packages_fields_callback2()
{
    global $post;

    $our_mail    = get_post_meta($post->ID, 'our_mail', true);
    $client_mail = get_post_meta($post->ID, 'client_mail', true);

    ?>
    <table style="width: 100%;">
        <tr>
            <td><label><?php _e( "Our mail", 'ama' ); ?></label></td>
            <td><?php wp_editor( $our_mail, 'our_mail', array('textarea_name' => 'package[our_mail]', 'textarea_rows' => 10) ); ?></td>
        </tr>
        <tr>
            <td><label><?php _e( "Client mail", 'ama' ); ?></label></td>
            <td><?php wp_editor( $client_mail, 'client_mail', array('textarea_name' => 'package[client_mail]', 'textarea_rows' => 10) ); ?></td>
        </tr>
    </table>
    <?php
}

function ama_packages_fields_callback()
{
    global $post;

    $meta = get_post_meta($post->ID);
    ?>
    <table style="width: 100%;">
        <tr>
            <td><label><?php _e("Month price", 'ama'); ?></label></td>
            <td><input type="text"  name="package[month_price]" value="<?php echo $meta['month_price'][0]; ?>"/> <i><?php _e("add without tax", "ama");  ?></i> <!--/ <?php _e("Month rent", 'ama'); ?> <input type="checkbox"  name="package[month_rent]" value="1" <?php if ( $meta['month_rent'][0]==1 ) : ?>checked="checked"<?php endif; ?>/>--></td>
        </tr>
        <tr>
            <td><label><?php _e("Article code month", 'ama'); ?></label></td>
            <td><input type="text"  name="package[article_code_month]" value="<?php echo $meta['article_code_month'][0]; ?>"/> <i><?php _e("article code month", "ama");  ?></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Month price text", 'ama'); ?></label></td>
            <td><textarea type="text"  name="package[month_price_text]" style="width: 60%;"><?php echo $meta['month_price_text'][0]; ?></textarea></td>
        </tr>
        <tr>
            <td><label><?php _e( "Year price", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[year_price]" value="<?php echo $meta['year_price'][0]; ?>"/> <i><?php _e("add without tax", "ama");  ?></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Article code year", 'ama'); ?></label></td>
            <td><input type="text"  name="package[article_code_year]" value="<?php echo $meta['article_code_year'][0]; ?>"/> <i><?php _e("article code year", "ama");  ?></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Year price text", 'ama'); ?></label></td>
            <td><textarea type="text"  name="package[year_price_text]" style="width: 60%;"><?php echo $meta['year_price_text'][0]; ?></textarea></td>
        </tr>
        <tr>
            <td><label><?php _e( "Month extra user price", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[month_extra_user_price]" value="<?php echo $meta['month_extra_user_price'][0]; ?>"/> <i><?php _e("add without tax", "ama");  ?></i></td>
        </tr>
        <tr>
            <td><label><?php _e( "Year extra user price", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[year_extra_user_price]" value="<?php echo $meta['year_extra_user_price'][0]; ?>"/> <i><?php _e("add without tax", "ama");  ?></i></td></td>
        </tr>
        <!--
			<tr>
			<td><label><?php _e( "Merit salary", 'ama' ); ?></label></td>
			<td><input type="checkbox"  name="package[is_salary]" value="1"  <?php if ( $meta['is_salary'][0]==1 ) : ?>checked="checked"<?php endif; ?>/></td></td>
			</tr>
			-->
        <tr>
            <td><label><?php _e( "Free package", 'ama' ); ?></label></td>
            <td><input type="checkbox"  name="package[free_package]" value="1"  <?php if ( $meta['free_package'][0]==1 ) : ?>checked="checked"<?php endif; ?>/></td></td>
        </tr>
        <tr>
            <td><label><?php _e( "File url", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[file_url]" value="<?php echo $meta['file_url'][0]; ?>" style="width: 300px;"/> <i><?php _e("In case of free package, file will be downloaded from this url.", "ama");  ?></i></td>
        </tr>
        <?php if (false){ ?>
            <tr>
                <td><label><?php _e( "Type", 'ama' ); ?></label></td>
                <td>
                    <?php /* ?><input type="text"  name="package[color_class]" value="<?php echo $meta['color_class'][0]; ?>"/><?php */ ?>
                    <select name="package[color_class]">
                        <option value="">-- vali --</option>
                        <option value="free">free</option>
                        <option value="standard">standard</option>
                        <option value="pro">pro</option>
                    </select>
                </td>
            </tr>
        <?php } ?>

        <tr>
            <td><label><?php _e( "Package url", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[package_info_url]" value="<?php echo $meta['package_info_url'][0]; ?>" style="width: 300px;"/> <i><?php _e("Package info url", "ama");  ?></i></td>
        </tr>

        <tr>
            <td><label><?php _e( "Package title size", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[package_title_size]" value="<?php echo $meta['package_title_size'][0]; ?>" style="width: 300px;"/> <i><?php _e("Package title size as pixels", "ama");  ?></i></td>
        </tr>
        <tr>
            <td><label><?php _e( "Package extra user info (year)", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[package_extra_user_info_year]" value="<?php echo $meta['package_extra_user_info_year'][0]; ?>" style="width: 300px;"/></td>
        </tr>
        <tr>
            <td><label><?php _e( "Package extra user info (month)", 'ama' ); ?></label></td>
            <td><input type="text"  name="package[package_extra_user_info_month]" value="<?php echo $meta['package_extra_user_info_month'][0]; ?>" style="width: 300px;"/></td>
        </tr>

    </table>

    <?php
}

function ama_coupongs_fields_callback()
{
    $packages = new WP_Query(array('post_type' => 'ama_packages','post_status' => 'publish'));

    #echo'<pre>';print_r($packages->posts);die;

    wp_enqueue_script('jquery-ui-datepicker');
    wp_register_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
    wp_enqueue_style('jquery-ui');
    wp_nonce_field( basename( __FILE__ ), 'ama_product_mark_nonce' );
    global $post;

    $meta = get_post_meta($post->ID);
    #echo'<pre>';print_r($meta);die;
    ?>
    <table style="width: 100%;">
        <tr>
            <td><label><?php _e("Code", 'ama'); ?></label></td>
            <td><input type="text"  name="coupongs[code]" value="<?php echo $meta['code'][0]; ?>"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Name", 'ama'); ?></label></td>
            <td><input type="text"  name="coupongs[name]" value="<?php echo $meta['name'][0]; ?>"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Start", 'ama'); ?></label></td>
            <td><input class="datepicker" type="date" name="coupongs[start]" value="<?php echo $meta['start'][0]; ?>"/></td>
        </tr>
        <tr>
            <td><label><?php _e("End", 'ama'); ?></label></td>
            <td><input class="datepicker" type="date" name="coupongs[end]" value="<?php echo $meta['end'][0]; ?>"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Monthly", 'ama'); ?></label></td>
            <td><input type="checkbox" name="coupongs[monthly]" <?php if($meta["monthly"][0]):?>checked<?php endif;?> value="1"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Yearly", 'ama'); ?></label></td>
            <td><input type="checkbox" name="coupongs[yearly]" <?php if($meta["yearly"][0]):?>checked<?php endif;?> value="1"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Apply by default", 'ama'); ?></label></td>
            <td><input type="checkbox" name="coupongs[default]" <?php if($meta["default"][0]):?>checked<?php endif;?> value="1"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Discount", 'ama'); ?></label></td>
            <td>
                <input type="number"  name="coupongs[discount]" value="<?php if($meta['discount'][0]):?><?php echo $meta['discount'][0]; ?><?php else:?>0<?php endif;?>"/>
                <label>Protsentides</label>
            </td>
        </tr>
        <tr>
            <td><label><?php _e("Freemonths", 'ama'); ?></label></td>
            <td>
                <input type="number"  name="coupongs[freemonths]" value="<?php if($meta['freemonths'][0]):?><?php echo $meta['freemonths'][0]; ?><?php else:?>0<?php endif;?>"/>
                <label>Kuudes</label>
            </td>
        </tr>
        <?php foreach($packages->posts as $package):?>
            <tr>
                <td><?=$package->post_title?></td>
                <td>
                    <input type="checkbox" <?php if($meta["package-".$package->ID][0]):?>checked<?php endif;?> name="coupongs[package-<?=$package->ID?>]" value="1">
                </td>
            </tr>
        <?php endforeach;?>
    </table>
    <script>
        jQuery(document).ready(function(){
            jQuery('.datepicker').datepicker({ dateFormat: 'dd-mm-yy' });
        });
    </script>
    <?php
}


function ama_orders_custom_post_type()
{
    register_post_type( 'ama_orders',
        array(
            'labels' => array(
                'name' => 'Orders',
                'singular_name' => 'Order',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Order',
                'edit' => 'Edit',
                'edit_item' => 'Edit Order',
                'new_item' => 'New Order',
                'view' => 'View',
                'view_item' => 'View Order',
                'search_items' => 'Search Order',
                'not_found' => 'No Order found',
                'not_found_in_trash' => 'No Orders found in Trash',
                'parent' => 'Parent Order'
            ),

            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title' ),
            'taxonomies' => array( '' ),
            #'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
            'has_archive' => true
        )
    );
}

function ama_orders_custom_post_type_fields()
{
    add_meta_box( 'ama_orders_custom_fields',
        'Order details',
        'ama_orders_fields_callback',
        'ama_orders', 'normal', 'default'
    );

    add_meta_box( 'ama_orders_custom_fields2',
        'Order customer',
        'ama_orders_fields_callback2',
        'ama_orders', 'normal', 'default'
    );
}

function ama_orders_fields_callback()
{
    global $post;

    wp_nonce_field( basename( __FILE__ ), 'ama_order_mark_nonce' );

    $meta  = get_post_meta($post->ID);
    $coupong = get_post_meta($meta['coupong'][0]);
    ?>
    <table style="width: 100%;">
        <tr>
            <td><label><?php _e("Total", 'ama'); ?></label></td>
            <td><input type="text"  name="package[total]" value="<?php echo $meta['total'][0]; ?>"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Extra users", 'ama'); ?></label></td>
            <td><input type="text"  name="package[extraUsers]" value="<?php echo $meta['extraUsers'][0]; ?>"/></td>
        </tr>
        <tr>
            <td><label><?php _e("Payment", 'ama'); ?></label></td>
            <td><?php echo $meta['payment'][0]; ?></td>
        </tr>
        <tr>
            <td><label><?php _e("Period", 'ama'); ?></label></td>
            <td><?php echo $meta['orderPeriod'][0]; ?></td>
        </tr>
        <tr>
            <td><label><?php _e("Order discount period", 'ama'); ?></label></td>
            <td><?php echo $meta['orderDiscountPeriod'][0]; ?></td>
        </tr>
        <tr>
            <td><label><?php _e("Type", 'ama'); ?></label></td>
            <td><?php if ($meta['type'][0] == 1) : ?><?php _e("Annually", "ama"); ?><? else : ?><?php _e("Monthly", "ama"); ?><?php endif; ?></td>
        </tr>
        <tr>
            <td><label><?php _e("Package", 'ama'); ?></label></td>
            <td><a href="post.php?post=<?php echo $meta['package'][0]; ?>&action=edit" target="_blank"><?php echo $meta['packageName'][0]; ?></a></td>
        </tr>
        <tr>
            <td><label><?php _e("Coupong", 'ama'); ?></label></td>
            <td><?=$coupong['name'][0]?> (<?=$coupong['code'][0]?>)</td>
        </tr>
        <tr>
            <td><label><?php _e( "Is payed?", 'ama' ); ?></label></td><td><input type="checkbox" name="order[payed]" value="1" <?php if ( $meta['payed'][0]==1 ):?>checked="checked"<?php endif; ?>/><?=$meta['payed'][0]?></td>
        </tr>

        <tr>
            <td><label><?php _e( "Json to merit with send payment", 'ama' ); ?></label></td><td><?php print_r($meta['sendToMeritPayment'][0]); ?></td>
        </tr>
        <tr>
            <td><label><?php _e( "Json response for sendPayment", 'ama' ); ?></label></td><td><?php print_r($meta['meritResponsePayment'][0]); ?></td>
        </tr>
        <tr>
            <td><label><?php _e( "Json to merit with sendContract", 'ama' ); ?></label></td><td><?php print_r($meta['sendToMeritContract'][0]); ?></td>
        </tr>
        <tr>
            <td><label><?php _e( "Json response for sendContract", 'ama' ); ?></label></td><td><?php print_r($meta['sendContractResponse'][0]); ?></td>
        </tr>
    </table>
    <?php
}

function ama_orders_fields_callback2()
{
    global $post;

    wp_nonce_field( basename( __FILE__ ), 'ama_order_mark_nonce' );

    $meta  = get_post_meta($post->ID);
    ?>
    <table style="width: 100%;">
        <tr>
            <td><label><?php _e("Date", 'ama');?></label></td>
            <td><?php echo $post->post_date;?></td>
        </tr>
        <tr>
            <td><label><?php _e("Name", 'ama'); ?></label></td>
            <td><input type="text"  name="package[name]" value="<?php echo $meta['name'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Y-tunnus", 'ama'); ?></label></td>
            <td><input type="text"  name="package[ytunnus]" value="<?php echo $meta['ytunnus'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Company", 'ama'); ?></label></td>
            <td><input type="text"  name="package[company]" value="<?php echo $meta['company'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>

        <tr>
            <td><label><?php _e("Street", 'ama'); ?></label></td>
            <td><input type="text"  name="package[street]" value="<?php echo $meta['street'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Index", 'ama'); ?></label></td>
            <td><input type="text"  name="package[index]" value="<?php echo $meta['index'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>

        <tr>
            <td><label><?php _e("City", 'ama'); ?></label></td>
            <td><input type="text"  name="package[city]" value="<?php echo $meta['city'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Email", 'ama'); ?></label></td>
            <td><input type="text"  name="package[email]" value="<?php echo $meta['email'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Phone", 'ama'); ?></label></td>
            <td><input type="text"  name="package[phone]" value="<?php echo $meta['phone'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
        <tr>
            <td><label><?php _e("Login email", 'ama'); ?></label></td>
            <td><input type="email"  name="package[loginEmail]" value="<?php echo $meta['loginEmail'][0]; ?>" style="width:300px;"/> <i></i></td>
        </tr>
    </table>
    <?php
}

function amachecked($option, $value) {
    if($option==$value) return 'checked="checked"';
    return '';
}

function formatArray($string) {
    $exp      = explode("\r\n", $string);
    $newarray = array();
    foreach ( $exp as $row ) {
        $e = explode(",", trim(stripslashes($row)));
        if ( empty($e[0]) ) continue;

        $newarray[] = array(
            'nimi' => trim($e[0]),
            'num'  => trim($e[1])
        );
    }
    return $newarray;
}
function ama_get_option($string) {
    $lng    =  ICL_LANGUAGE_CODE;
    $option = get_option($string . '_' . $lng);
    if ( empty($option) && $lng != 'et' ) {
        $option = get_option($string . '_et');
    }
    return stripslashes($option);
}



function saveForm() {
}

function set_contenttype($content_type){
    return 'text/html';
}


function mb_ucfirst($string, $encoding = "utf-8")
{
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}

//js scripts
function ama_payments_register_script() {
    wp_register_script('ama_tombstone_generator_script', _PLUGIN_URL_.'/js/ama_scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_script("ama_tombstone_generator_script");
    wp_enqueue_script( 'jquery' );
}


function calcPerc($price, $percent)
{
    $price2 = number_format($price*($percent/100), 2, '.', '');
    return number_format($price+$price2, 2, '.', '');
}

/**
 * Generate bank form for payment
 *
 * @param bank
 * @param product
 * @param orderId
 * @return string
 */
function generateBankForm($bank, $order, $orderId) {
    switch ($bank) {
        case 'suomi': return getSuomipankkiForm($order, $orderId); break;
        //case 'polish': return getPolishBankForm($order, $orderId); break;
        case 'invoice': makeContract($orderId); return sendOrderDetails($order, $orderId); break;
    }
}

/**
 * Generate suomipankki form
 * This is bank that needs a form to be generated
 * This includes allmoust all finnish banks
 *
 * @param product
 * @param orderId
 * @return string
 */
function getSuomipankkiForm($order, $orderId) {
    include_once _PLUGIN_PATH_ . "include/suomipankki.class.php";

    $seperator = '?';
    if ( stristr($order['redirectUrl'], '?') ) {
        $seperator = '&';
    }

    $suomipankki = new suomipankki;
    $suomipankki->MERCHANT_ID      = MERCHANT_ID;
    $suomipankki->MERCHANT_SECRET  = MERCHANT_SECRET;
    $suomipankki->AMOUNT           = str_replace(",", ".", $order['total']);
    $suomipankki->ORDER_NUMBER     = $orderId;
    $suomipankki->RETURN_ADDRESS   = $order['redirectUrl'].$seperator.'action=orderSuccess';
    $suomipankki->CANCEL_ADDRESS   = $order['redirectUrl'].$seperator.'action=orderCancel';
    $suomipankki->NOTIFY_ADDRESS   = $order['redirectUrl'].$seperator.'action=orderNotify';
    $suomipankki->REFERENCE_NUMBER = $suomipankki->generateReference(mktime());

    update_post_meta($orderId, 'reference', $suomipankki->REFERENCE_NUMBER);

    return $suomipankki->getForm();
}

/**
 * Generate polish bank form
 * This is bank that needs a form to be generated
 *
 * @param product
 * @param orderId
 * @return string
 */
function getPolishBankForm($order, $orderId) {
    include_once _PLUGIN_PATH_ . "include/polishBank.class.php";

    $seperator = '?';
    if ( stristr($order['redirectUrl'], '?') ) {
        $seperator = '&';
    }

    $polishBank = new polishBank(POLISH_IS_TEST);
    $polishBank->setP24SessionId( $reference = $polishBank->generateRandSessionId($orderId));
    $polishBank->setP24IdSprzedawcy(POLISH_MERCHANT_ID);
    $polishBank->setP24CrcKey(POLISH_CRC_KEY);
    $polishBank->setP24Kwota(str_replace(",", ".", $order['total']));
    $polishBank->setP24Email($order['email']);
    $polishBank->setP24ReutrnUrlOk($order['redirectUrl'].$seperator.'action=orderSuccess');
    $polishBank->setP24ReturnUrlError($order['redirectUrl'].$seperator.'action=orderCancel');
    $polishBank->setSubmitButtonText(__("Pay now", "ama"));

    update_post_meta($orderId, 'reference', $reference);

    return $polishBank->getForm();
}


/**
 * Go to polish bank payment view
 * This is bank that needs a form to be generated
 *
 * @param product
 * @param orderId
 * @return string
 */
function goToPolishPayment($order, $orderId)
{
    include_once _PLUGIN_PATH_ . "include/polishBank.class.php";

    $seperator = '?';
    if ( stristr($order['redirectUrl'], '?') ) {
        $seperator = '&';
    }

    $polishBank = new polishBank(POLISH_IS_TEST);
    $polishBank->setP24SessionId( $reference = $polishBank->generateRandSessionId($orderId));
    $polishBank->setP24MerchantId(POLISH_MERCHANT_ID);
    $polishBank->setP24CrcKey(POLISH_CRC_KEY);
    $polishBank->setP24Amount(str_replace(",", ".", $order['total']));
    $polishBank->setP24Client($order['name']);
    $polishBank->setP24Email($order['email']);
    $polishBank->setP24Address($order['street']);
    $polishBank->setP24Zip($order['index']);
    $polishBank->setP24City($order['city']);
    $polishBank->setP24Phone($order['phone']);
    $polishBank->setP24UrlReturn($order['redirectUrl'].$seperator.'action=orderSuccess');
    $polishBank->setP24UrlStatus($order['redirectUrl'].$seperator.'action=orderCancel');
    $polishBank->setP24Description(__("Order", 'ama') .' ' . $orderId);

    update_post_meta($orderId, 'reference', $reference);

    return $polishBank->goToPayment();
}

/**
 * Verify polish bank payment view
 *
 * @param product
 * @param orderId
 * @return string
 */
function verifyPolishPayment($order, $orderId)
{
    include_once _PLUGIN_PATH_ . "include/polishBank.class.php";

    $seperator = '?';
    if ( stristr($order['redirectUrl'], '?') ) {
        $seperator = '&';
    }

    $polishBank = new polishBank(POLISH_IS_TEST);
    $polishBank->setP24SessionId( $reference = $polishBank->generateRandSessionId($orderId));
    $polishBank->setP24MerchantId(POLISH_MERCHANT_ID);
    $polishBank->setP24CrcKey(POLISH_CRC_KEY);
    $polishBank->setP24Amount(str_replace(",", ".", $order['total']));
    $polishBank->setP24Client($order['name']);
    $polishBank->setP24Email($order['email']);
    $polishBank->setP24Address($order['street']);
    $polishBank->setP24Zip($order['index']);
    $polishBank->setP24City($order['city']);
    $polishBank->setP24Phone($order['phone']);
    $polishBank->setP24UrlReturn($order['redirectUrl'].$seperator.'action=orderSuccess');
    $polishBank->setP24UrlStatus($order['redirectUrl'].$seperator.'action=orderCancel');
    $polishBank->setP24Description(__("Order", 'ama') .' ' . $orderId);

    update_post_meta($orderId, 'reference', $reference);

    return $polishBank->goToPayment();
}


/**
 * Create and save new order
 *
 * @param $order
 * @return int
 */
function amaSaveOrder($order)
{
    global $wpdb;

    $order['reference'] = '';

    //insert new order
    $args = array(
        'post_title' => 'Order',
        'post_type'  => 'ama_orders',
        'post_date'  => date("Y-m-d H:i:s"),
    );
    if ( !isset($_SESSION['ama_order_id']) ) {

        if($order['follow_up']){
            // sest seda infot pole antud
            $order['email'] = '';
        }

        $wpdb->insert($wpdb->posts, $args);
        $order_id = $wpdb->insert_id;

        if($order['follow_up']){
            $wpdb->update($wpdb->posts, array('post_title' => 'Follow-up payment #'. $order_id), array('ID' => $order_id));
        } else {
            $wpdb->update($wpdb->posts, array('post_title' => 'Order #'. $order_id), array('ID' => $order_id));
        }

        $_SESSION['ama_order_id'] = $order_id;

        foreach ( $order as $meta_key => $meta_val ) {
            add_post_meta($order_id, $meta_key, $meta_val, true);
        }

    }
    else {
        $order_id = $_SESSION['ama_order_id'];

        foreach ( $order as $meta_key => $meta_val ) {
            update_post_meta($order_id, $meta_key, $meta_val);
        }
    }

    return $order_id;
}

function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}


function sendOrderDetails($order, $orderId)
{
    add_filter( 'wp_mail_content_type', 'set_contenttype' );

    global $sitepress;
    $lang_prefix = $sitepress->get_current_language();
    $options = get_option("ama_payments_options_" . $lang_prefix);
    $order_mail = $options['ama_order_email'];

    /*TEEME KASUTAJA KUI VEEL EI OLE*/
    $loginEmail = $order['loginEmail'];
    $contactName = $order['name'];
    registerUser($contactName, $loginEmail);
    /*/TEEME KASUTAJA KUI VEEL EI OLE*/

    include_once _PLUGIN_PATH_.'/templates/ama-payments-order-info.php';
    $client_data = amaPaymentsOrderInfo($order);
    $our_data = amaPaymentsOrderInfo($order, true);

    $packageId  = get_post_meta($orderId, "package", true);

    //our mail
    $ourMail  = get_post_meta($packageId, "our_mail", true);
    $html = str_replace("%tellija-andmed%", $our_data, nl2br($ourMail));

    if ( !empty($order_mail) ) {
        $headers[] = 'From: '.get_bloginfo('name').' <'.$order_mail.'>';
        $headers[] = 'Reply-To: '.$order['name'].' <'.$order['email'].'>';
        $mail = wp_mail($order_mail,__('License order:', 'ama') . " #" . $orderId, $html, $headers);
    }

    //client mail
    $clientMail = get_post_meta($packageId, "client_mail", true);
    $html = str_replace("%tellija-andmed%", $client_data, nl2br($clientMail));

    $headers[] = 'From: '.get_bloginfo('name').' <'.$order_mail.'>';
    wp_mail($order['email'],__('License order:', 'ama') . " #" . $orderId, $html, $headers);


}

function amaOrderSuccess()
{
    global $wpdb;

    //if we deal with soumipankki
    if ( empty($_REQUEST['p24_session_id'])) {
        $order_id = (int)$_REQUEST['ORDER_NUMBER'];
        if ( empty($order_id) ) {
            //wp_redirect('?action=orderNotify');
            echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
            exit;
        }
        if ( empty($_REQUEST['PAID']) ) {
            //wp_redirect('?action=orderNotify');
            echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
            exit;
        }
    }
    else {

        if(!empty($_REQUEST['p24_error_code'])) {
            //wp_redirect('?action=orderNotify');
            echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
            exit;
        }

        include_once _PLUGIN_PATH_ . "include/polishBank.class.php";
        $polishBank = new polishBank(POLISH_IS_TEST);
        $polishBank->setP24CrcKey(POLISH_CRC_KEY);
        $polishBank->setP24SessionId($_REQUEST['p24_session_id']);
        $polishBank->setP24MerchantId($_REQUEST['p24_merchant_id']);
        $polishBank->setP24PosId($_REQUEST['p24_pos_id']);
        $polishBank->setP24OrderId($_REQUEST['p24_order_id']);
        $polishBank->setP24Amount($_REQUEST['p24_amount']);
        $polishBank->setP24Currency($_REQUEST['p24_currency']);

        $order_id = (int)$polishBank->getIdFromSessionId();
        if ( empty($order_id) ) {
            //wp_redirect('?action=orderNotify');
            echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
            exit;
        }

        //check if the session id exists in this order
        $reference = get_post_meta($order_id, 'reference', true);
        if ( $reference != $polishBank->getP24SessionId() ) {
            //wp_redirect('?action=orderNotify');
            echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
            exit;
        }

        if(!$polishBank->verifyPayment()) {
            //wp_redirect('?action=orderNotify');
            echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
            exit;
        }

    }

    $meta = get_post_meta($order_id);

    if ( !is_array($meta) ) {
        //wp_redirect('?action=orderNotify');
        echo '<META http-equiv="refresh" content="0;URL=?action=orderNotify">';
        exit;
    }

    $order = fixOrderArray($meta);

    if ( $order['payed'] != 1 ) {
        sendOrderDetails($order, $order_id);
        update_post_meta($order_id, 'payed', 1);
    }

    //wp_redirect('?action=success&order=' . $order_id);
    echo '<META http-equiv="refresh" content="0;URL=?action=success&order=' . $order_id.'">';
    exit;
}

function fixOrderArray($meta) {
    $order = array();
    foreach ($meta as $key => $val ) {
        if ( is_array($val) ) {
            foreach ( $val as $k => $v ) {
                $order[$key] = $v;
            }
        }
        else {
            $order[$key] = $val;
        }
    }
    return $order;
}

function calcNewMonthPrice($productPrice)
{
    $curDate     = (int)date('d');
    $daysInMonth	= (int)cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

    if ( $curDate < 14 ) {
        $newPrice = number_format((($daysInMonth-$curDate)/$daysInMonth),2)*$productPrice;
    }
    else if ( $curDate >= 14 ) {
        $newPrice =  number_format((1+($daysInMonth-$curDate)/$daysInMonth),2)*$productPrice;
    }
    return $newPrice;
}

function calcAlv($price, $lang = 'FI')
{
    $tax = constant('TAX_'.strtoupper($lang));

    if ( !defined('TAX_'.strtoupper($lang)) )
        $tax = constant('TAX_FI');

    $orig_tax = $tax;
    $tax = (float)$tax;

    $price_ret = number_format(($price*$tax),2,'.','');
    $price_vat = $price_ret - $price;
    return array(
        'vat' => str_replace("1.", "", $orig_tax),
        'price_vat' => number_format($price_vat,2,'.',''),
        'price' =>  $price_ret,
    );
}

function loadJavascript()
{
    //load styles/scripts only where im using the form
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_style('styleuid', 'https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');

    wp_enqueue_style('colorboxcss', _PLUGIN_URL_ . '/css/colorbox.css');
    wp_enqueue_style('stylepackages', _PLUGIN_URL_ . '/css/packages.css');

    wp_register_script( 'jvalidate', _PLUGIN_URL_.'/js/jquery.validate.min.js', array('jquery'), '10.0', true );
    wp_enqueue_script( 'jvalidate' );

    wp_register_script('colorbox', _PLUGIN_URL_.'/js/jquery.colorbox-min.js', array('jquery'), '1.3.19', true);
    wp_enqueue_script("colorbox");

    wp_register_script('ama_payments_js', _PLUGIN_URL_.'/js/payments.js', array('jquery'), '1.0', true);
    wp_enqueue_script("ama_payments_js");

    wp_enqueue_style('modal_loader_widget_scope', _PLUGIN_URL_ . '/front_end/internal/widgets/ModalLoaderWidget/ModalLoaderWidgetScope.css');
    wp_enqueue_style('modal_loader_widget_themes', _PLUGIN_URL_ . '/front_end/internal/widgets/ModalLoaderWidget/ModalLoaderWidgetThemes.css');

    wp_register_script('modal_loader_widget', _PLUGIN_URL_.'/front_end/internal/widgets/ModalLoaderWidget/ModalLoaderWidget.js', array('jquery'), '1.0', true);
    wp_enqueue_script('modal_loader_widget');


    //wp_register_script('fin_payment','//payment.verkkomaksut.fi/js/payment-widget-v1.0.min.js', array('jquery'), '1.0', false);
    //wp_enqueue_script("fin_payment");
}

function registerUser($name, $email) {
    $isthere = file_get_contents('https://aktiva.meritaktiva.fi/Authent/SilentExists?email='.urlencode($email));

    if (strlen($name) > 0 && !filter_var($email, FILTER_VALIDATE_EMAIL) === false && $isthere == 'NO') {
        $additionalData = '';
        if(isset($_COOKIE['customCookie']) && $_COOKIE['customCookie']) {
            $value = $_COOKIE['customCookie'];
            $additionalData = '&source=' . $value;
        }
        $register = file_get_contents('https://aktiva.meritaktiva.fi/Authent/SilentRegister?name='.urlencode($name).'&email='.urlencode($email) . $additionalData);
    }
}
#makeContract(9670);
function makeContract($post_id, $iban = '') {
    $meta = get_post_meta($post_id);
    #print_r($meta);die();
    $period = $meta['type'][0];
    $productMeta = get_post_meta($meta['package'][0]);

    if($period == 2 && $productMeta['article_code_month'] || $period == 1 && $productMeta['article_code_year'] && (!isset($meta['sendToMeritContract'][0]) || empty($meta['sendToMeritContract'][0]))) {
        if($period == 2 && $productMeta['article_code_month']) {
            $articleCode = $productMeta['article_code_month'][0];
        } else {
            $articleCode = $productMeta['article_code_year'][0];
        }

        if($period == 2)
            $per = 'Kuu';
        else
            $per = 'Aasta';

        $discPct = str_replace(',', '.', $meta['total'][0]);
        $userCount = $meta['extraUsers'][0];
        $paymentMethod = $meta['payment'][0];

        if($paymentMethod == 'invoice') {
            $iban = 'ARVEGA';
        } else {
            $iban = 'PAYMENTSERVICE';
        }

        $name       = $meta['company'][0];
        #$regNr      = $meta['regNr'][0];
        $regNr      = $meta['ytunnus'][0];
        $address    = $meta['street'][0];
        $city       = $meta['city'][0];
        $postalCode = $meta['index'][0];
        $phoneNr    = $meta['phone'][0];
        $email      = $meta['email'][0];
        $userEmail  = $meta['loginEmail'][0];
        $packageTypeName  = $meta['packageName'][0];
        $contact   = $meta['contactName'][0];

        $date = date('Ymd',strtotime("-1 days"));;

        $params = array(
            'Period' => $per,
            'Item' => $articleCode,
            'DiscPct' => $discPct,
            'UserCount' => $userCount,
            'PaymentMethod' => $iban,
            'OrderDate' => $date,
            'Customer' => [
                'Name' => $name,
                'RegNo' => $regNr,
                'Address' => $address,
                'City' => $city,
                'PostalCode' => $postalCode,
                'PhoneNo' => $phoneNr,
                'Email' => $email,
                'UserEmail' => $userEmail,
                'Contact' => $contact,
            ],
        );

        $apiId = getApiId();
        $timestamp = getTimestamp();
        $requestJson = json_encode($params);
        $signature = signRequest($timestamp, $requestJson);

        $myFile = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/ama-payments/log.txt";
        $file = fopen($myFile,"a+") or die("can't open file");
        $oldContent = file_get_contents($file);
        $newContent = date("Y-m-d H:i:s") . ' sendContract: requestJson: ' . $requestJson;
        fwrite($file, $newContent."\n".$oldContent);

        add_post_meta($post_id, 'sendToMeritContract', $requestJson);

        $timestampString = '&timestamp';

        $url='https://aktivatest.meritaktiva.fi/api/v1/sendcontract?apiId='.$apiId.$timestampString.'='.$timestamp.'&signature='.$signature;

        $result = makeRequest($url, $requestJson);

        @$resultJson = json_encode($result);
        $result = json_decode($result);

        $myFile = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/ama-payments/log.txt";
        $file = fopen($myFile,"a+") or die("can't open file");
        $oldContent = file_get_contents($file);
        $newContent = date("Y-m-d H:i:s") . ' sendContract: sendContract: ' . $resultJson;
        fwrite($file, $newContent."\n".$oldContent);

        add_post_meta($post_id, 'sendContractResponse', $resultJson);

    }
}

function makeRequest($url, $requestJson) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestJson);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($requestJson)));

    $result = curl_exec($ch);

    if (FALSE === $result) {
        throw new Exception(curl_error($ch), curl_errno($ch));
    }

    curl_close($ch);

    $result = json_decode($result, true);

    return $result;
}

function signRequest($timestamp, $requestBody){
    $apiSecret = getApiKey();
    $apiId = getApiId();
    $string = $apiId+$timestamp+$requestBody;
    $signature = hash_hmac('sha256', $string, $apiSecret);
    return base64_encode($signature);
}

function getApiKey(){
    return 'pgwmKYcW4YpZKvvNUyO6h8vbua0kUMSiH3uapeeOjT0=';
}

function getApiId(){
    return 'b7d1a80a-a9cf-4389-8e44-a68e351090b9';
}

function getTimestamp(){
    return gmdate('YmdHis', time());
}

function getUserIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function paymentToAccounting($post_id) {
    $meta = get_post_meta($post_id);
    #mail('henri@finecode.ee', 'J2tkuarve: ' . $post_id, 'MAKSE PEALEMÄRKIMINE MERITISSE, AINULT JÄTKUARVE PUHUL || IP:' . $this->getUserIP());

    $meta = get_post_meta($post_id);
    $sess = $_SESSION['o'];
    $amount      = $sess['total'];
    $invoiceNr   = $sess['reference'];
    $reference   = $sess['reference'];
    $companyName = $sess['company'];
    $paymentMethod = $sess['payment'];
    $ibans = [
        'invoice' => 'ARVEGA',
        'polish' => 'PAYMENTSERVICE',
    ];
    $iban = $ibans[$paymentMethod];

    if(empty($iban)) {
        $iban = 'PAYMENTSERVICE';
    }

    $params = array(
        'IBAN' => $iban,
        'CustomerName' => $companyName,
        'InvoiceNo'    => $invoiceNr,
        'RefNo'        => $reference,
        'Amount'       => $amount,
    );

    $apiId = getApiId();
    $timestamp = getTimestamp();
    $requestJson = json_encode($params);
    $signature = signRequest($timestamp, $requestJson);

    $myFile = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/ama-payments/log.txt";
    $file = fopen($myFile,"a+") or die("can't open file");
    $oldContent = file_get_contents($file);
    $newContent = date("Y-m-d H:i:s") . ' sendPayment: sendParams: ' . $requestJson;
    fwrite($file, $newContent."\n".$oldContent);

    add_post_meta($post_id, 'sendToMeritPayment', $requestJson);

    $url='https://aktivatest.meritaktiva.fi/api/v1/sendpayment?apiId='.$apiId.'&timestamp='.$timestamp.'&signature='.$signature;

    $result = makeRequest($url, $requestJson);

    @$resultJson = json_encode($result);
    $result = json_decode($result);

    $myFile = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/ama-payments/log.txt";
    $file = fopen($myFile,"a+") or die("can't open file");
    $oldContent = file_get_contents($file);
    $newContent = date("Y-m-d H:i:s") . ' sendPayment: result: ' . $resultJson;
    fwrite($file, $newContent."\n".$oldContent);

    add_post_meta($post_id, 'meritResponsePayment', $resultJson);

}
?>
