<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
class Mailchimp_Sync_Import_Audiences_Table extends WP_List_Table {
    public function column_default($item, $column_name) {
        // Define how each column of the table will be displayed
        switch ($column_name) {
            case 'audience_id':
                return $item['id'];
            case 'audience_name':
                return $item['name'];
            case 'roles':
                // Display the selectable roles for each audience
                return $this->get_roles_select($item['id'], $item['selected_roles']);
            case 'actions':
                // Display the button to store the audiences and selected roles
                return $this->get_store_button($item['id']);
            default:
                return '';
        }
    }
    public function column_cb($item) {
        return '';
    }
    public function get_columns() {
        // Define the columns of the table
        $columns = array(
            'audience_id'   => 'Audience ID',
            'audience_name' => 'Audience Name',
            'roles'         => 'Roles',
            /*'actions'       => 'Actions' // Add a new column for actions*/
        );
        return $columns;
    }

    public function prepare_items() {
        // Get the audience and role data from the options
        $audiences = get_option('mailchimp_sync_import_audiences', array());
        $selected_roles = get_option('mailchimp_sync_selected_roles', array());

        // Set the table data
        $this->items = $audiences;

        // Set the table columns
        $this->_column_headers = array($this->get_columns(), array(), array());

        // Set the selected roles for each audience
        foreach ($audiences as &$audience) {
            $audience['roles'] = $this->get_roles_select($audience['id'], $audience['selected_roles']);
        }
    }
    private function get_roles_select($audience_id, $selected_roles) {
        $wp_roles = wp_roles();
        $roles = $wp_roles->get_names();

        $checkboxes_html = '';
        foreach ($roles as $role => $role_name) {
            $checked = in_array($role, $selected_roles) ? 'checked' : '';
            $checkboxes_html .= '<label>';
            $checkboxes_html .= '<input type="checkbox" name="selected_roles[' . $audience_id . '][]" value="' . $role . '" ' . $checked . '>';
            $checkboxes_html .= $role_name;
            $checkboxes_html .= '</label><br>';
        }

        return $checkboxes_html;
    }
   /* private function get_store_button($audience_id) {
        $button_html = '<form method="POST" action="">';
        $button_html .= '<input type="hidden" name="store_audiences" value="' . $audience_id . '">';
        $button_html .= '<button class="button button-primary" type="submit">Store</button>';
        $button_html .= '</form>';

        return $button_html;
    }*/
}

class Mailchimp_Sync_Import_Audiences {

    private $mailchimp_api_key;
    private $mailchimp_audience_id;

    public function __construct() {
        $this->mailchimp_api_key = get_option('data_settings_wp_mailchimp_sync')['mailchimp_api_key'];
        $this->mailchimp_audience_id = get_option('data_settings_wp_mailchimp_sync')['mailchimp_audience_id'];
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

        // Create an instance of the Mailchimp_Sync_Import_Audiences_Table class
        $table = new Mailchimp_Sync_Import_Audiences_Table();

        // Prepare the table data
        $table->prepare_items();

        // Display the table
        $table->display();

       // Display the button to store all audiences and selected roles
       echo '<form method="POST" action="">';
       echo '<input type="hidden" name="save_all_audiences" value="1">';
       echo '<button class="button button-primary" type="submit">Save All Audiences</button>';
       echo '</form>';

        // Check if the form is submitted to store the audiences and selected roles
        if (isset($_POST['save_all_audiences'])) {
            $selected_roles = $_POST['selected_roles'];

            // Update the selected roles for each audience
            foreach ($lists as &$audience) {
                $audience_id = $audience['id'];
                if (isset($selected_roles[$audience_id])) {
                    $audience['selected_roles'] = $selected_roles[$audience_id];
                }
            }

            // Save the updated audiences and selected roles to options
            update_option('mailchimp_sync_import_audiences', $lists);
            update_option('mailchimp_sync_selected_roles', $selected_roles);

            echo 'Audiences and selected roles stored successfully.';
        }


        
        /*// Display the button to store all audiences and selected roles
        echo '<form method="POST" action="">';
        echo '<input type="hidden" name="save_all_audiences" value="1">';
        echo '<button class="button button-primary" type="submit">Store All Audiences</button>';
        echo '</form>';*/
    }

}