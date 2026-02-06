<?php
if (!defined('ABSPATH')) {exit;}
if(!class_exists('tfb_Admin_Menu')) {
    class tfb_Admin_Menu{

        public function __construct(){
            add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
            add_action('admin_menu', [$this, 'adminMenu']);
        }

        public function adminMenu(){

            add_submenu_page(
                'tools.php',
                __('B TikTok Feed', 'tiktok'),
                __('B TikTok Feed', 'tiktok'),
                'manage_options',
                'b-tiktok-feed',
                [$this, 'helpPage'],
            );
        }

        function adminEnqueueScripts( $hook ) {
            if( strpos( $hook, 'b-tiktok-feed' ) ){
                wp_enqueue_style( 'ttp-admin-dashboard', TTP_DIR . 'build/admin-dashboard.css', [], TTP_PLUGIN_VERSION );
                wp_enqueue_script( 'ttp-admin-dashboard', TTP_DIR . 'build/admin-dashboard.js', [ 'react', 'react-dom' ], TTP_PLUGIN_VERSION, true );
                wp_set_script_translations( 'ttp-admin-dashboard', 'tiktok', TTP_DIR_PATH . 'languages' );
            }
	    }

        public function helpPage()
        {?>
            <div
                id='ttpDashboard'
                data-info='<?php echo esc_attr( wp_json_encode( [
                    'version' => TTP_PLUGIN_VERSION,
                    'isPremium' => false,
                    'hasPro' => false
                ] ) ); ?>'
            >
            </div>
        <?php }  
    }
    new tfb_Admin_Menu();
}