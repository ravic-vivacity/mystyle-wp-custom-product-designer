<?php
/**
 * Miscellaneous functions used by the plugin.
 * @package MyStyle
 * @since 1.5.0
 */
    
/**
 * Returns our interface with WooCommerce.
 *
 * @return MyStyle_WC_Interface
 */ 
function MyStyle_WC() {
    return MyStyle::get_instance()->get_WC();
}
