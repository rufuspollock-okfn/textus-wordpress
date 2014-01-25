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
//register_textus_viewer();
//add_action('wp_register_scripts','enqueue_textus_viewer');

//Set up the Textus API
add_action('init', 'textus_get_control');
add_shortcode('textus', 'textus_shortcode');

/* Wordpress Textus functions */

/**
 * Function to create a "slug" for the Textus Javascript / HTML
 */
function register_textus()
{
    $label = array(   
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
var textusTypography = "'.$rawjson.'";
var textUrl = "'.$rawtext.'";
var apiUrl = "";
var currentUser = { id : '.get_current_user_id().'};
viewer = new Viewer(textUrl, textusTypography, apiUrl, currentUser);
</script>
</pre>';
} 


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
        return $text['data'];
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
      if ($js != '.' && $js != '..') {
        $jsfsplit = split('/', $js);
        wp_register_script( substr(end($jsfsplit), 0,-3), plugins_url("/textus-wordpress/textus-viewer/vendor/$js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
        // hacky but good reminder for later integration
        if (substr(end($jsfsplit), 0,-3) != "jquery-1.7.2" && substr(end($jsfsplit), 0,-3) != "jquery.ui-1.8.22") {
          wp_enqueue_script( substr(end($jsfsplit), 0,-3));
        }
      }
    }
    //$dependencies = array('jquery, backbone', 'underscore');
    wp_register_script( 'textus-main', plugins_url("textus-wordpress/textus-viewer/js/main.js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
    wp_enqueue_script( 'textus-main');
    wp_register_script( 'textus-routes', plugins_url("textus-wordpress/textus-viewer/js/router.js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
    wp_enqueue_script( 'textus-routes');
    //array_push($dependencies, array('textus-viewer', 'textus-routes'));
    //array_push('textus-routes', $dependencies);
    wp_register_script( 'textus-reader', plugins_url("textus-wordpress/textus-viewer/js/activity/readTextActivity.js", dirname(__FILE__)), $dependencies, $version, $load_in_footer );
    wp_enqueue_script( 'textus-reader');
}


/* Textus API */
function is_server()
{
        $server = false;
        switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                        if (isset($_GET['text']) ) {
                                $server = true;
                        }
                        break;
                /*case 'POST':
                        //needs testing against the textus code
                        $server = true;
                        break;*/
                default:
                        wp_send_json(array("error" =>"Term not supported"));
                        break;
        }
        return $server;
}

/**
* Registered function which acts as an API for the textus viewer
* @param the GET url
*  Looks for the text and type parameters
*  @todo check if the values are always ints from the Textus
*
*/
function textus_get_control()
{
        global $urllink;

        if (is_server()) {
                
         // Load the relevant controller that contains the methods/
         switch($_SERVER['REQUEST_METHOD']) {
         case 'GET':
                        $request = new get_text_controller();
                        $parse = parse_parameters();
                        $response = ($_GET['type'] == "text") ? $request->ol_get_text($parse['text'], 'text') : $request->ol_get_text($parse['text'], 'typo');
            return_response($response);
                        break;
                /*case 'POST':
                        include (__DIR__.'/controller/post_controller.php');
                        //@todo get the vars which the textus viewer sets
                        $textid = parse_parameters();
                        $request = new post_controller();
            $response = $request->set_text($textid);
                        break;*/
                default:
                        $parse = self::parse_parameters();
                        if ($parse['action'] == 'json') {
                                return wp_send_json( array ('error' => 'Method is unsupported') );
                        }
                        break;
        }
        }
}

/**
* Function to parse the parameters.
* If the request method is get, then use the parse_str() to parse them
*
* Else take the input stream
*/
function parse_parameters()
{
        $parameters = array();
        $body_params = array();
        //if we get a GET, then parse the query string
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
                if (isset($_SERVER['QUERY_STRING'])) {
                        // make this more defensive
                        return $_GET;

                }
        } else {
                // Otherwise it is POST, PUT or DELETE.
                // At the moment, we only deal with JSON
                /*$data = file_get_contents("php://input");
                $body_params = json_decode($data);*/
        }

        foreach ($body_params as $field => $value) {
                $parameters[$field]= $value;
        }
        return $parameters;
}

/**
* Function to return the response given by the controller.
*
* @param Array $response_data
* Array of the data returned by the system
* @return String
* Response string depending on request type - JSON or HTML
*/
function return_response ($response_data) {
        // If the format is JSON, then send JSON else load the correct template
        //if ($response_data['format'] == 'json') {
         if (array_key_exists('error', $response_data)) {
                 return wp_send_json($response_data);
         }
         else {
           return wp_send_json(array("data"=>$response_data));
         }
        /*}
        else {
                if (array_key_exists('error', $response_data)) {
                        add_action('template_include', ol_set_template('error'));
                }
                else {
                        add_action('template_include', ol_set_template($parse['action']));
                }
        
        }*/
}

/* Wordpress DB functions */

/**
*  Functions to install a table for the annotations in Wordpress
*
*/
function textus_install() {
   global $wpdb;
   // name it as like the other WP tablesbut addd textus so it can be quickly found
    $table_name = $wpdb->prefix . "textus_annotations"; 
	/*
	"start" : 300,
	"end" : 320,
	"type" : "textus:comment",
	"userid" : [wordpress-id]
	"private": false
	"date" : "2010-10-28T12:34Z",
	"payload" : {
	    "lang" : "en"
	    "text" : "Those twenty characters really blow me away, man..."
	} */
   $sql = "CREATE TABLE $table_name (
     id mediumint(9) NOT NULL AUTO_INCREMENT,
     time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
     start smallint NOT NULL,
     end  smallint NOT NULL,
     userid  smallint NOT NULL,
     private tinytext NOT NULL,
     type text NOT NULL,
     text text NOT NULL,
     url VARCHAR(55) DEFAULT '' NOT NULL,
     UNIQUE KEY id (id)
   );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );

}


?>
