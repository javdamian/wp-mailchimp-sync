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
include_once(plugin_dir_path(__FILE__) . 'wp-mailchimp-sync.php');

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wp_Mailchimp_Sync
 * @subpackage Wp_Mailchimp_Sync/admin
 * @author     Javier Damián Mendoza <jdamian.m86@gmail.com>
 * 
 */
class Wp_Mailchimp_Sync_Admin {

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
	public function __construct( $wp_mailchimp_sync, $version ) {

		$this->wp_mailchimp_sync = $wp_mailchimp_sync;
		$this->version = $version;
		$this->options = get_option('data_settings_wp_mailchimp_sync');
		
	    // Add the following lines to instantiate the WPMailchimpSync class
    	$mailchimp_sync = new WPMailchimpSync();

		add_action('admin_menu', array($this, 'wp_mailchimp_sync_add_submenu_page'));
		add_action('admin_init', array($this, 'page_init'));

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->wp_mailchimp_sync, plugin_dir_url( __FILE__ ) . 'css/wp-mailchimp-sync-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->wp_mailchimp_sync, plugin_dir_url( __FILE__ ) . 'js/wp-mailchimp-sync-admin.js', array( 'jquery' ), $this->version, false );

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
	    // Retrieve the plugin options from the database
	    $this->options = get_option('data_settings_wp_mailchimp_sync');
	?>
	    <div class="settings-wrap">
	        <h1 class="settings-title">Wp Mailchimp Sync Settings</h1>
	        <div class="settings-form-wrap">
	            <h2 class="nav-tab-wrapper">
	                <a href="#api-key" class="nav-tab">Enter an API Key</a>
	                <a href="#import-audiences" class="nav-tab">Import MailChimp "Audiences"</a>
	                <a href="#user-roles" class="nav-tab">Select which User Roles sync to which Audience</a>
	                <a href="#automate-sync" class="nav-tab">Automate the sync</a>
	                <a href="#manual-sync" class="nav-tab">Push a manual sync</a>
	            </h2>
	            <form method="post" action="options.php">
	                <?php
	                // Display any errors or messages related to the settings
	                settings_errors();
	
	                // Output the hidden setting fields
	                settings_fields('data_settings_wp_mailchimp_sync');
	                ?>
	                <div id="api-key" class="settings-tab">
	                    <h3>Enter an API Key</h3>
	                    <?php
	                    do_settings_sections('settings_page_wp_mailchimp_sync');
		                // Output the submit button for the form
		                submit_button('Save Changes', 'primary', 'submit', false, null);                    
	                    ?>
	                    <input type="hidden" name="action" value="save_api_key">
	                </div>
	                <div id="import-audiences" class="settings-tab">
	                    <h3>Import MailChimp "Audiences"</h3>
	                    <?php
	                    // Additional settings sections and fields for importing audiences
	                    ?>
	                    <p>This is the Import Audiences tab.</p>
	                    <input type="hidden" name="action" value="import_audiences">
	                </div>
	                <div id="user-roles" class="settings-tab">
	                    <h3>Select which User Roles sync to which Audience</h3>
	                    <?php
	                    // Additional settings sections and fields for selecting user roles
	                    ?>
	                    <p>This is the User Roles tab.</p>
	                    <input type="hidden" name="action" value="save_user_roles">
	                </div>
	                <div id="automate-sync" class="settings-tab">
	                    <h3>Automate the sync</h3>
	                    <?php
	                    // Additional settings sections and fields for automating the sync
	                    ?>
	                    <p>This is the Automate Sync tab.</p>
	                    <input type="hidden" name="action" value="automate_sync">
	                </div>
	                <div id="manual-sync" class="settings-tab">
	                    <h3>Push a manual sync</h3>
	                    <?php
	                    // Additional settings sections and fields for manual sync
	                    ?>
	                    <p>This is the Manual Sync tab.</p>
	                    <input type="hidden" name="action" value="manual_sync">
	                </div>
	                <?php
	                ?>
	            </form>
	        </div>
	    </div>
	
	<?php
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
	public function sanitize($input) {
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
	public function api_key_callback() {
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
	public function audience_id_callback() {
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
