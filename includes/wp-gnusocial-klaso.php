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
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		
		// Publikigi enskribon en nodon de GNU social ĉe publikiĝo de WordPress A
		add_action( 'publish_post', array( $this, 'gs_publikigo' ) );
		
		add_action('pre_get_comments', array( $this, 'load_comments_wpgs_template' ));
		
        add_filter( 'comment_form_default_fields', array( $this, 'wpgs_comment_form_fields' ) );
        
        add_filter( 'comment_form_defaults', array( $this, 'wpgs_comment_form' ) );
        
        add_action('comment_form', array( $this, 'wpgs_comment_button' ));
		
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
		
		wp_enqueue_style( 'wpgs-stiloj', plugin_dir_url( __FILE__ ) . 'assets/styles.css' );
	}
	
    public function gs_publikigo() {
    
        $post = get_post($ID);
        
        $title = $post->post_title;
        $priskribo = $post->post_excerpt;        
        $permalink = get_permalink( $ID );
        
        //$kategorioj = get_the_category($ID);
        
        //foreach($kategorioj as $kategorio) {
        //    $kategoricheno .= '#' . $kategorio->cat_name . ' '; 
        //}
        
        $kategoricheno = '';
        
        $gs_konektilo = new GsKonektilo(get_option( '_wpgs_apiurl'), get_option( '_wpgs_salutnomo'), get_option( '_wpgs_pasvorto'));
        
        $respondo = $gs_konektilo->afishi($title, $permalink, $priskribo, $kategoricheno);
        
        $status = new SimpleXMLElement($respondo);
        
        add_post_meta( $post->ID, 'wpgs_conversation_id', (string)($status->id), true);      
        
    }
    
    public function load_comments_wpgs_template($comment_template){
        
        if( !(get_post_meta( get_the_ID(), 'wpgs_conversation_id', true ) == '') ) {
        
            $konversacio_id = get_post_meta( get_the_ID(), 'wpgs_conversation_id', true );

            $nodo_url = parse_url(get_option( '_wpgs_apiurl'));
            $nodo_url = $nodo_url['host'];

            $atom_fluo_url = 'http://' . $nodo_url . '/api/statusnet/conversation/' .  $konversacio_id . '.atom';

            $atom_legilo = new AtomLegilo($atom_fluo_url);
            
            $komentoj = $atom_legilo->legi(get_the_ID());
            
            foreach ($komentoj as $komento) {
                
                $datumoj = array(
                    'comment_post_ID' => get_the_ID(),
                    'comment_author' => $komento->auhtoro,
                    'comment_author_email' => $komento->auhtoro_url,
                    'comment_author_url' => $komento->auhtoro_url,
                    'comment_content' => $komento->enhavo,
                    'comment_type' => '',
                    'comment_parent' => 0,
                    'comment_author_IP' => '',
                    'comment_agent' => 'GNU social',
                    'comment_date' => $komento->publikig_dato->format('Y-m-d H:i:s'),
                    'comment_approved' => 1,
                );

                wp_insert_comment($datumoj);
            }
            
            $atom_legilo->ghisdatigi_daton(get_the_ID());          
            
        }
    }
    
    public function wpgs_comment_form_fields( $fields ) {
            $commenter = wp_get_current_commenter();
            $req = get_option( 'require_name_email' );
            $aria_req = ( $req ? " aria-required='true'" : '' );
            $html5 = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;
            $fields = array(
            'author' => '',
            'email' => '',
            'url' => '',
            );
            return $fields;
    }
    
    function wpgs_comment_form( $args ) {
        $args['comment_field'] = '';
        $args['comment_notes_after'] = '';
        $args['logged_in_as'] = '';
        return $args;
    }
    
    function wpgs_comment_button() {
        global $post;
        $konversacio_id = get_post_meta( $post->ID, 'wpgs_conversation_id', true );
        $nodo_url = parse_url(get_option( '_wpgs_apiurl'));
        $nodo_url = $nodo_url['host'];
        echo '<p>Komentu tiun ĉi afiŝon ĉe <a href="http://' . $nodo_url . '/conversation/' . $konversacio_id . '">GNU social ene de tiu ĉi konversacio</a></p>';
    }    
    
}
