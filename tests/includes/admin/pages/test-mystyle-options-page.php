<?php

require_once( MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-options-page.php' );

/**
 * The MyStyleOptionsPageTest class includes tests for testing the MyStyle_Options_Page
 * class
 *
 * @package MyStyle
 * @since 0.1.0
 */
class MyStyleOptionsPageTest extends WP_UnitTestCase {
    
    /**
     * Overrwrite the setUp function so that our custom tables will be persisted
     * to the test database.
     * 
     * Note: we need our tables because some of the functions here invoke hooks
     * that need the tables.
     */
    function setUp() {
        // Perform the actual task according to parent class.
        parent::setUp();
        // Remove filters that will create temporary tables. So that permanent tables will be created.
        remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
        remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
        
        //Create the tables
        MyStyle_Install::create_tables();
    }
    
    /**
     * Overrwrite the tearDown function to remove our custom tables.
     */
    function tearDown() {
        global $wpdb;
        // Perform the actual task according to parent class.
        parent::tearDown();
        
        //Drop the tables that we created
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Design::get_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Session::get_table_name());
    }
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['admin_menu'] );
        $this->assertContains( 'add_page_to_menu', $function_names );
               
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['admin_init'] );
        $this->assertContains( 'admin_init', $function_names );
    }
    
    /**
     * Test the admin_init function.
     */    
    public function test_admin_init() {
        global $wp_filter;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Run the function
        $mystyle_options_page->admin_init();
        
        //Assert that the expected settings fields were registered and rendered
        ob_start();
        settings_fields( 'mystyle_options' );
        //do_settings_sections( 'mystyle_settings' );
        $outbound = ob_get_contents();
        ob_end_clean();
        
        //Assert that the mystyle_options hidden field is registered/rendered
        $this->assertContains( "value='mystyle_options'", $outbound );
        
        //Assert that the action field is registered/rendered.
        $this->assertContains( '<input type="hidden" name="action" value="update" />', $outbound );
        
        //Assert taht the _secret_wpnonce field is registered/rendered.
        $this->assertContains( '<input type="hidden" id="_wpnonce" name="_wpnonce"', $outbound );
        
        //Assert taht the _wp_http_referer field is registered/rendered.
        $this->assertContains( '<input type="hidden" name="_wp_http_referer"', $outbound );
    }
    
    /**
     * Test the mystyle_account_settings_section function.
     */    
    public function test_mystyle_account_settings_section() {
        global $wp_filter;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Run the function
        $mystyle_options_page->admin_init();
        
        //Assert that the expected settings fields were registered and rendered
        ob_start();
        do_settings_sections( 'mystyle_account_settings' );
        $outbound = ob_get_contents();
        ob_end_clean();
        
        //Assert that the api key field is registered/rendered.
        $this->assertContains( 'name="mystyle_options[api_key]"', $outbound );
        
        //Assert that the secret field is registered/rendered.
        $this->assertContains( 'name="mystyle_options[secret]"', $outbound );
    }
    
    /**
     * Test the mystyle_account_settings_section function.
     */    
    public function test_mystyle_advanced_settings_section() {
        global $wp_filter;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Run the function
        $mystyle_options_page->admin_init();
        
        //Assert that the expected settings fields were registered and rendered
        ob_start();
        do_settings_sections( 'mystyle_advanced_settings' );
        $outbound = ob_get_contents();
        ob_end_clean();
        
        //Assert that the enable_flash field is registered/rendered.
        $this->assertContains( '<input type="checkbox" id="mystyle_enable_flash" name="mystyle_options[enable_flash]" value="1"  />', $outbound );
    }
    
    /**
     * Test the render_page function.
     */    
    public function test_render_page() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the options page was rendered
        ob_start();
        $mystyle_options_page->render_page();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'MyStyle Settings', $outbound );
    }
    
    /**
     * Test the add_page_to_menu function.
     */    
    public function test_add_page_to_menu() {
        
        //wp_set_current_user($this->factory->user->create( array( 'role' => 'administrator' ) ) );
        
        //Assert that the menu page doesn't yet exist
        $this->assertEquals( '', menu_page_url( 'mystyle', false ) );
        
        $mystyle_options_page = new MyStyle_Options_Page();
        $mystyle_options_page->add_page_to_menu();
        
        //Assert that the menu page was added
        $expected = 'http://example.org/wp-admin/admin.php?page=mystyle';
        $this->assertEquals( $expected, menu_page_url( 'mystyle', false ) );
    }
    
    /**
     * Test the render_access_section_text function.
     */    
    public function test_render_access_section_text() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the access section was rendered
        ob_start();
        $mystyle_options_page->render_access_section_text();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'MyStyle License', $outbound );
    }
    
    /**
     * Test the render_api_key function.
     */    
    public function test_render_api_key() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the API Key field was rendered
        ob_start();
        $mystyle_options_page->render_api_key();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'MyStyle API Key', $outbound );
    }
    
    /**
     * Test the render_secret function.
     */    
    public function test_render_secret() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the Secret field was rendered
        ob_start();
        $mystyle_options_page->render_secret();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'MyStyle Secret', $outbound );
    }
    
    /**
     * Test the render_advanced_section_text function.
     */    
    public function test_render_advanced_section_text() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the access section was rendered
        ob_start();
        $mystyle_options_page->render_advanced_section_text();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'For advanced users only.', $outbound );
    }
    
    /**
     * Test the render_enable_flash function.
     */    
    public function test_render_enable_flash() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the enable_flash field was rendered
        ob_start();
        $mystyle_options_page->render_enable_flash();
        $outbound = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'Use the Flash version', $outbound );
    }
    
    /**
     * Test that the validate function returns an error
     * when the api_key input is invalid.
     */
    public function test_validate_invalid_api_key() {
        //Clear out any previous settings errors.
        global $wp_settings_errors;
        $wp_settings_errors = null;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        $input = array();
        $input['api_key'] = 'not valid';
        $input['secret']  = 'validsecret';
        $input['enable_flash'] = 0;
        $input['customize_page_title_hide'] = 0;
        $input['mystyle_form_integration_config'] = 0;
        $input['enable_alternate_design_complete_redirect'] = 0;
        $input['alternate_design_complete_redirect_url'] = '';
        $input['redirect_url_whitelist'] = '';
        
        //Run the function.
        $new_options = $mystyle_options_page->validate($input);
        
        //Get the messages
        $settings_errors = get_settings_errors();
        
        //Assert that an error was thrown
        $this->assertEquals( 'error', $settings_errors[0]['type'] );
        
        //Assert that the settings were not stored.
        $this->assertTrue( empty( $new_options['api_key'] ) );
    }
    
    /**
     * Test that the validate function returns an error
     * when the api_key input contains html and javascript.
     */
    public function test_validate_attack_on_api_key() {
        //Clear out any previous settings errors.
        global $wp_settings_errors;
        $wp_settings_errors = null;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        $input = array();
        $input['api_key'] = '"><script>alert(document.cookie)</script>';
        $input['secret'] = 'validsecret';
        $input['enable_flash'] = 0;
        $input['customize_page_title_hide'] = 0;
        $input['mystyle_form_integration_config'] = 0;
        $input['enable_alternate_design_complete_redirect'] = 0;
        $input['alternate_design_complete_redirect_url'] = '';
        $input['redirect_url_whitelist'] = '';
        
        //Run the function.
        $new_options = $mystyle_options_page->validate($input);
        
        //Get the messages
        $settings_errors = get_settings_errors();
        
        //Assert that an error was thrown
        $this->assertEquals( 'error', $settings_errors[0]['type'] );
        
        //Assert that the settings were not stored.
        $this->assertTrue( empty( $new_options['api_key'] ) );
    }
    
    /**
     * Test the validate function doesn't throw any errors when
     * the api_key input is valid.
     */
    public function test_validate_valid_api_key() {
        //Clear out any previous settings errors.
        global $wp_settings_errors;
        $wp_settings_errors = null;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        $input = array();
        $input['api_key'] = 'A0000';
        $input['secret'] = 'validsecret';
        $input['enable_flash']  = 0;
        $input['customize_page_title_hide'] = 0;
        $input['mystyle_form_integration_config'] = 0;
        $input['enable_alternate_design_complete_redirect'] = 0;
        $input['alternate_design_complete_redirect_url'] = '';
        $input['redirect_url_whitelist'] = '';
        
        //Run the function.
        $new_options = $mystyle_options_page->validate($input);
        
        //Get the messages
        $settings_errors = get_settings_errors();
        $type = $settings_errors[0]['type'];
        
        //Assert that no errors were thrown.
        foreach ( $settings_errors as $key => $details ) {
            $this->assertNotEquals( 'error', $details['type'] );
        }
        
        //Assert that the settings were saved.
        $this->assertEquals( 'updated', $settings_errors[0]['type'] );
        
        //Assert that the settings were stored.
        $this->assertFalse( empty( $new_options['api_key'] ) );
    }
    
    /**
     * Test that the validate function returns an error
     * when the secret input is invalid.
     */
    public function test_validate_invalid_secret() {
        //Clear out any previous settings errors.
        global $wp_settings_errors;
        $wp_settings_errors = null;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        $input = array();
        $input['api_key'] = 'validapikey';
        $input['secret']  = 'not valid';
        $input['enable_flash'] = 0;
        $input['customize_page_title_hide'] = 0;
        $input['mystyle_form_integration_config'] = 0;
        $input['enable_alternate_design_complete_redirect'] = 0;
        $input['alternate_design_complete_redirect_url'] = '';
        $input['redirect_url_whitelist'] = '';
        
        //Run the function.
        $new_options = $mystyle_options_page->validate($input);
        
        //Get the messages
        $settings_errors = get_settings_errors();
        
        //Assert that an error was thrown
        $this->assertEquals( 'error', $settings_errors[0]['type'] );
        
        //Assert that the settings were not stored.
        $this->assertTrue( empty( $new_options['secret'] ) );
    }
    
    /**
     * Test that the validate function returns an error
     * when the secret input contains html and javascript.
     */
    public function test_validate_attack_on_secret() {
        //Clear out any previous settings errors.
        global $wp_settings_errors;
        $wp_settings_errors = null;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        $input = array();
        $input['api_key'] = 'validapikey';
        $input['secret']  = '"><script>alert(document.cookie)</script>';
        $input['enable_flash'] = 0;
        $input['customize_page_title_hide'] = 0;
        $input['mystyle_form_integration_config'] = 0;
        $input['enable_alternate_design_complete_redirect'] = 0;
        $input['alternate_design_complete_redirect_url'] = '';
        $input['redirect_url_whitelist'] = '';
        
        //Run the function.
        $new_options = $mystyle_options_page->validate( $input );
        
        //Get the messages
        $settings_errors = get_settings_errors();
        
        //Assert that an error was thrown
        $this->assertEquals( 'error', $settings_errors[0]['type'] );
        
        //Assert that the settings were not stored.
        $this->assertTrue( empty( $new_options['secret'] ) );
    }
    
    /**
     * Test the validate function doesn't throw any errors when
     * the secret input is valid.
     */
    public function test_validate_valid_secret() {
        //Clear out any previous settings errors.
        global $wp_settings_errors;
        $wp_settings_errors = null;
        
        $mystyle_options_page = new MyStyle_Options_Page();
        
        $input = array();
        $input['api_key'] = 'validapikey';
        $input['secret']  = 'A0000';
        $input['enable_flash'] = 0;
        $input['customize_page_title_hide'] = 0;
        $input['mystyle_form_integration_config'] = 0;
        $input['enable_alternate_design_complete_redirect'] = 0;
        $input['alternate_design_complete_redirect_url'] = '';
        $input['redirect_url_whitelist'] = '';
        
        //Run the function.
        $new_options = $mystyle_options_page->validate($input);
        
        //Get the messages
        $settings_errors = get_settings_errors();
        $type = $settings_errors[0]['type'];
        
        //Assert that no errors were thrown.
        foreach ( $settings_errors as $key => $details ) {
            $this->assertNotEquals( 'error', $details['type'] );
        }
        
        //Assert that the settings were saved.
        $this->assertEquals( 'updated', $settings_errors[0]['type']);
        
        //Assert that the settings were stored.
        $this->assertFalse( empty( $new_options['secret'] ) );
    }
    
    /**
     * Test the render_hide_customize_title function.
     */    
    public function test_render_hide_customize_title() {
        $mystyle_options_page = new MyStyle_Options_Page();
        
        //Assert that the force_mobile field was rendered
        ob_start();
        $mystyle_options_page->render_hide_customize_title();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'Hide the page title', $output );
    }
}
