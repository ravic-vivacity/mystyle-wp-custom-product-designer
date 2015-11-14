<?php

/**
 * MyStyle Main class. 
 * 
 * The MyStyle class sets up and controls the MyStyle plugin for features used
 * by both the frontend and the admin.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle {
    
    public static $STANDARD_DATE_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'woocommerce_add_order_item_meta', array( &$this, 'add_mystyle_order_item_meta' ), 10, 2 );
        add_action( 'woocommerce_order_status_completed', array( &$this, 'on_order_completed' ), 10, 1 );
        add_action( 'wp_login', array( &$this, 'on_wp_login' ), 10, 2 );
        add_action( 'user_register', array( &$this, 'on_user_register' ), 10, 1 );
        add_action( 'woocommerce_created_customer', array( &$this, 'on_woocommerce_created_customer' ), 10, 3 );
        
        add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_cart_item_from_session' ), 10, 3 );
    }
    
    /**
     * Init the MyStyle interface.
     */
    public function init() {
        add_filter( 'woocommerce_cart_item_thumbnail', array( &$this, 'modify_cart_item_thumbnail' ), 10, 3 );
        add_filter( 'woocommerce_in_cart_product_thumbnail', array( &$this, 'modify_cart_item_thumbnail' ), 10, 3 );
    }
    
    /**
     * Add the item meta from the cart to the order.
     * @param number $item_id The item_id of the item being added.
     * @param array $values The values from the cart.
     * @return Returns false on failure. On success, returns the ID of the inserted row.
     */
    public static function add_mystyle_order_item_meta( $item_id, $values ) {
        if( isset( $values['mystyle_data'] ) ) {
            return wc_add_order_item_meta( $item_id, 'mystyle_data', $values['mystyle_data'] );
        }
    }
    
    /**
     * After the order is completed, do the following if for all mystyle enabled
     * products:
     *  * Increment the design purchase count.
     * @param number $order_id The order_id of the new order.
     */
    function on_order_completed( $order_id ) {

        // order object (optional but handy)
        $order = new WC_Order( $order_id );

        if ( count( $order->get_items() ) > 0 ) {
            foreach( $order->get_items() as $item ) {
                
                if( isset( $item['mystyle_data'] ) ) {  
                    $mystyle_data = wc_get_order_item_meta( $item->order_item_id , 'mystyle_data' );
                    
                    //Increment the design purchase count
                    $design = MyStyle_DesignManager::get( $design_id );
                    $design->increment_purchase_count();
                    MyStyle_DesignManager::persist( $design );
                }
            }
        }
    }
    
    /**
     * Filter the woocommerce_get_cart_item_from_session and add our session 
     * data.
     * @param array $session_data The current session_data.
     * @param array $values The values that are to be stored in the session.
     * @param string $key The key of the cart item.
     * @return string Returns the updated cart image tag.
     */
    public static function get_cart_item_from_session( $session_data, $values, $key ) {
        
        // Fix for WC 2.2 (if our data is missing from the cart item, get it from the session variable 
        if( ! isset( $session_data['mystyle_data'] ) ) {
            $cart_item_data = WC()->session->get( 'mystyle_' . $key );
            $session_data['mystyle_data'] = $cart_item_data['mystyle_data'];
        }
	
        return $session_data;
    }
    
    /**
     * Override the product thumbnail image.
     * @param string $get_image The current image tag (ex. <img.../>).
     * @param string $cart_item The cart item that we are currently on.
     * @param string $cart_item_key The current cart_item_key.
     * @return string Returns the updated cart image tag.
     */
    public static function modify_cart_item_thumbnail( $get_image, $cart_item, $cart_item_key ) {
        
        $new_image_tag = $get_image;
        $design_id = null;
        
        //Try to get the design id, first from the cart_item and then from the session
        if( isset( $cart_item['mystyle_data'] ) ) {
            $design_id = $cart_item['mystyle_data']['design_id'];
        } else {
            $session_data = self::get_cart_item_from_session( array(), null, $cart_item_key );
            if( isset( $session_data['mystyle_data']) ) {
                $design_id = $session_data['mystyle_data']['design_id'];
            }
        }
            
        if( $design_id != null ) {
            
            $design = MyStyle_DesignManager::get( $design_id );

            $new_src = 'src="' . $design->get_thumb_url() . '"';
	
            $new_image_tag = preg_replace( '/src\=".*?"/', $new_src, $get_image );
        }
	
        return $new_image_tag;
    }
    
    /**
     * Called when a user logs in.
     * @param string $user_login
     * @param WP_User $user
     * @todo Add unit testing
     */
    public static function on_wp_login( $user_login, $user ) {
        $session = MyStyle_SessionHandler::get();
        MyStyle_DesignManager::set_wp_user_id_by_mystyle_session( $session, $user );
    }
    
    /**
     * Called when a user registers.
     * @param integer $user_id
     * @todo Add unit testing
     */
    public static function on_user_register( $user_id ) {
        $session = MyStyle_SessionHandler::get();
        $user = get_user_by( 'id', $user_id );
        MyStyle_DesignManager::set_wp_user_id_by_mystyle_session( $session, $user );
    }
    
    /**
     * Called when woocommerce creates a user (for instance when the user checks
     * out).
     * @param integer $user_id
     * @todo Add unit testing
     */
    public static function on_woocommerce_created_customer( $customer_id, $new_customer_data, $password_generated ) {
        $session = MyStyle_SessionHandler::get();
        $user = get_user_by( 'id', $customer_id );
        MyStyle_DesignManager::set_wp_user_id_by_mystyle_session( $session, $user );
    }
    
    /**
     * Function that looks to see if any products are mystyle enabled.
     * @return boolean Returns true if at least one product is customizable.
     * @todo: write unit tests
     */
    public static function site_has_customizable_products() {
        $args = array(
                    'post_type'      => 'product',
                    'numberposts' => 1,
                    'meta_key'       => '_mystyle_enabled',
                    'meta_value'     => 'yes',
                );

        $customizable_products = get_posts( $args );
        
        if( ! empty( $customizable_products ) ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Function that looks to see if passed products is mystyle enabled.
     * @param integer $product_id The id of the product to check.
     * @return boolean Returns true if the product is customizable, otherwise,
     * returns false.
     */
    public static function product_is_customizable( $product_id ) {
        $mystyle_enabled = get_post_meta( $product_id, '_mystyle_enabled', true );
        
        if( $mystyle_enabled == 'yes' ) {
            return true;
        } else {
            return false;
        }
    }
    
}
