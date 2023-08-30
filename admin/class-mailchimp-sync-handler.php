<?php
class MailchimpSyncHandler {

    private $mailchimp_api_key;

    private $mailchimp_audience_id;
    
    // Variable to track if the API call has been made
    private $api_called = false;

    public function __construct() {
        $this->mailchimp_api_key = get_option('data_settings_wp_mailchimp_sync')['mailchimp_api_key'];
        $this->mailchimp_audience_id = get_option('data_settings_wp_mailchimp_sync')['mailchimp_audience_id'];
        // add_action( 'user_register', array( $this, 'sync_user_to_mailchimp' ), 10, 1 );
    }

    public function sync_user_to_mailchimp( $user_id ) {
        
    // Check if the API call has already been made
        if ($this->api_called) {
            return;
        }

        // Set the API call flag to true
        $this->api_called = true;

        $user_info = get_userdata( $user_id );

        $data = array(
            'email_address' => $user_info->user_email,
            'status' => 'subscribed',
            'merge_fields' => array(
                'FNAME' => $user_info->first_name,
                'LNAME' => $user_info->last_name,
            ),
        );
        
        $url_parts = explode('-', $this->mailchimp_api_key);
        $dc = $url_parts[1];
        $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $this->mailchimp_audience_id . '/members/';

        // Send a POST request to Mailchimp
        $response = wp_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'user:' . $this->mailchimp_api_key ),
            ),
            'body' => json_encode( $data ),
        ));

        if (is_wp_error($response)) {
            // Register an error message
            $response_message = 'An error occurred while syncing the user to Mailchimp. Please try again later.';
            add_settings_error(
                'mailchimp_sync_settings',
                'mailchimp_sync_error',
                $response_message,
                'error'
            );
        } else {
            // Check the response status code
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);

            if ($response_code >= 200 && $response_code < 300) {
                // User synced successfully
                $response_message = 'User synced to Mailchimp successfully.';
                add_settings_error(
                    'mailchimp_sync_settings',
                    'mailchimp_sync_success',
                    $response_message,
                    'success'
                );
            } else {
                // User sync failed
                $response_message = 'User sync to Mailchimp failed. Status code: ' . $response_code . '. Error message: ' . $response_data['title'];

                // Check if the same error message has already been added
                $existing_errors = get_settings_errors('mailchimp_sync_settings');
                $existing_error_messages = array_column($existing_errors, 'message');
                if (!in_array($response_message, $existing_error_messages)) {
                    add_settings_error(
                        'mailchimp_sync_settings',
                        'mailchimp_sync_error',
                        $response_message,
                        'error'
                    );
                }
            }
        }

    }

}