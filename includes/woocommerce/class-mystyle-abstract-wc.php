<?php

/**
 * MyStyle_AbstractWC class. 
 * 
 * Abstract class for facilitating interactions with WooCommerce.
 * 
 * The abstract class, interface, etc is used for:
 *  * Unit testing.
 *  * Supporting multiple versions of WooCommerce.
 *
 * @package MyStyle
 * @since 1.5.0
 */
abstract class MyStyle_AbstractWC {
    
    /**
     * Singleton class instance
     */
    private static $instance;
    
    /**
     * Checks to see if WooCommerce is installed and activated.
     * @return boolean Returns true if WooCommerce is installed and activated,
     * otherwise, returns false.
     */
    public function is_installed() {
        $ret = false;
        
        if ( class_exists( 'WooCommerce' ) ) {
            $ret = true;
        }
        
        return $ret;
    }
    
    /**
     * Returns the version of WooCommerce that is installed. Returns null if
     * WooCommerce isn't installed.
     * @return string|null
     */
    public function get_version() {
        $version = null;
        
        if ( defined( 'WC_VERSION' ) ) {
            $version = WC_VERSION;
        }
        
        return $version;
    }
    
    /**
     * Compares the past version with the currently installed version of
     * WooCommerce using the passed operator.
     * @param string $version The version to compare with (ex: "3.0").
     * @param string $operator A comparison operator (ex: ">").
     * @return boolean Returns the result of the comparison.
     */
    public function version_compare( $version, $operator ) {
        $current_version = $this->get_version();
        
        return version_compare( $current_version, $version, $operator );
    }
    
    /**
     * Wrapper for the global wc_get_page_id function.
     * @param string $page
     * @return int
     */
    public function wc_get_page_id( $page ) {
        return wc_get_page_id( $page );
    }
    
    /**
     * Wraps the now depcrecated get_matching_variation method of the 
     * WC_Product_Variable to allow us to call it independent of WC version.
     * @param integer $product_id The product id of the product whose variation
     * you are looking for.
     * @param array $variation The variation that you are looking for.
     * @return integer Returns the variation id of the matching product
     * variation.
     * @todo Add unit testing
     */
    public function get_matching_variation( 
                        $product_id, 
                        $variation ) 
    {
        if( MyStyle()->get_WC()->version_compare( '3.0', '<' ) ) {
            $variable_product = new \WC_Product_Variable( $product_id );
            return $variable_product->get_matching_variation( $variation );
        } else {
            $product = new WC_Product( $product_id );
            $data_store = \WC_Data_Store::load( 'product' );
            return $data_store->find_matching_product_variation( 
                                    $product,
                                    $variation 
                                );
        }
    }
    
    /**
     * Gets the mystyle data from a WooCommerce Order Item array. This function
     * is designed to work with both WC 2.x and WC 3.x
     * @param array $item The item that we are working with.
     * @return array|null Returns and associative array of mystyle_data or null
     * if no mystyle data is found in the order item meta.
     */
    public function get_mystyle_data_from_order_item( $item ) {
        $mystyle_data = null;
        
        $item_meta = new WC_Order_Item_Meta( $item );
       
        if( array_key_exists( 'mystyle_data', $item_meta->meta ) ) {
            if( isset( $item_meta->meta['mystyle_data'][0] )) { // WC < 3.0
                $mystyle_data = unserialize( $item_meta->meta['mystyle_data'][0] );
            } else { // WC >= 3.0
                $mystyle_data = $item_meta->meta['mystyle_data'];
            }   
        }
       
       return $mystyle_data;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}
