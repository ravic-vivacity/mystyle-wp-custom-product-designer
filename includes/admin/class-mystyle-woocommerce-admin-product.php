<?php

/**
 * MyStyle WooCommerce Admin Product class.
 *
 * The MyStyle WooCommerce Admin Product class hooks MyStyle into the
 * WooCommerce Product admin interace.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_WooCommerce_Admin_Product { 

    /**
     * Constructor, constructs the class and registers hooks.
     */
    public function __construct() {
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
    }

    /**
     * Init the mystyle woocommerce admin
     */
    function admin_init() {
        add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'add_product_data_tab' ) );
        add_action( 'woocommerce_product_write_panels', array( &$this, 'add_mystyle_data_panel' ) );
        add_action( 'woocommerce_process_product_meta', array( &$this, 'process_mystyle_data_panel' ) );
    }

    /**
     * Add a MyStyle tab to the product options tab set.
     */
    public static function add_product_data_tab() {
        echo '<li class="mystyle_product_tab mystyle_product_options"><a href="#mystyle_product_data">MyStyle</a></li>';
    }

    /**
     * Create the content of the MyStyle product options tab.
     * @global WP_Post $post The post that is currently being edited
     */
    public static function add_mystyle_data_panel() {
        global $post;

        // pull existing values
        $mystyle_enabled = get_post_meta( $post->ID, '_mystyle_enabled', true );
        $template_id = get_post_meta( $post->ID, '_mystyle_template_id', true );
        $customizer_ux = get_post_meta( $post->ID, '_mystyle_customizer_ux', true );
        $mystyle_design_id = get_post_meta( $post->ID, '_mystyle_design_id', true );
		$mystyle_print_type = get_post_meta( $post->ID, '_mystyle_print_type', true );

        ?>
            <div id="mystyle_product_data" class="panel woocommerce_options_panel">
                <div class="options_group">
                    <?php
                        woocommerce_wp_checkbox(
                            array(
                                'id' => '_mystyle_enabled',
                                'label' => __( 'Make Customizable?', 'mystyle' ),
                                'desc_tip'    => 'true',
                                'description' => __( 'Enable this option to make the product customizable.', 'mystyle' ),
                                'value'       => $mystyle_enabled,
                            )
                        );

                        woocommerce_wp_text_input(
                            array(
                                'id'          => '_mystyle_template_id',
                                'label'       => __( 'MyStyle Template ID', 'mystyle' ),
                                'placeholder' => '',
                                'desc_tip'    => 'true',
                                'description' => __( 'Enter the MyStyle Template Id for the product. For an example template, you can use Template Id 70.', 'woocommerce' ),
                                'value'       => $template_id,
                            )
			);

                        ?>
                    <p class="description" style="margin-left: 2em;">
                        Need a template? Check out our <a href="http://www.mystyleplatform.com/mystyle-product-catalog/" title="MyStyle Product Catalog" target="_blank">Product Catalog</a>.
                    </p>

                    <br/>
                    <div class="mystyle-toggle" onclick="mystyleTogglePanelVis('advanced')">
                        <a class="mystyle-toggle-link" title="Click to toggle">Advanced</a>
                        <a id="mystyle-toggle-handle-advanced" class="mystyle-toggle-handle" title="Click to toggle"></a>
                    </div>
                    <div class="mystyle-panel" id="mystyle-panel-advanced" style="display:none;">

                        <?php

                            woocommerce_wp_text_input(
                                array(
                                    'id'          => '_mystyle_design_id',
                                    'label'       => __( 'MyStyle Design ID', 'mystyle' ),
                                    'placeholder' => '',
                                    'desc_tip'    => 'true',
                                    'description' => __( 'Enter a MyStyle Design ID for the product to always start with.  You can get a design ID for any design your site has made by using the Design Manager (add-on).', 'mystyle' ),
                                    'value'       => $mystyle_design_id,
                                )
                            );

                            woocommerce_wp_text_input(
                                array(
                                    'id'          => '_mystyle_customizer_ux',
                                    'label'       => __( 'Alternate Customizer UX', 'mystyle' ),
                                    'placeholder' => '',
                                    'desc_tip'    => 'true',
                                    'description' => __( 'Alternate UX must be set up special for your site.  Do not use this unless you have a custom UX variant developed.', 'woocommerce' ),
                                    'value'       => $customizer_ux,
                                )
                            );

                        ?>
                        // print output dropdown
                        woocommerce_wp_select(
                            array(
                                'id'          => '_mystyle_print_type',
                                'label'       => __( 'Print Output Override', 'mystyle' ),
                                'placeholder' => 'DEFAULT',
                                'desc_tip'    => 'true',
                                'description' => __( 'This will override the product print output type setting.', 'mystyle' ),
                                'value'       => $mystyle_print_type,
                                'options'     => array(
                                                        'DEFAULT'       => 'DEFAULT',
                                                        'FULL-COLOR'    => 'FULL-COLOR',
                                                        'GREYSCALE'     => 'GREYSCALE',
                                                        'BLACK-ON-WHITE' => 'BLACK-ON-WHITE',
                                                        'WHITE-ON-BLACK' => 'WHITE-ON-BLACK',
                                                        'NO-PRINT-FILE' => 'NO-PRINT-FILE',
                                                ),
                            )
			);
                    </div> <!-- end advanced mystyle section -->

                </div>
            </div>
        <?php
    }

    /**
     * Process the mystyle tab options when a post is saved
     * @param integer $post_id The id of the post that is being saved.
     * @todo Unit test the validation logic
     */
    public static function process_mystyle_data_panel( $post_id ) {

        $mystyle_enabled = ( isset( $_POST['_mystyle_enabled'] ) && $_POST['_mystyle_enabled'] ) ? 'yes' : 'no' ;
        $template_id = $_POST['_mystyle_template_id'];
        $customizer_ux = $_POST['_mystyle_customizer_ux'];
        $mystyle_design_id = $_POST['_mystyle_design_id'];
		$mystyle_print_type = $_POST['_mystyle_print_type'];
        if ( $mystyle_enabled == 'yes' ) {
            if( $template_id != '' ) { //both options are set (store them)
                update_post_meta( $post_id, '_mystyle_enabled', 'yes' );
                update_post_meta( $post_id, '_mystyle_template_id', $template_id );
                update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
                update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
                update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );            } else { //enabled but no template id (store template_id, disable and notify)
                update_post_meta( $post_id, '_mystyle_enabled', 'no' );
                update_post_meta( $post_id, '_mystyle_template_id', $template_id );
                update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
                update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
                update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );                $validation_notice = MyStyle_Notice::create(
                                        'invalid_product_options',
                                        'You must choose a Template Id in order to make the product customizable.',
                                        'error'
                                    );
                mystyle_notice_add_to_queue( $validation_notice );
            }
        } else { //not enabled (store both)
            update_post_meta( $post_id, '_mystyle_enabled', 'no' );
            update_post_meta( $post_id, '_mystyle_template_id', $template_id );
            update_post_meta( $post_id, '_mystyle_customizer_ux', $customizer_ux );
            update_post_meta( $post_id, '_mystyle_design_id', $mystyle_design_id );
            update_post_meta( $post_id, '_mystyle_print_type', $mystyle_print_type );        }
    }

}
?>

<style>

    .advanced-section {
        display: none;
    }

</style>