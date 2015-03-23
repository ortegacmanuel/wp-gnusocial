<?php
/**
 * Define the main plugin class
 * 
 * @since 0.2.6
 * 
 * @package Wp_Gnusocial
 */

// Don't allow this file to be accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * The main class.
 * 
 * @since 0.1.0
 */
final class Wp_Gnusocial {
	
	/**
	 * The plugin version.
	 * 
	 * @since 0.0.1
	 */
	const VERSION = '0.0.1';
	
	/**
	 * The plugin slug.
	 * 
	 * @since 0.0.1
	 */
	const SLUG = 'wp_gnusocial';
	
	/**
	 * The only instance of this class.
	 * 
	 * @since 0.0.1
	 * @access protected
	 */
	protected static $instance = null;
	
	/**
	 * Get the only instance of this class.
	 * 
	 * @since 0.0.1
	 * 
	 * @return object $instance The only instance of this class.
	 */
	public static function get_instance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	/**
	 * Prevent cloning of this class.
	 *
	 * @since 0.2.6
	 * 
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', self::SLUG ), self::VERSION );
	}

	/**
	 * Prevent unserializing of this class.
	 *
	 * @since 0.2.6
	 * 
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', self::SLUG ), self::VERSION );
	}
	
	/**
	 * Construct the class!
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function __construct() {
		
		/**
		 * Require the necessary files.
		 */
		$this->require_files();
		
		/**
		 * Add the necessary action hooks.
		 */
		$this->add_actions();
	}
	
	/**
	 * Require the necessary files.
	 * 
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function require_files() {
		
		/**
		 * The helper functions.
		 */
		require( plugin_dir_path( __FILE__ ) . 'functions.php' );
		require( plugin_dir_path( __FILE__ ) . 'gsfluo/gsfluo.php' );
	}
	
	/**
	 * Add the necessary action hooks.
	 * 
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	private function add_actions() {
		
		// Load the text domain for i18n.
		add_action( 'init', array( $this, 'load_textdomain' ) );
		
		// Publikigi enskribon en nodon de GNU social ĉe publikiĝo de WordPress A
		add_action( 'publish_post', array( $this, 'gs_publikigo' ) );
		
		add_filter('comments_template', array( $this, 'load_comments_wpgs_template' ));
		
	}
	
	/**
	 * Load the text domain.
	 *
	 * Based on the bbPress implementation.
	 *
	 * @since 0.1.0
	 * 
	 * @return The textdomain or false on failure.
	 */
	public function load_textdomain() {
		
		$locale = get_locale();
		$locale = apply_filters( 'plugin_locale',  $locale, 'wp-gnusocial' );
		$mofile = sprintf( 'wp-gnusocial-%s.mo', $locale );

		$mofile_local  = plugin_dir_path( dirname( __FILE__ ) ) . 'languages/' . $mofile;
		$mofile_global = WP_LANG_DIR . '/wp-gnusocial/' . $mofile;

		if ( file_exists( $mofile_local ) )
			return load_textdomain( 'wp-gnusocial', $mofile_local );
			
		if ( file_exists( $mofile_global ) )
			return load_textdomain( 'wp-gnusocial', $mofile_global );
		
		load_plugin_textdomain( 'wp-gnusocial' );
		
		return false;
	}
	
	/**
	 * Enqueue the styles.
	 *
	 * @since 0.1.0
	 * 
	 * @return void
	 */
	public function enqueue_styles() {
		
		wp_enqueue_style( 'av-styles', plugin_dir_url( __FILE__ ) . 'assets/styles.css' );
	}
	
    public function gs_publikigo() {
    
        $post = get_post($ID);
        
        $title = $post->post_title;
        $priskribo = $post->post_excerpt;        
        $permalink = get_permalink( $ID );
        
        $kategorioj = get_the_category($ID);
        
        foreach($kategorioj as $kategorio) { 
            $kategoricheno .= '#' . $kategorio->cat_name . ' '; 
        }
        
        $gs_konektilo = new GsKonektilo(get_option( '_wpgs_apiurl'), get_option( '_wpgs_salutnomo'), get_option( '_wpgs_pasvorto'));
        
        $respondo = $gs_konektilo->afishi($title, $permalink, $priskribo, $kategoricheno);
        
        $status = new SimpleXMLElement($respondo);
        
        add_post_meta( $post->ID, 'wpgs_conversation_id', (string)($status->id), true);      
        
    }
    
    public function load_comments_wpgs_template($comment_template){
        
        if( !(get_post_meta( get_the_ID(), 'wpgs_conversation_id', true ) == '') ) {
            return dirname(__FILE__) . '/komentoj.php';
        }
    }
}
