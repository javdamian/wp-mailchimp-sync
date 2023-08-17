<?php

class WPMailchimpSync {
    // Your Mailchimp API Key
    private $mailchimp_api_key;
    // Your Mailchimp Audience ID
    private $mailchimp_audience_id;

    public function __construct() {
        $this->mailchimp_api_key = get_option('data_settings_wp_mailchimp_sync')['api_key'];
        $this->mailchimp_audience_id = get_option('data_settings_wp_mailchimp_sync')['audience_id'];
        add_action( 'user_register', array( $this, 'sync_user_to_mailchimp' ), 10, 1 );
    }

    public function sync_user_to_mailchimp( $user_id ) {
        $user_info = get_userdata( $user_id );
        $data = array(
            'email_address' => $user_info->user_email,
            'status' => 'subscribed',
            'merge_fields' => array(
                'FNAME' => $user_info->first_name,
                'LNAME' => $user_info->last_name,
            ),
        );
        $url = 'https://<dc>.api.mailchimp.com/3.0/lists/' . $this->mailchimp_audience_id . '/members/';
        // Send a POST request to Mailchimp
        $response = wp_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'user:' . $this->mailchimp_api_key ),
            ),
            'body' => json_encode( $data ),
        ));
        // Check for errors
        if ( is_wp_error( $response ) ) {
            error_log( $response->get_error_message() );
        }
    }
}
