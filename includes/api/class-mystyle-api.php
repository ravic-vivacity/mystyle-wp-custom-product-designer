<?php

/**
 * MyStyle API class. 
 * 
 * The MyStyle API class works with the MyStyle API.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_API implements MyStyle_API_Interface {
    
    private $api_endpoint_url;
    
    /**
     * Constructor.
     */
    public function __construct( $api_endpoint_url ) {
        $this->api_endpoint_url = $api_endpoint_url;
    }
    
    /**
     * Retrieves design data from the API and adds it to the passed design
     * object.
     * @param MyStyle_Design $design
     * @return \MyStyle_Design
     */
    public function add_api_data_to_design( MyStyle_Design $design ) {
            
        //Set up the api call variables.
        $api_key = MyStyle_Options::get_api_key();
        $secret = MyStyle_Options::get_secret();
        $action = 'design';
        $method = 'get';
        $data = '{"design_id":[' . $design->get_design_id() . ']}';
        $ts = time();

        $toHash = $action . $method . $api_key . $data . $ts;
        $sig = base64_encode( hash_hmac( 'sha256', $toHash, $secret, true ) );

        $post_data = array();
        $post_data['action'] = $action;
        $post_data['method'] = $method;
        $post_data['app_id'] = $api_key;
        $post_data['data'] = $data;
        $post_data['sig'] = $sig;
        $post_data['ts'] = $ts;
        //$post_data['session'] = //not currently being used
        //$post_data['user_id'] = //not currently being used

        $response = wp_remote_post( $this->api_endpoint_url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $post_data,
                'cookies' => array(),
            )
        );

        if ( is_wp_error( $response ) ) {
            //We fail silently and write to the log.
            $error_message = $response->get_error_message();
            error_log( $error_message );
        } else {            
            $response_data = json_decode( $response['body'], true ); //['data'][$design_id]);
            $design_data = $response_data['data'][ $design->get_design_id() ];            
            //var_dump($design_data);

            $design->add_api_data( $design_data );
        }
        
        return $design;
    }
    
    /**
     * Creates and returns a MyStyle_User object using the passed user_id and
     * data retrieved from the API.
     * @param integer $user_id The MyStyle user id.
     * @return \MyStyle_User
     */
    public function get_user( $user_id ) {
        /* @var $user \MyStyle_User */
        $user = null;
        
        //Set up the api call variables.
        $api_key = MyStyle_Options::get_api_key();
        $secret = MyStyle_Options::get_secret();
        $action = 'user';
        $method = 'get';
        $data = '{"user_id":[' . $user_id . ']}';
        $ts = time();

        $toHash = $action . $method . $api_key . $data . $ts;
        $sig = base64_encode( hash_hmac( 'sha256', $toHash, $secret, true ) );

        $post_data = array();
        $post_data['action'] = $action;
        $post_data['method'] = $method;
        $post_data['app_id'] = $api_key;
        $post_data['data'] = $data;
        $post_data['sig'] = $sig;
        $post_data['ts'] = $ts;
        //$post_data['session'] = //not currently being used
        //$post_data['user_id'] = //not currently being used

        $response = wp_remote_post( $this->api_endpoint_url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $post_data,
                'cookies' => array(),
            )
        );

        if ( is_wp_error( $response ) ) {
            //We fail silently and write to the log.
            $error_message = $response->get_error_message();
            error_log( $error_message );
        } else {            
            $response_data = json_decode( $response['body'], true );
            //var_dump($response_data);
            $user_data = $response_data['data'][ $user_id ];            
            $user = new \MyStyle_User( $user_id, $user_data['email'] );
        }
        
        return $user;
    }

}


