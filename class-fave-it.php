<?php
/**
 * Fave-It.
 *
 * @package   FaveIt
 * @author    Manny Fleurmond <funkatronic@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2013 Cross Eye Design
 */

class FaveIt {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	public $plugin_slug = 'fave-it';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		 add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );


		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		//Load p2p
		require dirname( __FILE__ ) . '/inc/scb/load.php';
		scb_init( array( $this, 'p2p_init' ) );
		
		//Query actions/filters
		add_action( 'parse_query', array( $this, 'parse_query' ) );
		add_action( 'pre_user_query', array( $this, 'pre_user_query' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		
		//Admin
		add_action('admin_init', array($this,'admin_init'),0);
		
		//AJAX
		add_action('wp_ajax_fave', array( $this,'ajax_fave' ) );
		add_action('wp_ajax_nopriv_fave', array( $this,'ajax_fave' ) );
		add_action('wp_ajax_unfave', array( $this,'ajax_unfave' ) );
		add_action('wp_ajax_nopriv_unfave', array( $this,'ajax_unfave' ));
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		//Setup default post types
		add_option( 'fave_post_types', array( 'post', 'page' ) );
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}


	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug, plugins_url( 'js/public.js', __FILE__ ), array( 'jquery', 'jquery-ui-widget' ), $this->version );
		wp_localize_script( $this->plugin_slug, 'faveItData', array( 'faveNonce' => wp_create_nonce( 'fave_post', 'fave_nonce' ), 'userId' => get_current_user_id()) );
	}	

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Fave-It', $this->plugin_slug ),
			__( 'Fave-It', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}
	
	/**
	 * Actions for Posts 2 Posts core.
	 *
	 * @since    1.0.0
	 *
	 */
	public function p2p_init() {
		add_action( 'plugins_loaded', array( $this, 'load_p2p_core' ), 20 );
		add_action( 'init', array( $this, 'fave_connection' ) );
	}
	
	/**
	 * Load Posts 2 Posts core.
	 *
	 * @since    1.0.0
	 *
	 */
	public function load_p2p_core() {
		if ( function_exists( 'p2p_register_connection_type' ) )
			return;
	
		// TODO: replace APP_TD with your textdomain
		define( 'P2P_TEXTDOMAIN', $this->plugin_slug );
	
		require_once dirname( __FILE__ ) . '/inc/p2p-core/init.php';
	
		// TODO: can't use activation hook
		add_action( 'admin_init', array( 'P2P_Storage', 'install' ) );	
	}
	
	/**
	 * Create fave connection
	 *
	 * @since    1.0.0
	 *
	 */
	public function fave_connection() {
		$types = get_option( 'fave_post_types', array( 'post', 'page' ) );	
		$types - apply_filters( 'fave_post_types', $types );
		p2p_register_connection_type( array(
			'name' => 'fave',
			'from' => $types,
			'to' => 'user',
			'admin_box' => false
		) );
	}
	
	/**
	 * Alters query to pull faved posts of a user
	 *
	 * @since    1.0.0
	 *
	 * @param    object    $query    Instance of WP_Query
	 */
	function parse_query( $query ) {
		if(  $user_id = $query->get( 'fave_user' ) ) {
			$user = new WP_User( $user_id );
			
			if ( $user->exists() ) {
				$query->set( 'connected_type', 'fave' );
				$query->set( 'connected_items', $user );
				$query->set( 'suppress_filters', false );
				$query->set( 'nopaging', true );
			}
		}
	}
	
	/**
	 * Alters query to pull users of a faved post
	 *
	 * @since    1.0.0
	 *
	 * @param    object    $query    Instance of WP_User_Query
	 */
	function pre_user_query( $query ) {
		if ( $post_id = $query->get( 'fave_post' ) ) {
			$post = get_post( $post_id );
			$query->set( 'connected_type', 'fave' );
			$query->set( 'connected_items', $post );
			
		}
	}
	
	/**
	 * Adds 'fave_user' to query vars
	 *
	 * @since    1.0.0
	 *
	 * @param    array    $qv    Query vars
	 */
	function query_vars( $qv ) {
		$qv[] = 'fave_user';
		return $qv;
	}
	
	
	/**
	 * Setup for settings page
	 *
	 * @since    1.0.0
	 */
	function admin_init() {
		register_setting('fave_it_options', 'fave_post_types');	
		add_settings_section('fave_it_main', 'Fave-It Settings', array($this,'render_section'), $this->plugin_slug);
		add_settings_field('fave_it_types', 'Post types', array($this, 'render_settings'), $this->plugin_slug, 'fave_it_main' );

	}
	
	/**
	 * Render section description
	 *
	 * @since    1.0.0
	 */
	function render_section() {
		echo '<p>Enable post type faving</p>';
	}
	
	/**
	 * Render settings
	 *
	 * @since    1.0.0
	 */
	function render_settings() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected = get_option( 'fave_post_types' );
		
		foreach( $post_types as $post_type ) {
			printf( 
				'<label><input type="checkbox" value="%s" name="%s" %s />  %s </label> <br />', $post_type->name, 
				'fave_post_types[]', 
				checked( in_array( $post_type->name, $selected ), true, false ), 
				$post_type->labels->name 
			);		
		}
	}
	
	/**
	 * Creates a fave connection
	 *
	 * @since    1.0.0
	 *
	 * @param    int|WP_Post    $post_id    Post id or post object
	 * @param    int|WP_User    $user_id    User id or user object
	 *
	 * @return   boolean   Returns connection id or false if no connection made
	 */
	static function fave_post( $post_id = NULL, $user_id = NULL ) {
		if( $post_id  || function_exists( 'p2p_type' )){	
			
			//If not set, set $user_id to current user
			if( ! $user_id )
				$user_id = get_current_user_id();
			
			//Check if connection already exists
			if( self::has_fave( $post_id, $user_id ) )
				return true;
			
			//If connection  occurs, return true and set off action
			if( !is_wp_error( p2p_type( 'fave' )->connect( $post_id, $user_id ) ) ) {
				do_action( 'fave_post', $post_id, $user_id );
				return true;	
			}
		}
		
		//If everything else fails
		return false;
	}
	
	/**
	 * Removes a fave connection
	 *
	 * @since    1.0.0
	 *
	 * @param    int|WP_Post    $post_id    Post id or post object
	 * @param    int|WP_User    $user_id    User id or user object
	 *
	 * @return   boolean    Returns if unfave successful
	 */
	static function unfave_post( $post_id = 0, $user_id = 0 ) {
		if( $post_id  || function_exists( 'p2p_type' )){				
			//If not set, set $user_id to current user
			if( ! $user_id )
				$user_id = get_current_user_id();
			
			//Unfave and set off action
			if( p2p_type( 'fave' )->disconnect( $post_id, $user_id ) > 0){
				do_action( 'unfave_post', $post_id, $user_id );
				return true;
			}		
		}
		
		//If all esle fails
		return false;	
	}	
	
	/**
	 * Checks for connection
	 *
	 * @since    1.0.0
	 *
	 * @param    int|WP_Post    $post_id    Post id or post object
	 * @param    int|WP_User    $user_id    User id or user object
	 *
	 * @return   boolean   
	 */
	static function has_fave( $post_id = 0, $user_id = 0 ) {
		//Return false is p2p connections not available
		if( !function_exists( 'p2p_type' ))
			return false;
			
		if( empty( $post_id ) )
			$post_id = get_queried_object_id();
			
		if( empty( $user_id ) )
			$user_id = get_current_user_id();
		
		return (boolean) p2p_type( 'fave' )->get_p2p_id( $post_id, $user_id );
	}
	
	/**
	 * Creates a fave connection via ajax
	 *
	 * @since    1.0.0
	 */
	function ajax_fave() {
		//Checks
		check_ajax_referer( 'fave_post' );
		$post_id = $_REQUEST['post_id'] ? (int) $_REQUEST['post_id'] : 0;
		$user_id = $_REQUEST['user_id'] ? (int) $_REQUEST['user_id'] : 0;
		
		if( self::fave_post( $post_id, $user_id ) )
			wp_send_json_success();
		else
			wp_send_json_error();
	}
	
	/**
	 * Removes a fave connection via ajax
	 *
	 * @since    1.0.0
	 */
	function ajax_unfave() {
		//Checks
		check_ajax_referer( 'fave_post' );
		$post_id = $_REQUEST['post_id'] ? (int) $_REQUEST['post_id'] : 0;
		$user_id = $_REQUEST['user_id'] ? (int) $_REQUEST['user_id'] : 0;
		
		if( self::unfave_post( $post_id, $user_id ) > 0)
			wp_send_json_success();
		else 
			wp_send_json_error();
	}
}