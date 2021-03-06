<?php
/**
 * The template for displaying the MyStyle Design Profile page content.
 * 
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 * 
 * @package MyStyle
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div id="mystyle-design-profile-wrapper" class="woocommerce">
    <ul class="mystyle-button-group mystyle-design-nav">
        <?php if( ! empty( $previous_design_url ) ) { ?>
                <li><a href="<?php echo $previous_design_url; ?>">&larr;</a></li>
        <?php } else { ?>
                <li>&nbsp;</li>
        <?php } ?>
                <li><a href="<?php echo get_permalink( MyStyle_Design_Profile_Page::get_id() ); ?>">&uarr;</a></li>
        <?php if( ! empty( $next_design_url ) ) { ?>
                <li><a href="<?php echo $next_design_url; ?>">&rarr;</a></li>
        <?php } else { ?>
                <li>&nbsp;</li>
        <?php } ?>
    </ul>
    <img id="mystyle-design-profile-img" src="<?php echo $design->get_web_url(); ?>"/>
    <ul class="mystyle-button-group">
        <li><a onclick="location.href = '<?php echo $design->get_reload_url(); ?>';" class="button">Customize</a></li>
        <li>
            <form enctype="multipart/form-data" method="post" action="<?php echo get_permalink( $design->get_product_id() ); ?>">
                <?php 
                    //if we have the cart_data (older versions of the plugin don't) through it all into hidden fields
                    if( $design->get_cart_data_array() != null ) { 
                        foreach( $design->get_cart_data_array() AS $key => $value ) {
                            echo '<input type="hidden" name="' . $key . '" value="' . sanitize_title( $value ) . '" />';
                        }
                    } else {
                        //if we don't have the cart data just use the product_id
                        echo '<input type="hidden" name="add-to-cart" value="' . $design->get_product_id() . '" />';
                    }
                ?>
                <input type="hidden" name="design_id" value="<?php echo $design->get_design_id(); ?>" />
                <button type="submit" class="button">Add to Cart</a>
            </form>
        </li>
    </ul>
</div>

