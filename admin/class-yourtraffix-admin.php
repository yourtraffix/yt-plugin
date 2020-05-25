<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://yourtraffix.com
 * @since      1.0.0
 *
 * @package    Yourtraffix
 * @subpackage Yourtraffix/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Yourtraffix
 * @subpackage Yourtraffix/admin
 * @author     Yourtraffix <gilad@yourtraffix.com>
 */
class Yourtraffix_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// require_once('BFIGitHubPluginUploader.php');
		// require_once(plugin_dir_path(__FILE__) . 'BFIGitHubPluginUpdater.php');
		// require_once(plugin_dir_path(__FILE__) . 'updates.php');

		if (is_admin()) {
			// new BFIGitHubPluginUpdater(__FILE__, 'yourtraffix', "yt-plugin");
			// new ACF_Updates();
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Yourtraffix_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Yourtraffix_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/yourtraffix-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Yourtraffix_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Yourtraffix_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/yourtraffix-admin.js', array('jquery'), $this->version, false);
	}

	public function custom_query_vars($vars)
	{

		$vars[] = 'api_action';
		return $vars;
	}

	public function theme_functionality_urls()
	{
		add_rewrite_rule(
			'^yt/verify/?',
			'index.php?api_action=yt_verify_token',
			'top'
		);

		flush_rewrite_rules();
	}

	public function wp_loaded()
	{
		if (isset($_GET['yt_verify']) && $_GET['yt_verify'] === 'token') {
			if (strpos($_SERVER['HTTP_REFERER'], 'yourtraffix.com') === true) {
				die(json_encode(['token' => get_option('yt_token')]));
			} else {
				die(json_encode(['token' => null]));
			}
		}
	}

	public function custom_requests($wp)
	{

		// $valid_actions = array('yt_verify_token');
		// if (
		// 	!empty($wp->query_vars['api_action']) &&
		// 	in_array($wp->query_vars['api_action'], $valid_actions)
		// ) {
		// 	die(json_encode(['token' => '11bf5b37-e0b8-42e0-8dcf-dc8c4aefc000']));
		// }
	}

	public function get_posts()
	{
		$args = array(
			'post_type' => 'post',
			'post_status' => array('publish'),
			'por_per_page' => 40,
			'orderby'  => 'date',
			'order'   => 'DESC',
		);
		$query = new WP_Query($args);
		$response = new stdClass();
		$response->posts = $query->posts;
		foreach ($response->posts  as $post) {
			$post->featured_image = wp_get_attachment_url(get_post_thumbnail_id($post->ID), 'thumbnail');
		}

		wp_die(wp_json_encode($response));
	}

	public function get_user_data()
	{
		$response = new stdClass();
		$response->email = get_bloginfo('admin_email');
		$response->url = get_bloginfo('url');
		$response->language = get_bloginfo('language');
		$response->yt_token =  $this->set_token();
		$response->wp_user_info =  wp_get_current_user();
		wp_die(wp_json_encode($response));
	}

	private function set_token()
	{
		$yt_token = get_option('yt_token');
		if ($yt_token) {
			return $yt_token;
		}

		$yt_token = uniqid();
		add_option('yt_token', $yt_token);
		return $yt_token;
	}

	public function admin_menu()
	{
		add_menu_page(
			__('YourTraffix', 'my-textdomain'),
			__('YourTraffix', 'my-textdomain'),
			'manage_options',
			'YourTraffix',
			array($this, 'render_admin_page'),
			'dashicons-schedule',
			3
		);
	}

	public function custom_script_load()
	{
		wp_enqueue_script('yourtraffix', plugin_dir_url(__FILE__) . '/../build/embedded/yt-script.js', array('jquery'));
		
		wp_register_style( 'yourtraffix', plugin_dir_url(__FILE__) . '/../build/embedded/yt-script.css' );
    	wp_enqueue_style( 'yourtraffix' );
	}

	public function render_admin_page()
	{
?>
		<style>
			.yourtraffix-iframe {
				width: calc(100vw - 160px);
				height: calc(100vh - 32px);
				position: fixed;
				top: 32px;
				<?php if (is_rtl()) : ?>right<?php else : ?>left<?php endif; ?>: 160px;
			}
		</style>
		<iframe src="<?php echo PLUGIN_DIR; ?>/admin/build/index.php/?<?php echo $_SERVER['QUERY_STRING']; ?>" class="yourtraffix-iframe"></iframe>
<?php
	}
}
