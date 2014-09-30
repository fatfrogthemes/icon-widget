<?php
/**
 * Plugin Name: Fat Frog Icon Widget
 * Plugin: URI: https://www.fatfrogthemes.co.uk
 * Description: An example of the WP Settings Framework in action.
 * Version: 1.0.0
 * Author: Alex Hammond
 * License: GPL3
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds Fat Frog Icon widget.
 */
class Fat_Frog_Icon_Widget extends WP_Widget {

	public $title = '';
	public $image = '';
	public $description = '';
	private $plugin_path;
	private $plugin_url;
	private $l10n;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->l10n        = 'fatfrog-icon-widget';

		parent::__construct(
			'fatfrog_icon_widget', // Base ID
			__('Fat Frog Icon Widget', 'fatfrog'), // Name
			array( 'description' => __( 'Displays an icon and description', 'fatfrog' ), ) // Args
		);

		add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
		add_action( 'init', array( $this, 'updater' ) );

	}

	/**
	 * Upload the Javascripts for the media uploader
	 */
	public function upload_scripts()
	{
		wp_enqueue_media();
		wp_enqueue_script('fatfrog-icon-widget-upload-media', $this->plugin_url . 'js/upload-media.js', array( 'jquery' ), '20140932', true );
	}

	public function updater() {
		include_once 'updater.php';

		define( 'WP_GITHUB_FORCE_UPDATE', true );

		if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

			$config = array(
				'slug' => plugin_basename( __FILE__ ),
				'proper_folder_name' => 'fat-frog-icon-widget',
				'api_url' => 'https://api.github.com/repos/jkudish/WordPress-GitHub-Plugin-Updater',
				'raw_url' => 'https://raw.github.com/jkudish/WordPress-GitHub-Plugin-Updater/master',
				'github_url' => 'https://github.com/jkudish/WordPress-GitHub-Plugin-Updater',
				'zip_url' => 'https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/archive/master.zip',
				'sslverify' => true,
				'requires' => '3.0',
				'tested' => '3.3',
				'readme' => 'README.md',
				'access_token' => '',
			);

			new WP_GitHub_Updater( $config );

		}

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$this->image = ( isset( $instance['image'] ) ) ? apply_filters( 'fatfrog_icon_image', $instance['image'] ) : '';
		$this->title = ( isset( $instance['title'] ) ) ? apply_filters( 'fatfrog_icon_title', $instance['title'] ) : '';
		$this->description = ( isset( $instance['description'] ) ) ? apply_filters( 'fatfrog_icon_description', $instance['description'] ) : '';

		$html = $args['before_widget'];
		$html .= '<div class="fatfrog-icon-widget-container">';
		$html .= '<figure><img src="' . $this->image . '" width="50" height="50" /></figure>';
		if ( ! empty( $this->title ) )
			$html .= $args['before_title'] . $this->title . $args['after_title'];
		$html .= '<div class="fatfrog-icon-widget-description">' . $this->description . '</div>';
		$html .= '</div>';
		$html .= $args['after_widget'];

		echo $html;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$image = ( isset( $instance['image'] ) ) ? $instance['image'] : '';
		$description = ( isset( $instance['description'] ) ) ? $instance['description'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Image:' ); ?> <span style="font-size: 0.8em; font-style: italic;"><?php _e( '100px by 100px for best results', $this->l10n ); ?></span></label>
			<input name="<?php echo $this->get_field_name( 'image' ); ?>" id="<?php echo $this->get_field_id( 'image' ); ?>" class="test-input widefat" type="text" size="36"  value="<?php echo esc_url( $image ); ?>" />
			<input class="fatfrog-icon-widget-upload-image-button button button-primary alignright" type="button" value="Upload Image" data-uploader_title="Fat Frog Icon Widget Image" />
		</p>

		<p style="clear: both">
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', $this->l10n ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Description:', $this->l10n ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?> fatfrog-widget-icon-editor" rows="10" name="<?php echo $this->get_field_name( 'description' ); ?>"><?php echo wp_kses_post( $description ); ?></textarea>
			<span class="description" style="font-style: italic;"><?php _e( 'Use basic html to style and add links', $this->l10n ); ?></span>
		</p>

	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['image'] = ( ! empty( $new_instance['image'] ) ) ? strip_tags( $new_instance['image'] ) : '';
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['description'] = ( ! empty( $new_instance['description'] ) ) ? wp_kses_post( $new_instance['description'] ) : '';

		return $instance;
	}

} // class Fat_Frog_Icon_Widget

/**
 * Register the Fat Frog Icon Widget
 */
add_action( 'widgets_init', create_function( '', 'register_widget("Fat_Frog_Icon_Widget");' ) );