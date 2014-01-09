<?php
/*
Plugin Name: Open Literature
Plugin URI: http://github.com/OpenHumanities
Description: A plugin to insert an Open Literature instance into Wordpress. 
Author: OKFN
Version: 0.1
Author URI: http://www.openliterature.net
*/
include __DIR__ .'/controller/get_text_controller.php';

// set up the Textus slug API
add_action('init', 'register_textus');

//Textus Javascript functions

register_textus_viewer();
add_action('wp_register_scripts','enqueue_textus_viewer');


/* Wordpress Textus functions */

/**
 * Function to create a "slug" for the Textus Javascript / HTML
 */
function register_textus()
{
    $labels = array(
            'name' => _x('Textus', 'post type general name'),
            'singular_name' => _x('Textus Item', 'post type singular name'),
            'add_new' => _x('Add New', 'textus item'),
            'add_new_item' => __('Add New Textus Item'),
            'edit_item' => __('Edit Textus Item'),
            'new_item' => __('New Textus Item'),
            'view_item' => __('View Textus Item'),
            'search_items' => __('Search Texts'),
            'not_found' =>  __('Nothing found'),
            'not_found_in_trash' => __('Nothing found in Trash'),
            'parent_item_colon' => ''
                    );
    
    $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title','editor','thumbnail')
    );
    // register the post type
    register_post_type( 'textus', $args );
}

/**
 * Shortcode creation function to get the correct text
 * from the store
 * 
 * @param array $atts
 * @return string
 * HTML to place the test with textus markup
 */
function textus_shortcode( $atts ) {
    //extract the id from the incoming array
    extract(
      shortcode_atts(
        array(
          'id' => '',
        ), 
      $atts)
    );
    
    $rawtext = textus_get_text($id, 'text');   
    $rawjson = textus_get_text($id, 'json');

    // return the text with the call the the Javascript location
    return '<div id="raw">
'.$rawtext.'
</div>
<script src="/vendor/textus-viewer.js"></script>
<script type="text/javascript">
var textusTypography = typography;
// now boot textus viewer
viewer = new Viewer('.$rawtext.', '.$rawjson.');
</script>
</pre>';
}
add_shortcode('textus', 'textus_shortcode');

/**
 * Function to get the requested text
 */
function textus_get_text($id, $type) {
    $request = new get_text_controller();
    $text = $request->ol_get_text($id, $type);
    if ($text['error']) {
        return $text['error'];
    }
    else{
        return $text['text'];
    }
}


/* Javascript functions*/

function fetch_textus_vendor() {
    // list all vendor files
    $jsfiles = scandir(__DIR__ . '/textus-viewer/vendor');
    return (empty($jsfiles)) ? array('error in fetching files') : $jsfiles;
}

/**
*  Function to register the Textus JS functions in Wordpress
*  and load after the jquery, backbone or underscore
*/
function register_textus_viewer() {
    $jsfiles = fetch_textus_vendor();
    $url = plugins_url('', dirname(__FILE__));
    foreach ($jsfiles as $js) {
      if ($js != '.' || $js != '..') {
        $jsfsplit = split('/', $js);
        wp_register_script( substr(end($jsfsplit), 0,-3), plugins_url("textus-viewer/vendor/".$js, dirname(__FILE__)), $dependencies, $version, $load_in_footer );
        wp_enqueue_script( substr(end($jsfsplit), 0,-3));
      }
    }
    //$dependencies = array('jquery, backbone', 'underscore');
    wp_register_script( 'textus-main', plugins_url("textus-viewer/js/main.js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
    wp_enqueue_script( 'textus-main');
    wp_register_script( 'textus-routes', plugins_url("textus-viewer/js/router.js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
    wp_enqueue_script( 'textus-routes');
    //array_push($dependencies, array('textus-viewer', 'textus-routes'));
    //array_push('textus-routes', $dependencies);
    wp_register_script( 'textus-reader', plugins_url("textus-viewer/js/activity/readTextActivity.js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
    wp_enqueue_script( 'textus-reader');
}


?>
