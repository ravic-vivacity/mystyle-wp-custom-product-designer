<?php

require_once(MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php');

/**
 * The FrontEndTest class includes tests for testing the MyStyle_FrontEnd class.
 *
 * @package MyStyle
 * @since 0.2.1
 * @todo Add the tests for the rest of the class functions/methods.
 */
class MyStyleFrontEndTest extends WP_UnitTestCase {
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );
        
        //Assert that the before_add_to_cart_button function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_before_add_to_cart_button'] );
        $this->assertContains( 'before_add_to_cart_button', $function_names );
        
        //Assert that the before_add_to_cart_button function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_after_add_to_cart_button'] );
        $this->assertContains( 'after_add_to_cart_button', $function_names );
    }
    
    /**
     * Test the mystyle_frontend_init function.
     */    
    public function test_mystyle_frontend_init() {
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Assert that the frontend stylesheet is registered
        global $wp_styles;
        $this->assertContains( 'myStyleFrontEndStylesheet', serialize( $wp_styles ) );
    }
    
    /**
     * Mock the mystyle_metadata
     * @param type $metadata
     * @param type $object_id
     * @param type $meta_key
     * @param type $single
     * @return string
     */
    function mock_mystyle_metadata($metadata, $object_id, $meta_key, $single){
        return 'yes';
    }
    
    /**
     * Test the before_add_to_cart_button function.
     */
    public function test_before_add_to_cart_button() {
        
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Assert that the expected code was output
        ob_start();
        $mystyle_frontend->before_add_to_cart_button();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( '<div class="mystyle-customizable-product">', $outbound );
    }
    
    /**
     * Test the after_add_to_cart_button function.
     */
    public function test_after_add_to_cart_button() {
         
        //Mock the global $post variable
        $post_vars = new stdClass();
        $post_vars->ID = 1;
        $GLOBALS['post'] = new WP_Post( $post_vars );
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        $mystyle_frontend = new MyStyle_Frontend();
        
        //Create the MyStyle Customize page (needed by the function)
        MyStyle_Customize_Page::create();
        
        //Assert that the expected code was output
        ob_start();
        $mystyle_frontend->after_add_to_cart_button();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( '<button class="mystyle_customize_button', $outbound );
    }
    
}