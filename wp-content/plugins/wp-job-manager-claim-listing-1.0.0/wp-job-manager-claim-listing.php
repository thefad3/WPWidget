<?php
/*
 * Plugin Name: 	WP Job Manager - Claim Listing
 * Plugin URI: 		https://astoundify.com/downloads/wp-job-manager-claim-listing/
 * Description: 	Allow listings to be "claimed" to indicate verified ownership. A fee can be charged using WooCommerce.
 * Version: 		1.0.0
 * Author: 		Astoundify
 * Author URI: 		http://astoundify.com
 * Text Domain: 	wp-job-manager-claim-listing
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WP_Job_Manager_Claim_Listing.
 *
 * Main WPJMCL class initializes the plugin.
 *
 * @class		WP_Job_Manager_Claim_Listing
 * @version		1.0.0
 * @author		Jeroen Sormani
 */
class WP_Job_Manager_Claim_Listing {


	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version = '1.0.1';


	/**
	 * Instace of WP_Job_Manager_Claim_Listing.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of WPJMCL.
	 */
	private static $instance;


	/**
	 * Construct.
	 *
	 * Initialize the class and plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) :
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		endif;

		// Check if WooCommerce is active
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
				return;
			endif;
		endif;

		// Check if WP Job Manger is active
		if ( ! in_array( 'wp-job-manager/wp-job-manager.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'wp-job-manager/wp-job-manager.php' ) ) :
				return;
			endif;
		endif;

		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

		// License updater
		add_action( 'admin_init', array( $this, 'license_updater' ), 9 );


		$this->init();

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}


	/**
	 * init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Load textdomain
		$this->load_textdomain();

		// Set available statuses
		$this->statuses = apply_filters( 'wpjmcl_claim_statuses', array(
			'approved' 	=> __( 'Approved', 'wp-job-manager-claim-listing' ),
			'pending' 	=> __( 'Pending', 'wp-job-manager-claim-listing' ),
			'verifying' => __( 'Verifying', 'wp-job-manager-claim-listing' ),
			'declined' 	=> __( 'Declined', 'wp-job-manager-claim-listing' ),
		) );

		/**
		 * Listing class
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpjmcl-listing.php';
		$this->listing = new WPJMCL_Listing();

		/**
		 * Claim class
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpjmcl-claims.php';
		$this->claims = new WPJMCL_Claims();

		/**
		 * Settings class
		 */
		if ( is_admin() ) :
			require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-wpjmcl-settings.php';
			$this->settings = new WPJMCL_Settings();
		endif;

	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		// Load textdomain
		load_plugin_textdomain( 'wp-job-manager-claim-listing', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue() {

		wp_enqueue_style( 'chosen', JOB_MANAGER_PLUGIN_URL . '/assets/css/chosen.css' );
		wp_enqueue_style( 'wp-job-manager-claim-listing', plugins_url( 'assets/css/wp-job-manager-claim-listing.css', __FILE__ ), array(
			'dashicons',
		), $this->version );

		wp_enqueue_script( 'wp-job-manager-claim-listing', plugins_url( 'assets/js/wp-job-manager-claim-listing.js', __FILE__ ), array(
			'jquery',
			'chosen',
		), $this->version );

	}


	/**
	 * License updater.
	 *
	 * Initialise the automatic license updater.
	 *
	 * @since 1.1.0
	 */
	public function license_updater() {

		include_once( 'includes/updater/class-astoundify-updater.php' );

		new Astoundify_Updater_Claims( __FILE__ );

	}


}


/**
 * The main function responsible for returning the WP_Job_Manager_Claim_Listing object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WP_Job_Manager_Claim_Listing()->method_name(); ?>
 *
 * @since 1.0.0
 *
 * @return object WP_Job_Manager_Claim_Listing class object.
 */
if ( ! function_exists( 'WP_Job_Manager_Claim_Listing' ) ) :

 	function WP_Job_Manager_Claim_Listing() {
		return WP_Job_Manager_Claim_Listing::instance();
	}

endif;

WP_Job_Manager_Claim_Listing();
