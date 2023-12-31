<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');


class Mailchimp_Sync_Import_Audiences_Table extends WP_List_Table
{
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
            default:
                return '';
        }
    }
    public function column_cb($item)
    {
        // Here you can display a checkbox for each row of the table
        return sprintf(
            '<input type="checkbox" name="selected_audiences[]" value="%s" />',
            $item['id']
        );
    }

    public function get_columns()
    {
        // Define the columns of the table
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'audience_id'   => 'Audience ID',
            'audience_name' => 'Audience Name',
            'roles'         => 'Roles'
        );

        return $columns;
    }

    public function prepare_items()
    {
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

    private function get_roles_select($audience_id, $selected_roles)
    {
        $wp_roles = wp_roles();
        $roles = $wp_roles->get_names();

        $select_html = '<select name="selected_roles[' . $audience_id . '][]" multiple>';
        foreach ($roles as $role => $role_name) {
            $selected = in_array($role, $selected_roles) ? 'selected' : '';
            $select_html .= '<option value="' . $role . '" ' . $selected . '>' . $role_name . '</option>';
        }
        $select_html .= '</select>';

        return $select_html;
    }
}

class Mailchimp_Sync_Import_Audiences
{

    public function __construct()
    {
        $this->mailchimp_api_key = get_option('data_settings_wp_mailchimp_sync')['mailchimp_api_key'];
        $this->mailchimp_audience_id = get_option('data_settings_wp_mailchimp_sync')['mailchimp_audience_id'];
    }

    public function import()
    {
        // Create an instance of the Mailchimp_Sync_Import_Audiences_Table class
        $table = new Mailchimp_Sync_Import_Audiences_Table();

        // Prepare the table data
        $table->prepare_items();

        // Display the table
        $table->display();
    }
}
