<?php

use GuzzleHttp\RequestOptions;
use Spatie\Crawler\CrawlProfiles\CrawlAllUrls;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://automattic.com
 * @since      1.0.0
 *
 * @package    Link_Checker
 * @subpackage Link_Checker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Link_Checker
 * @subpackage Link_Checker/admin
 */
class Link_Checker_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->add_action_rest_api();
	}

	private function add_action_rest_api() {
		// Custom endpoint
		add_action( 'rest_api_init', function () {
			register_rest_route( 'linkchecker/v1','/check',
				array(
						'methods'  => 'GET',
						'callback' => array( $this, 'api_check' ),
					)
				);
			}
		);
	}

	public function add_admin_menu() {
		add_menu_page( 'Link Checker', 'Link Checker', 'manage_options', 'team51-link-checker', array( $this, 'render_admin_page' ), 'dashicons-editor-unlink' );
	}

	function render_admin_page() {
		$html = '';
		$html .= '<h1>Link Checker</h1>';
		$html .= '<button id="linkCheckerStartBtn">Start</button>';
		$html .= '
		<div id="link_checker_vue_app">
			Last check: {{ date }}

		  	<div>
			  	<div v-for="(urlsGroup, key) in results">
				  	<h3>HTTP Code: {{ key === "---" ? "N/A" : key }}</h3>
					<ul class="linkchecker__urls">
						<li v-for="url in urlsGroup">
							{{ url }}
						</li>
					</ul>
				</div>
			</div>
		</div>';

		echo $html;
	}

	function api_check() {
		$this->scan();
		return 'ok';
	}

	private function scan() {
		$base_url = sprintf(
			'%s://%s',
			isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
			$_SERVER['SERVER_NAME']
		);

		if ( ! empty( $_GET['testurl'] ) ) {
			$base_url = $_GET['testurl'];
		}

		$skip_external = true;
		// $timeout       = \WP_CLI\Utils\get_flag_value( $assoc_args, 'timeout', 10 );

		$crawl_profile = $skip_external ? new CrawlInternalUrls( $base_url ) : new CrawlAllUrls();

		$crawl_logger = new CrawlLogger();
		//$crawl_logger->setOutputFile( 'linker.log' );

		$concurrent_connections = 10;
		$timeout                = 10;

		$client_options = array(
			RequestOptions::TIMEOUT         => $timeout,
			RequestOptions::VERIFY          => ! $skip_external,
			RequestOptions::ALLOW_REDIRECTS => array(
				'track_redirects' => true,
			),
		);

		$crawler = Crawler::create( $client_options )
			->setConcurrency( $concurrent_connections )
			->setCrawlObserver( $crawl_logger )
			->setCrawlProfile( $crawl_profile )
			->ignoreRobots();

		$crawler->startCrawling( $base_url );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/link-checker-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'vuejs', plugin_dir_url( __FILE__ ) . 'js/vue.js', array(), 1, false );

		$v = filemtime( plugin_dir_path( __FILE__ ) . 'js/link-checker-admin.js' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/link-checker-admin.js', array( 'jquery' ), $v, false );

	}

}
