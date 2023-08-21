<?php

class Mailchimp_Sync_Import_Audiences {
    
    public function __construct() {
        $this->mailchimp_api_key = get_option('data_settings_wp_mailchimp_sync')['mailchimp_api_key'];
        $this->mailchimp_audience_id = get_option('data_settings_wp_mailchimp_sync')['mailchimp_audience_id'];
        // add_action( 'user_register', array( $this, 'sync_user_to_mailchimp' ), 10, 1 );
    }


    public function import() {
        // Fetch Mailchimp audiences
        $api_key = $this->mailchimp_api_key; // Assuming the API key is stored in the options
        $url_parts = explode('-', $api_key);
        $dc = $url_parts[1];
        $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/';
    
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key
        );
    
        $response = wp_remote_get($url, array(
            'headers' => $headers
        ));
    
        if (is_wp_error($response)) {
            echo 'Error fetching Mailchimp audiences: ' . $response->get_error_message();
            return;
        }
    
        $audiences = json_decode(wp_remote_retrieve_body($response), true);
        // Now you can access the list of audiences
        $lists = $audiences['lists'];
        // var_dump($lists);
    
        // Retrieve saved audiences and selected roles from options
        $saved_audiences = get_option('mailchimp_sync_import_audiences', array());
        $selected_roles = get_option('mailchimp_sync_selected_roles', array());
    
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Audience ID</th>';
        echo '<th>Audience Name</th>';
        echo '<th>Roles</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    
        // Display the fetched audiences and allow selection of WordPress roles
        foreach ($lists as $list) {
            echo '<tr>';
            echo '<td>' . $list['id'] . '</td>';
            echo '<td>' . $list['name'] . '</td>';
    
            // Get the list of WordPress roles
            $wp_roles = wp_roles();
            $roles = $wp_roles->get_names();
    
            echo '<td>';
            foreach ($roles as $role => $role_name) {
                $checked = in_array($role, $selected_roles) ? 'checked' : '';
                echo '<label><input type="checkbox" name="selected_roles[]" value="' . $role . '" ' . $checked . '> ' . $role_name . '</label><br>';
            }
            echo '</td>';
    
            echo '</tr>';
        }
    
        echo '</tbody>';
        echo '</table>';
    
        // Save the fetched audiences and selected roles to options
        update_option('mailchimp_sync_import_audiences', $lists);
        update_option('mailchimp_sync_selected_roles', $_POST['selected_roles']);
    }


}
