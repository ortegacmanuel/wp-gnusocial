<?php

add_action('widgets_init', create_function('', 'return register_widget("wpgs_konversacioj_widget");'));
class wpgs_konversacioj_widget extends WP_Widget {

	
	/*-----------------------------------------------------------------------------------*/
	/*	Widget actual processes
	/*-----------------------------------------------------------------------------------*/
	
	public function __construct() {
		parent::__construct(
	 		'wpgs_konversacioj_widget',
			__('Federitaj konversacioj', 'wp_gnusocial'),
			array( 'description' => __( 'Listigo de la afiŝoj publikigitaj en via nodo de GNU social', 'wp_gnusocial' ), )
		);
	}

	
	/*-----------------------------------------------------------------------------------*/
	/*	Outputs the options form on admin
	/*-----------------------------------------------------------------------------------*/
	
	public function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
		}
		else {
			$title = __( 'Federitaj Konversacioj', 'wp_gnusocial' );
		}

		if ( $instance ) {
			$nr_posts = esc_attr( $instance[ 'nr_posts' ] );
		}
		else {
			$nr_posts = __( '10', 'wp_gnusocial' );
		}

		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"> <?php _e('Titolo:', 'wp_gnusocial'); ?> </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('nr_posts'); ?>"> <?php _e('Kiom da afiŝoj listigi:', 'wp_gnusocial'); ?> </label>
				<input class="widefat" id="<?php echo $this->get_field_id('nr_posts'); ?>" name="<?php echo $this->get_field_name('nr_posts'); ?>" type="text" value="<?php echo $nr_posts; ?>" />
				<p style="font-size: 10px; color: #999; margin: -10px 0 0 0px; padding: 0px;"> <?php _e('Nombro da afiŝoj listigotaj', 'wp_gnusocial'); ?></p>
			</p>
		<?php 
	}
	

	/*-----------------------------------------------------------------------------------*/
	/*	Processes widget options to be saved
	/*-----------------------------------------------------------------------------------*/
	
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['nr_posts'] = sanitize_text_field($new_instance['nr_posts']);
		return $instance;
	}

	/*-----------------------------------------------------------------------------------*/
	/*	Outputs the content of the widget
	/*-----------------------------------------------------------------------------------*/

	public function widget( $args, $instance ) {
		global $post;
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$nr_posts = apply_filters( 'widget', $instance['nr_posts'] );
		?>
		
		<?php
                        
        $nodo_url = parse_url(get_option( '_wpgs_apiurl'));
        $nodo_domajno = $nodo_url['host'];
        $nodo_protokolo = $nodo_url['scheme'];

        $json_url = $nodo_protokolo . '://' . $nodo_domajno . '/api/hedero/posts.json';

        $json = file_get_contents($json_url);

        $konversacioj = json_decode($json);

		?>

			<?php if( $konversacioj ) : ?>
			
				<aside class="widget Akt-eventoj">	
					<div class="widget-title">
						<h3><?php echo $title ?></h3>
						<div class="vakigi"></div>
					</div>
					
					<div class="ftrajo-korpo">

						<?php foreach ($konversacioj as $konversacio) { ?>
							<article class="ftrajho-elemento">
									<figure>
										<a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>">
        				<?php if (isset($konversacio->attachments[0]->thumb_url)) { ?>
                                                <img src="<?php echo $konversacio->attachments[0]->thumb_url ?>" />
        				<?php } ?>
										</a>
									</figure>
								<div class="entry-meta-widget">
									<?php echo $konversacio->statusnet_html ?>
								</div>
							</article>
                            <hr/>
						<?php } ?>
					</div>
                    <div class="vidichiujn fdekstre"><a href="<?php echo $nodo_protokolo . '://' . $nodo_domajno . '/hedero/posts' ?>" class="radiuso" title="<?php _e('Vidi ĉiujn', 'wplook'); ?>"><?php _e('vidi ĉiujn', 'wp_gnusocial'); ?></a></div>
				</aside>
			    <div class="ftrajo-vakigo"></div>
			<?php endif; ?>
		<?php
	}
}
?>
