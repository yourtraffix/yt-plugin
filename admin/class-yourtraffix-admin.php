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
		wp_enqueue_script('my-custom-script', plugin_dir_url(__FILE__) . '/../build/embedded/yt-script.js', array('jquery'));
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
		<iframe src="/wp-content/plugins/yt-plugin-0.0.13/admin/build/index.html?<?php echo $_SERVER['QUERY_STRING']; ?>" class="yourtraffix-iframe"></iframe>
<?php
	}

	public function misha_plugin_info($res, $action, $args)
	{
		die('asdasd misha_plugin_info');
		// do nothing if this is not about getting plugin information
		if ('plugin_information' !== $action) {
			return false;
		}

		$plugin_slug = 'yourtraffix'; // we are going to use it in many places in this function

		// do nothing if it is not our plugin
		if ($plugin_slug !== $args->slug) {
			return false;
		}

		// trying to get from cache first
		if (false == $remote = get_transient('misha_update_' . $plugin_slug)) {

			// info.json is the file with the actual plugin information on your server
			$remote = wp_remote_get(
				'https://api.yourtraffix.com/wordpress-plugin-update',
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json'
					)
				)
			);

			if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
				set_transient('misha_update_' . $plugin_slug, $remote, 43200); // 12 hours cache
			}
		}

		if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {

			$remote = json_decode($remote['body']);
			$res = new stdClass();

			$res->name = $remote->name;
			$res->slug = $plugin_slug;
			$res->version = $remote->version;
			$res->tested = $remote->tested;
			$res->requires = $remote->requires;
			$res->author = '<a href="https://rudrastyh.com">Misha Rudrastyh</a>';
			$res->author_profile = 'https://profiles.wordpress.org/rudrastyh';
			$res->download_link = $remote->download_url;
			$res->trunk = $remote->download_url;
			$res->requires_php = '5.3';
			$res->last_updated = $remote->last_updated;
			$res->sections = array(
				'description' => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog' => $remote->sections->changelog
				// you can add your custom sections (tabs) here
			);

			// in case you want the screenshots tab, use the following HTML format for its content:
			// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
			if (!empty($remote->sections->screenshots)) {
				$res->sections['screenshots'] = $remote->sections->screenshots;
			}

			$res->banners = array(
				'low' => 'https://YOUR_WEBSITE/banner-772x250.jpg',
				'high' => 'https://YOUR_WEBSITE/banner-1544x500.jpg'
			);
			return $res;
		}

		return false;
	}

	public function misha_push_update($transient)
	{
		if (empty($transient->checked)) {
			return $transient;
		}

		// trying to get from cache first, to disable cache comment 10,20,21,22,24
		if (false == $remote = get_transient('misha_upgrade_yourtraffix')) {

			// info.json is the file with the actual plugin information on your server
			$remote = wp_remote_get(
				'https://api.yourtraffix.com/wordpress-plugin-update',
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json'
					)
				)
			);

			if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
				set_transient('misha_upgrade_yourtraffix', $remote, 43200); // 12 hours cache
			}
		}

		if ($remote) {

			$remote = json_decode($remote['body']);

			// your installed plugin version should be on the line below! You can obtain it dynamically of course 
			if ($remote && version_compare('1.0', $remote->version, '<') && version_compare($remote->requires, get_bloginfo('version'), '<')) {
				$res = new stdClass();
				$res->slug = 'yourtraffix';
				$res->plugin = 'yourtraffix/yourtraffix.php'; // it could be just yourtraffix.php if your plugin doesn't have its own directory
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;
				$transient->response[$res->plugin] = $res;
				$transient->checked[$res->plugin] = $remote->version;
			}
		}
		return $transient;
	}

	public function misha_after_update($upgrader_object, $options)
	{
		die('asdasd misha_after_update');
		if ($options['action'] == 'update' && $options['type'] === 'plugin') {
			// just clean the cache when new plugin version is installed
			delete_transient('misha_upgrade_yourtraffix');
		}
	}
}
