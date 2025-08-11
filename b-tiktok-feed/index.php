<?php
/**
 * Plugin Name: Feeds For TikTok
 * Description: Embed Tiktok feed in your website
 * Version: 1.0.22
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: tiktok
 */

// ABS PATH
if (!defined('ABSPATH')) {exit;}

register_activation_hook(__FILE__, function () {
	if ( is_plugin_active('my-social-feeds/my-social-feeds.php')) {
		deactivate_plugins('my-social-feeds/my-social-feeds.php');
	}  
});

if (!function_exists('ttp_init')) {
    function ttp_init()
    {
        global $ttp_bs;
        require_once plugin_dir_path(__FILE__) . 'bplugins_sdk/init.php';
        $ttp_bs = new BPlugins_SDK(__FILE__);
    }
    ttp_init();
} else {
    $ttp_bs->uninstall_plugin(__FILE__);
}

class TTP_Tiktok {

    private static $instance;

    private function __construct() {
        $this->defined_constants();
        $this->load_classes();

        add_action('enqueue_block_assets', [$this, 'enqueueTiktokAssets']);
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('init', [$this, 'onInit']);
        add_action('admin_footer', [$this, 'load_tiktok_script'], 10);
        add_action('wp_footer', [$this, 'load_tiktok_script'], 10);
    }

    public static function get_instance() {

        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new self();
        return self::$instance;
    }

    public function defined_constants(){
        define( 'TTP_PLUGIN_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.22' );
        define('TTP_DIR', plugin_dir_url(__FILE__));
        define('TTP_ASSETS_DIR', plugin_dir_url(__FILE__) . 'assets/');
    }

    public function load_classes() {
        
        global $ttp_bs;

        require_once plugin_dir_path(__FILE__) . 'includes/api/TiktokAPI.php';
        new TTP_TikTok\TTP_TikTok_Api();

		if($ttp_bs->can_use_premium_feature()){ 
            require_once plugin_dir_path(__FILE__) . 'includes/post-type/custom-post.php';
            new TTP_TikTok\TTP_Custom_Post_Type();
        }
    }

    public function load_tiktok_script()
        {
            ?>
            <script async src="https://www.tiktok.com/embed.js"></script>
            <?php
        }

        public function enqueueTiktokAssets()
        {
            wp_register_style('ttp-fancyApp', TTP_ASSETS_DIR . 'css/fancyapps.min.css');
            wp_register_script('ttp-fancyApp', TTP_ASSETS_DIR . 'js/fancyapps.min.js', [], TTP_PLUGIN_VERSION);

            wp_localize_script('ttp-fancyApp', 'ttpData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'tiktokAuthorized' => false !== get_transient('ttp_tiktok_authorized_data'),
                'nonce' => wp_create_nonce('ttp_fetch_data_nonce'),
            ]);

            wp_localize_script('ttp-tiktok-player-editor-script', 'ttpPatters', [
                'patternsImagePath' => TTP_DIR . 'assets/images/patterns/',
            ]);
        }

        public function adminEnqueueScripts($hook)
        {
            if ('edit.php' === $hook) {
                wp_enqueue_style('ttpAdmin', TTP_ASSETS_DIR . 'css/admin.css', [], TTP_PLUGIN_VERSION);
                wp_enqueue_script('ttpAdmin', TTP_ASSETS_DIR . 'js/admin.js', ['wp-i18n'], TTP_PLUGIN_VERSION, true);
            }
        }
        
        public function onInit(){
            register_block_type( __DIR__ . '/build' );
        }
}

TTP_Tiktok::get_instance();