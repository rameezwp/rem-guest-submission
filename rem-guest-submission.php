<?php
/**
 * Plugin Name: REM - Guest Submission
 * Plugin URI: https://webcodingplace.com/real-estate-manager-wordpress-plugin/
 * Description: Enables guests to submit listings.
 * Version: 1.0.0
 * Author: WebCodingPlace
 * Author URI: https://webcodingplace.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: real-estate-manager
 * Domain Path: /languages
 */


define('REM_GU_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define('REM_GU_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );

/**
*   Guest User Addon
*/
class RemGuestUserAddon {
    
    function __construct(){
        add_shortcode( 'rem_create_property_guest', array($this, 'create_property') );
        add_action( 'wp_ajax_nopriv_rem_guest_create_pro_ajax', array($this, 'save_property_frontend' ) );
        add_action( 'wp_ajax_rem_guest_create_pro_ajax', array($this, 'save_property_frontend' ) );
    }
    function create_property($attrs){

        extract( shortcode_atts( array(
            'style' => '',
        ), $attrs ) );

        global $rem_ob;
        wp_enqueue_media();
        rem_load_bs_and_fa();
        rem_load_basic_styles();
        $images_limit = rem_get_option('gallery_images_limit', 0);
        wp_enqueue_style( 'rem-admin-css', REM_URL . '/assets/admin/css/admin.css' );
        
        wp_enqueue_script( 'wp-ajax-response' );
        wp_enqueue_script('image-edit');
        wp_enqueue_style('imgareaselect');
        
        wp_enqueue_style( 'rem-easydropdown-css', REM_URL . '/assets/front/lib/easydropdown.css' );
        wp_enqueue_style( 'rem-labelauty-css', REM_URL . '/assets/front/lib/labelauty.css' );
        wp_enqueue_script( 'rem-labelauty', REM_URL . '/assets/front/lib/labelauty.min.js', array('jquery'));
        wp_enqueue_script( 'rem-easy-drop', REM_URL . '/assets/front/lib/jquery.easydropdown.min.js', array('jquery'));
        wp_enqueue_script( 'rem-match-height', REM_URL . '/assets/front/lib/jquery.matchheight-min.js', array('jquery'));
        wp_enqueue_script( 'rem-tooltip', REM_URL . '/assets/front/lib/tooltip.js', array('jquery'));
        wp_enqueue_style( 'rem-select2-css', REM_URL . '/assets/admin/css/select2.min.css' );
        wp_enqueue_script( 'rem-select2-js', REM_URL . '/assets/admin/js/select2.min.js' , array('jquery'));
            
        // If Map is enabled
        if (rem_get_option('single_property_map', 'enable') == 'enable') {

            $def_lat = rem_get_option('default_map_lat', '-33.890542'); 
            $def_long = rem_get_option('default_map_long', '151.274856');
            $zoom_level = rem_get_option('maps_zoom_level', '18');
            $map_type = rem_get_option( 'maps_type', 'roadmap');
            $maps_api = apply_filters( 'rem_maps_api', 'AIzaSyBbpbij9IIXGftKhFLMHOuTpAbFoTU_8ZQ');
            $drag_icon = apply_filters( 'rem_maps_drag_icon', REM_URL.'/assets/images/pin-drag.png' );
            if (rem_get_option('use_map_from', 'leaflet') == 'leaflet') {
                wp_enqueue_style( 'rem-leaflet-css', REM_URL . '/assets/front/leaflet/leaflet.css');
                wp_enqueue_script( 'rem-leaflet-js', REM_URL . '/assets/front/leaflet/leaflet.js', array('jquery'));
                wp_enqueue_style( 'rem-leaflet-geo-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
                wp_enqueue_script( 'rem-leaflet-geo-js', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
            } else {
                if (is_ssl()) {
                    wp_enqueue_script( 'rem-edit-property-map', 'https://maps.googleapis.com/maps/api/js?key='.$maps_api.'&libraries=places');
                } else {
                    wp_enqueue_script( 'rem-edit-property-map', 'http://maps.googleapis.com/maps/api/js?key='.$maps_api.'&libraries=places');
                }
            }
            $localize_vars = array(
                'use_map_from' => rem_get_option('use_map_from', 'leaflet'),
                'def_lat' => $def_lat,
                'def_long' => $def_long,
                'zoom_level' => $zoom_level,
                'map_type' => $map_type,
                'leaflet_styles' => rem_get_leaflet_provider(rem_get_option('leaflet_style')),
                'maps_api' => $maps_api,
                'drag_icon' => $drag_icon,
                'maps_styles' => stripcslashes(rem_get_option('maps_styles')),
                'images_limit' => $images_limit,
                'success_message' => __( 'Successful', 'real-estate-manager' ),
            );
        }

        wp_enqueue_script( 'rem-create-pro', REM_URL . '/assets/front/js/create-property.js', array('jquery', 'jquery-ui-sortable'));
        wp_enqueue_script( 'guest-user-crate-pro', REM_GU_URL . '/js/scripts.js', array('jquery'));

        if (isset($localize_vars)) {
            wp_localize_script( 'rem-create-pro', 'rem_property_vars', $localize_vars );
        } else {
            $localize_vars = array(
                'def_lat' => 'disable',
                'images_limit' => $images_limit,
                'success_message' => __( 'Successful', 'real-estate-manager' ),
            );                
            wp_localize_script( 'rem-create-pro', 'rem_property_vars', $localize_vars );
        }
        ob_start();
            $property_individual_cbs = $rem_ob->get_all_property_features();
            $in_theme = get_stylesheet_directory().'/rem/shortcodes/create-property.php';
            if (file_exists($in_theme)) {
                include $in_theme;
            } else {
                include REM_GU_PATH. '/shortcodes/create-property.php';
            }
        return ob_get_clean();
    }

    function save_property_frontend(){
        
        if (isset($_REQUEST) && $_REQUEST != '') {

            // if editing existing property and user is the agent of that property
                // New property is submitted
            if(rem_get_option('property_submission_mode') == 'approve'){
                $property_id = $this->insert_property_in_db('', $_REQUEST, 'pending');
                do_action( 'rem_new_property_submitted', $property_id );
            } else {
                $property_id = $this->insert_property_in_db($_REQUEST['property_id'], $_REQUEST, 'publish');
            }

            echo apply_filters( 'rem_redirect_after_property_submit', get_permalink( $property_id ), $_REQUEST );
        }
        die();
    }
    function insert_property_in_db($property_id = '', $data,  $status = 'draft'){
        $user = get_user_by( 'email', $data['rem_user_email'] );

        // if user already hase account get the user id form there
        // if not create new user
        if ($user) {
            $user_id = $user->ID;
        }else {
            global $rem_emails_manager;
            $password = $this->generatePassword(6,8);
            $user_id = wp_create_user( $data['rem_user_email'],$password, $data['rem_user_email'] );
            $site_title = get_bloginfo( 'name' );
            $subject = __( 'Register on ', 'rem-ss' ). $site_title;
            $message = (rem_get_option('gu_register_email_msg') != '') ? rem_get_option('gu_register_email_msg') : 'User Name: %user_name% Password : %password%' ;
            $message = str_replace("%user_name%", $data['rem_user_email'], $message);
            $message = str_replace("%password%", $password, $message);
            $rem_emails_manager->send_email($data['rem_user_email'], $subject, $message);
        }
        if ($user_id) {
        
            $property_data = array(
              'post_title'    => wp_strip_all_tags( $data['title'] ),
              'post_content'  => html_entity_decode($data['content']),
              'post_type'   => 'rem_property',
              'post_author' => $user_id,
              'post_status'   => apply_filters( 'rem_property_publish_status', $status, $property_id ),
            );
            
            $property_id = wp_insert_post( $property_data );

            foreach ($data as $key => $meta) {
                if ($key != 'title' || $key != 'content' || $key != 'rem_property_data' || $key != 'tags') {
                    update_post_meta( $property_id, 'rem_'.$key, $meta );
                }

                if ($key == 'tags') {
                    wp_set_post_terms( $property_id, $meta, 'rem_property_tag' );
                }
            }
            
            if (isset($_FILES) && is_array($_FILES)) {
                foreach ($_FILES as $file_key => $media_values) {
                    $property_media = array();
                        $files = $media_values;  
                        foreach ($files['name'] as $key => $value) {            
                            if ($files['name'][$key]) { 
                                $file = array( 
                                    'name' => $files['name'][$key],
                                    'type' => $files['type'][$key], 
                                    'tmp_name' => $files['tmp_name'][$key], 
                                    'error' => $files['error'][$key],
                                    'size' => $files['size'][$key]
                                ); 
                                $_FILES = array ($file_key => $file);
                                foreach ($_FILES as $file => $array) {
                                    $newupload = $this->my_handle_attachment($file,$property_id);
                                    if ($newupload) {
                                        
                                        $property_media[] = $newupload;
                                    }
                                }
                            } 
                        }
                    if (!empty($property_media)) {
                        update_post_meta( $property_id, 'rem_'.$file_key, $property_media );
                    }else {
                        update_post_meta( $property_id, 'rem_'.$file_key, '' );
                    }
                }
            }
            if (!isset($data['property_detail_cbs'])) {
                update_post_meta( $property_id, 'rem_property_detail_cbs', '' );
            }
        }
        return $property_id;
    }

    function my_handle_attachment($file_handler,$post_id,$set_thu=false) {
        // check to make sure its a successful upload
        if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');

        $attach_id = media_handle_upload( $file_handler, $post_id );
        
        return $attach_id;
    }

    function render_property_field($field, $value = ''){
        $default_val = ($value != '') ? $value : $field['default'];
        $default_val = str_replace("\"", "'", $default_val);
        $columns = apply_filters( 'rem_property_fields_cols', 'col-sm-4 col-xs-12', $field );
        
        $show_condition = isset($field['show_condition']) ? $field['show_condition'] : 'true' ; 
        $conditions = isset($field['condition']) ? $field['condition'] : array() ;
        $required = (isset($field['required']) && $field['required'] == 'true' ) ? 'required' : '' ;
        $required_badge = (isset($field['required']) && $field['required'] == 'true' ) ? '<span title="'.__( 'Required', 'real-estate-manager' ).'" class="glyphicon glyphicon-asterisk"></span>' : '' ;
        $help_text = (rem_get_option('property_fields_help_text') == 'enable' && $field['help'] != '') ? '<span title="'.wp_strip_all_tags($field['help']).'" class="glyphicon glyphicon-question-sign"></span>' : '' ;
        $help_text = apply_filters( 'property_fields_help_text', $help_text, $field, $value );
        $dropdown_class = rem_get_option('dropdown_style', 'rem-easydropdown');
        ?>
        <div class="<?php echo $columns; ?> space-form wrap-<?php echo $field['key']; ?>" data-condition_status="<?php echo $show_condition; ?>" data-condition_bound="<?php echo isset($field['condition_bound']) ? $field['condition_bound'] : 'all' ?>" data-condition='<?php echo json_encode($conditions); ?>'>
            <?php 
            switch ($field['type']) {
                case has_action( "rem_render_property_field_frontend_{$field['key']}" ) :
                    do_action( "rem_render_property_field_frontend_{$field['key']}", $field, $value );
                    break;
                case 'text':
                case 'number':
                case 'shortcode':
                case 'video':
                case 'date': 
                    $max = isset($field['max_value']) ? 'max="'.$field['max_value'].'"' : '';
                    $min = isset($field['min_value']) ? 'min="'.$field['min_value'].'"' : '';
                    ?>
                    <label for="<?php echo $field['key']; ?>" class="rem-field-label">
                        <?php echo $field['title']; ?>
                        <?php echo $help_text; ?>
                        <?php echo $required_badge; ?>
                    </label>
                    <input id="<?php echo $field['key']; ?>" <?php echo $max; ?> <?php echo $min; ?>  <?php echo $required; ?> class="form-control" value="<?php echo stripcslashes($default_val); ?>" type="<?php echo $field['type']; ?>" title="<?php echo $field['help']; ?>" name="<?php echo $field['key']; ?>">
                    <?php break;
                case 'checkbox': ?>
                    <?php $chkd = (isset($default_val[$field['key']])) ? 'checked' : '' ; ?>
                    <input <?php echo $chkd; ?> class="labelauty" type="checkbox" name="property_detail_cbs[<?php echo $field['key']; ?>]" data-labelauty="<?php echo $field['title']; ?>">
                    <?php break;
                case 'upload': 
                    if (is_user_logged_in()) {  ?>
                        <div class="upload-attachments-wrap">
                            <p class="text-center">
                                <button type="button"
                                    class="upload-attachment btn btn-info"
                                    data-title="<?php _e( 'Select attachments for property', 'real-estate-manager' ); ?>"
                                    data-field_key="<?php echo $field['key']; ?>"
                                    data-max_files="<?php echo (isset($field['max_files'])) ? esc_attr( $field['max_files'] ) : '' ; ?>"
                                    data-file_type="<?php echo (isset($field['file_type'])) ? esc_attr( $field['file_type'] ) : '' ; ?>"
                                    data-max_files_msg="<?php echo (isset($field['max_files_msg'])) ? esc_attr( $field['max_files_msg'] ) : '' ; ?>"
                                    data-btntext="<?php _e( 'Add', 'real-estate-manager' ); ?>">
                                    <span class="dashicons dashicons-paperclip"></span>
                                    <?php echo $field['title']; ?>
                                </button>
                            </p>
                            <p>
                                <?php echo esc_attr($field['help'] ); ?>
                            </p>
                            <div class="row attachments-prev">
                                <?php
                                    if ($value != '') {
                                        if (!is_array($value)) {
                                            $value = explode("\n", $value);
                                        }
                                        if (is_array($value)) {
                                            foreach ($value as $id) {
                                                $attachment_url = wp_get_attachment_image_src( $id, 'thumbnail', true ); ?>
                                                <div class="col-sm-3">
                                                    <div class="rem-preview-image">
                                                        <input type="hidden" name="<?php echo $field['key']; ?>[<?php echo trim($id); ?>]" value="<?php echo $id; ?>">
                                                        <div class="rem-image-wrap">
                                                            <img class="attachment-icon" src ="<?php echo $attachment_url[0]; ?>">
                                                            <span class="attachment-name"><a target="_blank" href="<?php echo wp_get_attachment_url( $id ); ?>"><?php echo get_the_title( $id ); ?></a></span>
                                                        </div>
                                                        <div class="rem-actions-wrap">
                                                            <a href="javascript:void(0)" class="btn remove-image btn-sm">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                    <?php } else {  ?>
                    <div class="upload-attachments-wrap">
                        <p style="text-align: center" id="attachments-upload-btn-area">
                        <?php $media_limit =  (isset($field['max_files']) && !empty($field['max_files'])) ? $field['max_files'] : '4' ; ?>
                        <?php for ($i=0; $i < $media_limit; $i++) { ?>
                            <label for="<?php echo  $field['key'].'_'.$i; ?>" class="btn btn-default"
                                data-title="<?php _e( 'Select attachments for property', 'real-estate-manager' ); ?>"
                                data-field_key="<?php echo $field['key']; ?>"
                                data-max_files="<?php echo (isset($field['max_files'])) ? esc_attr( $field['max_files'] ) : '' ; ?>"
                                data-file_type="<?php echo (isset($field['file_type'])) ? esc_attr( $field['file_type'] ) : '' ; ?>"
                            >
                                <span class="dashicons dashicons-images-alt2"></span>
                                <?php echo $field['title']; ?>
                                <input class="attachmentsupload" id="<?php echo  $field['key'].'_'.$i; ?>" type="file" name="<?php echo $field['key']; ?>[]" />
                            </label>
                            <?php } ?>
                        </p>
                        <div class="attachments-prev"></div>
                    </div>
                    <?php } ?>    
                    <?php break;
                case 'textarea': ?>
                    <label class="rem-field-label">
                        <?php echo $field['title']; ?>
                        <?php echo $help_text; ?>
                        <?php echo $required_badge; ?>
                    </label>
                    <textarea <?php echo $required; ?> id="<?php echo $field['key']; ?>" name="<?php echo $field['key']; ?>" class="form-control" rows="2"><?php echo stripcslashes($value); ?></textarea>
                    <?php break;

                case 'select': ?>
                    <label class="rem-field-label">
                        <?php echo $field['title']; ?>
                        <?php echo $help_text; ?>
                        <?php echo $required_badge; ?>
                    </label>
                    <select <?php echo $required; ?> class="<?php echo esc_attr($dropdown_class); ?>" data-settings='{"cutOff": 5}' name="<?php echo $field['key']; ?>">
                        <?php if($field['key'] != 'property_featured'){ ?>
                            <option value="">-- <?php echo __( 'Any', 'real-estate-manager' ).' '.$field['title']; ?> --</option>
                        <?php } ?>
                        <?php
                            if (is_array($field['options'])) {
                                $options = $field['options'];
                            } else {
                                $options = explode("\n",$field['options']);
                            }
                            foreach ($options as $title) {
                                $title = stripcslashes($title);
                                $selected = ($default_val == $title) ? 'selected' : '' ;
                                echo '<option value="'.$title.'" '.$selected.'>'.$title.'</option>';
                            }
                        ?>
                    </select>
                    <?php break;
                case 'select2': ?>
                    <label class="rem-field-label">
                        <?php echo $field['title']; ?>
                        <?php echo $help_text; ?>
                        <?php echo $required_badge; ?>
                    </label>
                    <select <?php echo $required; ?> class="rem-select2-field" data-settings='{"cutOff": 5}' name="<?php echo $field['key']; ?>[]" multiple>
                        <?php if($field['key'] != 'property_featured'){ ?>
                            <option value="">-- <?php echo __( 'Any', 'real-estate-manager' ).' '.$field['title']; ?> --</option>
                        <?php } ?>
                        <?php
                            if (is_array($field['options'])) {
                                $options = $field['options'];
                            } else {
                                $options = explode("\n",$field['options']);
                            }
                            foreach ($options as $title) {
                                $title = stripcslashes($title);
                                $selected = (is_array($value) && in_array($title, $value)) ? 'selected' : '' ;
                                echo '<option value="'.$title.'" '.$selected.'>'.$title.'</option>';
                            }
                        ?>
                    </select>
                    <?php break;
            
                default:
                    
                    break;
            } ?>
        </div>
        <?php
    }

    function generatePassword($length, $strength) {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength & 1) 
        {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength & 2) 
        {
            $vowels .= "AEUY";
        }
        if ($strength & 4) 
        {
            $consonants .= '23456789';
        }
        if ($strength & 8) 
        {
            $consonants .= '@#$%';
        }
        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) 
        {
            if ($alt == 1) 
            {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } 
            else 
            {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }
}
if( class_exists('RemGuestUserAddon')){

    $rem_guest_user_obj = new RemGuestUserAddon;

    if (defined('REM_PATH')) {
        require_once REM_PATH.'/inc/update/wp-package-updater/class-wp-package-updater.php';
        $guest_submission_updater = new WP_Package_Updater(
            'https://kb.webcodingplace.com/',
            wp_normalize_path( __FILE__ ),
            wp_normalize_path( plugin_dir_path( __FILE__ ) )
        );
    }
}
