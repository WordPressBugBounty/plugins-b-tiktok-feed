<?php
/**
 * Plugin Name: Feeds For TikTok
 * Description: Embed Tiktok feed in your website
 * Version: 1.0.25
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: tiktok
 */

// ABS PATH
if (!defined('ABSPATH')) {exit;}

if ( function_exists( 'btf_fs' ) ) {
    btf_fs()->set_basename( false, __FILE__ );
} else {

    define( 'TTP_PLUGIN_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.25' );
    define('TTP_DIR', plugin_dir_url(__FILE__));
    define('TTP_ASSETS_DIR', plugin_dir_url(__FILE__) . 'assets/');
    define('TTP_DIR_PATH', plugin_dir_path(__FILE__));
     

    if ( !function_exists( 'btf_fs' ) ) {
        // Create a helper function for easy SDK access.
        function btf_fs() {
            global $btf_fs;
            if ( !isset( $btf_fs ) ) {
                // Include Freemius SDK.
                // if ( TTP_IS_PRO ) {
                //     require_once dirname( __FILE__ ) . '/freemius/start.php';
                // } else {
                    require_once dirname( __FILE__ ) . '/freemius-lite/start.php';
                // }
                $ttpConfig =  array(
                    'id'                  => '21810',
                    'slug'                => 'b-tiktok-feed',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_d0c440e4c0ef287e32bd7654765ed',
                    'is_premium'          => false,
                    'premium_suffix'      => 'Pro',
                    // If your plugin is a serviceware, set this option to false.
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => array(
                        'days'               => 7,
                        'is_require_payment' => false,
                    ),
                    // Automatically removed in the free version. If you're not using the
                    // auto-generated free version, delete this line before uploading to wp.org.
                    'menu'                => array(
                        'slug'           => 'b-tiktok-feed',
                        'first-path'     =>  'tools.php?page=b-tiktok-feed#/pricing',
                        'support'        => false,
                    ),
                );
                $btf_fs = fs_lite_dynamic_init( $ttpConfig );
            }
            return $btf_fs;
        }

        // // Init Freemius.
        btf_fs();
        // Signal that SDK was initiated.
        do_action( 'btf_fs_loaded' );
    }
    class TTP_Tiktok {

        private static $instance;

        private function __construct() {
             
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
         
        public function load_classes() {
            
            global $ttp_bs;

            require_once plugin_dir_path(__FILE__) . 'includes/api/TiktokAPI.php';
            new TTP_TikTok\TTP_TikTok_Api();

            require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';

            // if($ttp_bs->can_use_premium_feature()){ 
            //     require_once plugin_dir_path(__FILE__) . 'includes/post-type/custom-post.php';
            //     new TTP_TikTok\TTP_Custom_Post_Type();
            // }
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
                    'dataGet' => wp_create_nonce('ttp_data_get_nonce'),
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

}