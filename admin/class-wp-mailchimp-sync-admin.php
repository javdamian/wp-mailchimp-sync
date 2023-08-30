<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wp_Mailchimp_Sync
 * @subpackage Wp_Mailchimp_Sync/admin
 */

// Include the WPMailchimpSync class
include_once(plugin_dir_path(__FILE__) . 'class-mailchimp-sync-handler.php');
include_once(plugin_dir_path(__FILE__) . 'class-mailchimp-sync-import-audiences.php');
//include_once(plugin_dir_path(__FILE__) . 'class-mailchimp-sync-import-roles-table.php');

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wp_Mailchimp_Sync
 * @subpackage Wp_Mailchimp_Sync/admin
 * @author     Javier Damián Mendoza <jdamian.m86@gmail.com>
 * 
 */
class Wp_Mailchimp_Sync_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wp_mailchimp_sync    The ID of this plugin.
	 */
	private $wp_mailchimp_sync;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wp_mailchimp_sync       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($wp_mailchimp_sync, $version)
	{

		$this->wp_mailchimp_sync = $wp_mailchimp_sync;
		$this->version = $version;
		$this->options = get_option('data_settings_wp_mailchimp_sync');

		// Add the following lines to instantiate the WPMailchimpSync class
		$mailchimp_sync = new MailchimpSyncHandler();

		add_action('admin_menu', array($this, 'wp_mailchimp_sync_add_submenu_page'));
		add_action('admin_init', array($this, 'page_init'));
		add_action('user_register', array($mailchimp_sync, 'sync_user_to_mailchimp'), 10, 1);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style($this->wp_mailchimp_sync, plugin_dir_url(__FILE__) . 'css/wp-mailchimp-sync-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->wp_mailchimp_sync, plugin_dir_url(__FILE__) . 'js/wp-mailchimp-sync-admin.js', array('jquery'), $this->version, false);
	}


	/**
	 * Register a menu page with a Dashicon.
	 * 
	 * @since    1.0.0
	 * @access   public 
	 * 
	 */
	public function wp_mailchimp_sync_add_submenu_page()
	{
		add_menu_page(
			'WP Mailchimp Sync',
			'WP Mailchimp Sync',
			'manage_options',
			'wp-mailchimp-sync',
			array($this, 'wp_mailchimp_sync_import_display'),
			'dashicons-rest-api', // menu icon
			5 // priority
		);
	}

	/**
	 * Display form
	 * 
	 * @since    1.0.0
	 * @access   public 
	 * 
	 */

	 public function wp_mailchimp_sync_import_display()
	 {
		 $this->options = get_option('data_settings_wp_mailchimp_sync');
		 $api_key = $this->options['mailchimp_api_key']; // Assuming the API key is stored in the options
		 $audience_id = $this->options['mailchimp_audience_id']; // Assuming the audience ID is stored in the options
		 $saved_audiences = get_option('mailchimp_sync_import_audiences', array());
		 $selected_roles = get_option('mailchimp_sync_selected_roles', array());
		 $table = new Mailchimp_Sync_Import_Audiences_Table($audiences, $selected_roles);

		 echo '<div class="settings-wrap">';
		 echo '<h1 class="settings-title">Wp Mailchimp Sync Settings</h1>';
		 echo '<div class="settings-form-wrap">';
		 echo '<form method="post" action="options.php">';
	 
		 // Display any errors or messages related to the settings
		 settings_errors();
		 // Output the hidden setting fields
		 settings_fields('data_settings_wp_mailchimp_sync');
	 
		 echo '<div class="settings-tab">';
		 echo '<h3>Please enter your Mailchimp API Key and Mailchimp Audience ID to enable synchronization with Mailchimp</h3>';
	 
		 do_settings_sections('settings_page_wp_mailchimp_sync');
		 // Output the submit button for the form
		 submit_button('Save', 'primary', 'submit', false, null);
	 
		 echo '</div>';
		 echo '</form>';
		 echo '</div>';
	 
		if ($api_key && $audience_id) {
			 echo '<div class="import-audiences">';
			 echo '<h3>Import Audiences:</h3>';
	 
			 if (!empty($saved_audiences)) {
				$table->prepare_items();
				$table->display();
			 } else {
				 echo '<p>No audiences found.</p>';
			 }
	 
			 if (!isset($_POST['import_audiences'])) {
                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="import_audiences" value="1">';
                echo '<button id="importButton" class="button button-primary button-import" type="submit">Import audiences</button>';
                echo '</form>';
            } else {
				// Code to fetch and display the updated audience table
				$table->prepare_items();
				$table->display();
                echo '<p>Import successful. Updated audience table:</p>';
                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="import_audiences" value="1">';
                echo '<button id="importButton" class="button button-primary button-import" type="submit">Import audiences</button>';
                echo '</form>';
            }
        }
        echo '</div>';
	}

	 

		/**
		 * Register and add settings
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 */
		public function page_init()
		{
			register_setting(
				'data_settings_wp_mailchimp_sync', // Option group
				'data_settings_wp_mailchimp_sync', // Option name
				array($this, 'sanitize') // Sanitize
			);

			add_settings_section(
				'settings_section_mailchimp_api_key',
				'Mailchimp API Key',
				array($this, 'print_mailchimp_api_key_section_info'),
				'settings_page_wp_mailchimp_sync'
			);

			add_settings_field(
				'mailchimp_api_key',
				'API Key',
				array($this, 'api_key_callback'),
				'settings_page_wp_mailchimp_sync',
				'settings_section_mailchimp_api_key'
			);

			add_settings_section(
				'settings_section_mailchimp_audience_id',
				'Mailchimp Audience ID',
				array($this, 'print_mailchimp_audience_id_section_info'),
				'settings_page_wp_mailchimp_sync'
			);

			add_settings_field(
				'mailchimp_audience_id',
				'Audience ID',
				array($this, 'audience_id_callback'),
				'settings_page_wp_mailchimp_sync',
				'settings_section_mailchimp_audience_id'
			);
		}

		/**
		 * Sanitize each setting field as needed
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 * @param array $input Contains all settings fields as array keys
		 */
		public function sanitize($input)
		{
			$new_input = array();
			if (isset($input['mailchimp_api_key'])) {
				$new_input['mailchimp_api_key'] = sanitize_text_field($input['mailchimp_api_key']);
			}
			if (isset($input['mailchimp_audience_id'])) {
				$new_input['mailchimp_audience_id'] = sanitize_text_field($input['mailchimp_audience_id']);
			}
			return $new_input;
		}

		/** 
		 * Print the Section text
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 */
		public function print_section_info()
		{
			print 'Please configure the settings below:';
		}

		/** 
		 * Get the settings option array and print one of its values
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 */
		public function api_key_callback()
		{
			printf(
				'<input type="text" id="mailchimp_api_key" name="data_settings_wp_mailchimp_sync[mailchimp_api_key]" value="%s" />',
				isset($this->options['mailchimp_api_key']) ? esc_attr($this->options['mailchimp_api_key']) : ''
			);
		}

		/** 
		 * Get the settings option array and print one of its values
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 */
		public function audience_id_callback()
		{
			printf(
				'<input type="text" id="mailchimp_audience_id" name="data_settings_wp_mailchimp_sync[mailchimp_audience_id]" value="%s" />',
				isset($this->options['mailchimp_audience_id']) ? esc_attr($this->options['mailchimp_audience_id']) : ''
			);
		}

		/** 
		 * Get the settings option array and print one of its values
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 */
		// public function api_debug_callback() {
		// 	printf(
		// 		'<label class="switch">
		// 			<input type="checkbox" name="data_settings_wp_mailchimp_sync[api_debug]" %s id="api_debug" value="1" >
		// 			<span class="slider round"></span>
		// 		</label>',
		// 		(isset($this->options['api_debug']) && $this->options['api_debug']==1) ? 'checked' : ''
		// 	);
		// }


		/** 
		 * Return the api key of the settings
		 * 
		 * @since    1.0.0
		 * @access   public 
		 * 
		 */
		// public function get_api_key()
		// {
		// 	return isset($this->options['api_key']) ? $this->options['api_key'] : '';
		// }

	}
