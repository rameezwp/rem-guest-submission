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
* 	Guest User Addon
*/
class RemGuestUserAddon {
	
	function __construct(){
        add_shortcode( 'rem_guest_user_create_property', array($this, 'create_property') );
		add_shortcode( 'rem_custom_create_property', array($this, 'custome_create_property') );
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
    function custome_create_property($attrs){
        
        $form_id = isset($attrs['form']) ? $attrs['form'] : '';
        $add_images = isset($attrs['add_images']) && $attrs['add_images'] == 'true' ? true : false;
        $add_attachments = isset($attrs['add_attachments']) && $attrs['add_attachments'] == 'true' ? true : false;
            
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

		wp_enqueue_style( 'rem-select2-css', REM_URL . '/assets/admin/css/select2.min.css' );
        wp_enqueue_script( 'rem-select2-js', REM_URL . '/assets/admin/js/select2.min.js' , array('jquery'));
           
		// wp_enqueue_script( 'rem-create-pro', REM_URL . '/assets/front/js/create-property.js', array('jquery', 'jquery-ui-sortable'));
		wp_enqueue_script( 'guest-user-crate-pro', REM_GU_URL . '/js/scripts.js', array('jquery'));

       
        $localize_vars = array(
            'def_lat' => 'disable',
            'images_limit' => $images_limit,
            'success_message' => __( 'Successful', 'real-estate-manager' ),
        );                
        wp_localize_script( 'guest-user-crate-pro', 'rem_property_vars', $localize_vars );

		ob_start();
			$in_theme = get_stylesheet_directory().'/rem/shortcodes/custom-create-property.php';
			if (file_exists($in_theme)) {
				include $in_theme;
			} else {
				include REM_GU_PATH. '/shortcodes/custom-create-property.php';
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
    	$property_data = array(
    	  'post_title'    => wp_strip_all_tags( $data['title'] ),
    	  'post_content'  => html_entity_decode($data['content']),
    	  'post_type'   => 'rem_property',
    	  'post_status'   => apply_filters( 'rem_property_publish_status', $status, $property_id ),
    	);
    	
    	$property_id = wp_insert_post( $property_data );

        foreach ($data as $key => $meta) {
            if ($key != 'title' || $key != 'content' || $key != 'rem_property_data' || $key != 'tags') {
                update_post_meta( $property_id, 'rem_'.$key, $meta );
                // var_dump($key);
            }
        }
        $property_images = array();
        $property_attachments = array();
        if ( isset($_FILES["images"]) ) { 
            $files = $_FILES["images"];     
            foreach ($files['name'] as $key => $value) {            
                if ($files['name'][$key]) { 
                    $file = array( 
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key], 
                        'tmp_name' => $files['tmp_name'][$key], 
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    ); 
                    $_FILES_images = array ("my_file_upload" => $file); 
                    foreach ($_FILES_images as $file => $array) {              
                        $newupload = $this->my_handle_attachment($file,$property_id);
                        $property_images[] = $newupload;
                    }
                } 
            } 
        }
        if ( isset($_FILES["attachemnts"])  ) {
            $attachemnts = $_FILES["attachemnts"];  
            foreach ($attachemnts['name'] as $key => $value) {            
                if ($attachemnts['name'][$key]) { 
                    $file = array( 
                        'name' => $attachemnts['name'][$key],
                        'type' => $attachemnts['type'][$key], 
                        'tmp_name' => $attachemnts['tmp_name'][$key], 
                        'error' => $attachemnts['error'][$key],
                        'size' => $attachemnts['size'][$key]
                    ); 
                    $_FILES = array ("my_attchments_file_upload" => $file); 
                    foreach ($_FILES as $file => $array) {              
                        $newupload_attachments = $this->my_handle_attachment($file,$property_id);
                        $property_attachments[] = $newupload_attachments;
                    }
                } 
            } 
        }
        if (!empty($property_images)) {
            update_post_meta( $property_id, 'rem_property_images', $property_images );
            $img_ids = 0;
            foreach ($property_images as $imgID => $id) {
                if ($img_ids == 0) {
                    set_post_thumbnail( $property_id, $imgID );
                }
                $img_ids++;
            }
        }else {
            update_post_meta( $property_id, 'rem_property_images', '' );
        }

        if (!empty($property_attachments)) {
            $property_attachments = implode( "\n", $property_attachments );
            update_post_meta( $property_id, 'rem_file_attachments', $property_attachments );
        }else {
            update_post_meta( $property_id, 'rem_file_attachments', '' );
        }

        if (!isset($data['property_detail_cbs'])) {
            update_post_meta( $property_id, 'rem_property_detail_cbs', '' );
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

}
if( class_exists('RemGuestUserAddon')){

    $rem_guest_user_obj = new RemGuestUserAddon;

}