<?php
/*
Plugin Name: Open Literature
Plugin URI: http://github.com/OpenHumanities
Description: A plugin to insert an Open Literature instance into Wordpress. 
Author: OKFN
Version: 0.1
Author URI: http://www.openliterature.net
*/
ini_set("allow_url_fopen", true);
include __DIR__ .'/controller/get_text_controller.php';

// set up the Textus slug API
add_action('init', 'register_textus');

//Set up the Textus API
add_action('init', 'textus_get_control');
add_shortcode('textus', 'textus_shortcode');

// function to create annotation table
register_activation_hook( __FILE__, 'textus_install' );

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
          case 'POST':
          case 'PUT':
            //needs testing against the textus code
            $server = true;
            break;
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
               //$parse = parse_parameters();
               if ( $_GET['type'] == 'annotation' ) {
                  if (intval($_GET['text'])) {
                     return_response(array("status"=>200, "notes"=>textus_get_annotations($_GET['text'])));
                  } else {
                     return_response(array("status" => 403, "error"=>"You need to specify a text"));
                  }
                }
                break;
            case 'POST':
              $textid = json_decode(file_get_contents("php://input"), TRUE);
              //@todo get the vars which the textus viewer sets
              
              // returns the new noteid
              $name = textus_db_get_id($textid['name']);
               if (!$name) {
                   var_dump($name);
                  return_response(array("status" => 403, "note"=>"This user does not exist"));
              }
              $noteid = textus_insert_annotation(
                $name, $textid['textid'], 
                $textid['start'], $textid['end'], 
                $textid['private'], 
                $textid['payload']['language'], $textid['payload']['text']
              );

              if (intval($noteid) > 0) {
                 return_response(array("status" => 200, "note"=>"The note has been stored" + intval($noteid)));
              } else {
                 return_response(array("status" => 403, "note"=>"The note could not updated"));
              }
              
              break;
            case 'PUT':
             
              //@todo get the vars which the textus viewer sets
              $textid = json_decode(file_get_contents("php://input"), TRUE);
              // returns the new noteid
              $name = textus_db_get_id($textid['name']);
              //$userid, $id, $start, $end, $private, $lang, $text, $noteid

              $noteid = textus_updates_annotation(
                 $name, $textid['textid'], 
                $textid['start'], $textid['end'], 
                $textid['private'], 
                $textid['payload']['language'], $textid['payload']['text'], $textid['id']);
                            
              if (intval($noteid) > 0 ) {
                  return_response(array("status"=> 200, "notes" => $textid['id'] + " has been updated"));
              }
              break;
            case 'DELETE':
             
              //@todo get the vars which the textus viewer sets
              $textid = json_decode(file_get_contents("php://input"), TRUE);
              // returns the new noteid
              $name = textus_db_get_id($textid['name']);
              //$userid, $id, $start, $end, $private, $lang, $text, $noteid
              $noteid = textus_delete_annotation($textid['id']);
              if (intval($noteid) > 0 ) {
                  return_response(array("status"=> 200, "notes" => $textid['id'] + " has been deleted"));
              }
              break;
           default:
             $parse = parse_parameters();
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
function parse_parameters($data)
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
                //$data = file_get_contents("php://input");
                $body_params = json_decode($data, TRUE);
                print_r($body_params);
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
           return wp_send_json($response_data);
         }
}

/* Wordpress DB functions */

/**
*  Functions to install a table for the annotations in Wordpress
*
*/
function textus_install() {
   global $wpdb;
   // name it as like the other WP tables but add textus so it can be quickly found
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
	} 

       id will be int of the currently logged in user. 
*/
   $sql = "CREATE TABLE $table_name (
     id mediumint(9) NOT NULL AUTO_INCREMENT,
     textid mediumint(9) NOT NULL, 
     time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
     start smallint NOT NULL,
     end  smallint NOT NULL,
     userid  smallint NOT NULL,
     private tinytext NOT NULL,
     language tinytext NOT NULL, 
     text text NOT NULL,
     UNIQUE KEY id (id)
   );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );

}

/**
*  Function to insert the notes into the store
*
*  @return int
*  Number of rows affected. If 0, then operation has failed
*/
function textus_insert_annotation($userid, $textid, $start, $end, $private, $lang, $text) {
  $rows = textus_db_insert_annotation($userid, $textid, $start, $end, $private, $lang, $text);
  return ($rows) ? $rows : false;
 
}

/**
*  Function to update the notes into the store
*
*  @return int
*  Number of rows affected. If 0, then operation has failed
*/
function textus_updates_annotation($userid, $id, $start, $end, $private, $lang, $text, $noteid) {
  $rows = textus_db_update_annotation($userid, $id, $start, $end, $private, $lang, $text, $noteid);
  if ($rows)
  {
    return $rows;
  }
}

/**
*  Function to update the notes into the store
*
*  @return int
*  Number of rows affected. If 0, then operation has failed
*/
function textus_delete_annotation($noteid) {
  $rows = textus_db_delete_annotation($noteid);
  if ($rows)
  {
    return $rows;
  }
}

/**
*  Function to insert the annotation into the table
* 
*
*  return int
*  returns the number of rows affected. Should only be 1. If not, the calling function needs to throw an error.
*/
function textus_db_insert_annotation($userid, $textid, $start, $end, $private, $lang, $text)
{
    global $wpdb;

    $table_name = $wpdb->prefix . "textus_annotations"; 

   $rows_affected = $wpdb->insert( $table_name, 
     array( 
       'textid' => $textid,
       'start' => $start, 
       'end' => $end, 
       'userid' => $userid,
       'private' => $private,
       'language' => $lang,
       'text' => $text
        ) 
      );
    return $rows_affected;
}

/**
*  Function to get the text annotations for a given id
*
*  @param textid
*  The text id given from the API
*
*  @return array
*  Returns an array of the annotations to be jsonified later
*/
function textus_get_annotations($textid)
{
   global $currentuser;
    $annotations = array();
   if (!$textid) {
     return wp_send_json("No text id was given");
   }

   $notes = textus_db_select_annotation($textid);
   if (!$notes)
   {
      //actually do we want to return an empty JSON message?
      $annotations = array('error' => 'No annotations could be found for this text' );
   } else {
     foreach ($notes as $note)
     {
         // put the notes into the correct structure
         $annotations[] = array(
            "id" => $note->id,
            "start" => $note->start, 
            "end" => $note->end, 
            "time" => $note->time, 
            "private" => $note->private, 
            "payload" => array(
               "language" => $note->language, 
               "text" => $note->text)
            );
     }
   }
   return $annotations;
}

/**
*   Function to get the annotations from the store
*/
function textus_db_select_annotation($textid)
{
  global $wpdb;
  if (!$textid) {
     return wp_send_json(array("status"=>500, "error"=>"No text id was given"));
  }
  $notes = $wpdb->get_results( 
   "SELECT id, start, end, time, userid, private, language, text
    FROM " . $wpdb->prefix . "textus_annotations
    WHERE textid='$textid'"
  );

   if ($notes) 
   {
      return $notes;
   }
}

/**
*   Function to get user id from the given "nice_name"
*/
function textus_db_get_id($name)
{
  global $wpdb;
  if (!$name) {
     return wp_send_json(array("status"=>500, "error"=>"No username was given"));
  }
  $notes = $wpdb->get_var( 
   "SELECT ID
    FROM " . $wpdb->prefix . "users
    WHERE user_nicename='$name'"
  );

   if ($notes) 
   {
      return $notes;
   }
}

/**
*  Update the store
*/
function textus_db_update_annotation ($userid, $textid, $start, $end, $private, $lang, $text,$id) {
  global $wpdb;
  $updates = $wpdb->update( $wpdb->prefix."textus_annotations", 
     array( 
       'start' => $start, 
       'end' => $end, 
       'userid' => $userid,
       'private' => $private,
       'language' => $lang,
       'text' => $text
      ),
      array('id' => $id), 
      $format = null, 
      $where_format = null 
  );
  if ($updates) {
     return $updates;
  }
}

function textus_db_delete_annotation ($noteid) {
  global $wpdb;
  $delete = $wpdb->delete( $wpdb->prefix."textus_annotations", 
      array('id' => $noteid), 
      $format = null, 
      $where_format = null 
  );
  if ($delete) {
     return $delete;
  }
}

?>
