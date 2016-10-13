<?php

/**
 * MyStyle Cart class.
 * The MyStyle Cart class has hooks for working with the shopping cart.
 *
 * @package MyStyle
 * @since 1.4.10
 */
class MyStyle_Cart {
    
    /**
     * Singleton class instance
     * @var MyStyle_Cart
     */
    private static $instance;
    
    /**
     * Constructor, constructs the class and sets up the hooks.
     */
    public function __construct() {
        add_filter( 'woocommerce_product_single_add_to_cart_text', array( &$this, 'filter_cart_button_text' ), 10, 1 ); 
        add_filter( 'woocommerce_add_to_cart_handler', array( &$this, 'filter_add_to_cart_handler' ), 10, 2 );
        add_filter( 'woocommerce_cart_item_product', array( &$this, 'filter_cart_item_product' ), 10, 3 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_cart_item_from_session' ), 10, 3 );
        
        add_action( 'woocommerce_loop_add_to_cart_link', array( &$this, 'loop_add_to_cart_link' ), 10, 2 );
        add_action( 'woocommerce_add_to_cart_handler_mystyle_customizer', array( &$this, 'mystyle_add_to_cart_handler_customize' ), 10, 1 );
        add_action( 'woocommerce_add_to_cart_handler_mystyle_add_to_cart', array( &$this, 'mystyle_add_to_cart_handler' ), 10, 1 );
    }
    
    /**
     * Filter the "Add to Cart" button text.
     * @param string $text The current cart button text.
     */
    public function filter_cart_button_text( $text ) {
        global $product;
        
        if( $product != null ) {
            
            if( MyStyle::product_is_customizable( $product->id ) ) {
                $text = "Customize";
            }
        }
        
        return $text;
    }
    
    /**
     * Filter to add our add_to_cart handler for customizable products.
     * @param string $handler The current add_to_cart handler.
     * @param type $product The current product.
     * @return string Returns the name of the handler to use for the add_to_cart
     * action.
     */
    public function filter_add_to_cart_handler( $handler, $product ) {

        if($product != null) {
            $product_id = $product->id;
        } else {
            $product_id = absint( $_REQUEST['add-to-cart'] );
        }
        
        if( isset( $_REQUEST['design_id'] ) ) {
            $handler = 'mystyle_add_to_cart';
            if(WC_VERSION < 2.3) {
                //old versions of woo commerce don't support custom add_to_cart handlers so just go there now.
                self::mystyle_add_to_cart_handler( false );
            }
        } else {
        
            if( MyStyle::product_is_customizable( $product_id ) ) {
                $handler = 'mystyle_customizer';
                if(WC_VERSION < 2.3) {
                    //old versions of woo commerce don't support custom add_to_cart handlers so just go there now.
                    self::mystyle_add_to_cart_handler_customize( false );
                }
            }
        }
    
        return $handler;
    }
    
    /**
     * Modify the add to cart link for product listings
     * @param type $link The "Add to Cart" link (html)
     * @param type $product The current product.
     * @return type Returns the html to be outputted.
     */
    public function loop_add_to_cart_link( $link, $product ) {
        //var_dump($product);
        
        if( ( MyStyle::product_is_customizable( $product->id ) ) && ( $product->product_type != 'variable') ) {
            $customize_page_id = MyStyle_Customize_Page::get_id();
            
            //build the url to the customizer including the poduct_id
            $customizer_url = add_query_arg( 'product_id', $product->id, get_permalink( $customize_page_id ) );
            
            //Add the passthru data to the url
            $passthru = array();
            $passthru['post'] = array();
            $passthru['post']['quantity'] = 1;
            $passthru['post']['add-to-cart'] = $product->id;
            $passthru_encoded = base64_encode( json_encode( $passthru ) );
            $customizer_url = add_query_arg( 'h', $passthru_encoded, $customizer_url );
            
            //Build the link (a tag) to the customizer
            $customize_link = sprintf( 
                '<a ' .
                    'href="%s" ' . 
                    'rel="nofollow" ' .
                    'class="button %s product_type_%s" ' .
                '>%s</a>',
		esc_url( $customizer_url ),
		$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
		esc_attr( $product->product_type ),
		esc_html( "Customize" ) );
	
            
            $ret = $customize_link;
        } else {
            $ret = $link;
        }
        
        return $ret;
    }
    
    /**
     * Handles the add_to_cart action for customizing customizable products.
     * @param string $url The current url.
     */
    public function mystyle_add_to_cart_handler_customize( $url ) {
        $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
        
        //set up an array of data to pass to/through the customizer.
        $passthru = array(
            'post' => $_REQUEST,
        );

        //add all available product attributes (if there are any) to the pass through data
        $product = new WC_Product_Variable( $product_id );
        $attributes = $product->get_variation_attributes();
        if( !empty( $attributes ) ) {
            $passthru['attributes'] = $attributes;
        }

        $customize_page_id = MyStyle_Customize_Page::get_id();
        
        $args = array(
                    'product_id' => $product_id,
                    'h' => base64_encode(json_encode($passthru)),
                );
        
        $customizer_url = add_query_arg( $args, get_permalink( $customize_page_id ) );
        wp_safe_redirect( $customizer_url );
        
        //exit (unless called by phpunit)
        if( ! defined('PHPUNIT_RUNNING') ) {
            exit;
        }
    }
    
    /**
     * Handles the add_to_cart action for when an exising design is added to the
     * cart.
     * @param string $url The current url.
     */
    public function mystyle_add_to_cart_handler( $url ) {
        global $woocommerce;
        
        $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
        $design_id = absint( $_REQUEST['design_id'] );
        
        //Get the woocommerce cart
        $cart = $woocommerce->cart;
        
        //Add the mystyle meta data to the cart item
        $cart_item_data = array();
        $cart_item_data['mystyle_data'] = array( 'design_id' => $design_id );
        
        //Add the product and meta data to the cart
        $cart_item_key = $cart->add_to_cart(
                                    $product_id, //WooCommerce product id
                                    1, //quantity
                                    null, //variation id
                                    null, //variation attribute values
                                    $cart_item_data //extra cart item data we want to pass into the item
                            );
        
    }

    /**
     * Filter the construction of the cart item product.
     * @param array $product
     * @param array $cart_item
     * @param string $cart_item_key
     * @return mixed Returns a WC_Product or one of its child classes.
     */
    public function filter_cart_item_product( $product, $cart_item, $cart_item_key ){
        
        //Note: we put the require_once here because we need to wait until after woocommerce is bootstrapped
        require_once( MYSTYLE_INCLUDES . 'model/class-mystyle-product.php' );
        require_once( MYSTYLE_INCLUDES . 'model/class-mystyle-product-variation.php' );
        
        //convert the product to a MyStyle_Product (if it has mystyle_data)
        if( 
            ( array_key_exists( 'mystyle_data', $cart_item ) ) &&
            ( $cart_item['mystyle_data'] != null )
          ) 
        {
            $design_id = $cart_item['mystyle_data']['design_id'];
            $design = MyStyle_DesignManager::get( $design_id );
            if( get_class( $product ) == 'WC_Product_Variation' ) {
                $product = new MyStyle_Product_Variation( $product, $design, $cart_item_key );
            } else {
                $product = new MyStyle_Product( $product, $design, $cart_item_key );
            }
        }
        
        return $product;
    }
    
    /**
     * Filter the woocommerce_get_cart_item_from_session and add our session 
     * data.
     * @param array $session_data The current session_data.
     * @param array $values The values that are to be stored in the session.
     * @param string $key The key of the cart item.
     * @return string Returns the updated cart image tag.
     */
    public function get_cart_item_from_session( $session_data, $values, $key ) {
        
        // Fix for WC 2.2 (if our data is missing from the cart item, get it from the session variable 
        if( ! isset( $session_data['mystyle_data'] ) ) {
            $cart_item_data = WC()->session->get( 'mystyle_' . $key );
            $session_data['mystyle_data'] = $cart_item_data['mystyle_data'];
        }
	
        return $session_data;
    }
    
    /**
     * Resets the singleton instance. This is used during testing if we want to
     * clear out the existing singleton instance.
     * @return MyStyle_Cart Returns the singleton instance of
     * this class.
     */
    public static function reset_instance() {
        
        self::$instance = new self();

        return self::$instance;
    }
    
    
    /**
     * Gets the singleton instance.
     * @return MyStyle_Cart Returns the singleton instance of
     * this class.
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
}

