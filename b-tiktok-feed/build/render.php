<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

    $videos = get_transient('ttp_tiktok_videos');
    $user_info = get_transient('ttp_tiktok_user_info');

    $id = wp_unique_id( 'ttpTiktok-' );
    ?>
    <div <?php echo wp_kses_post( get_block_wrapper_attributes() ); ?> 
        data-data="<?php echo esc_attr(wp_json_encode(compact('videos', 'user_info'))) ?>" 
        id="<?php echo esc_attr( $id ); ?>"
        data-attributes='<?php echo esc_attr(wp_json_encode($attributes)); ?>'>
    </div>

        