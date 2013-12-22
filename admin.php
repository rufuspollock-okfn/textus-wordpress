<?php
/**
 * @file
 * Functions to set up necessary admin pages
 */
$ol_storage_admin = new ol_storage_admin();

class ol_storage_admin{
	
	/**
	 * Constructor for the Admin section
	 * 
	 */
    public function __construct(){
    	// Check if the user is an admin user. 
        if(is_admin()){
          // If so, show the forms.
	      add_action('admin_menu', array($this, 'add_plugin_page'));
	      add_action('admin_init', array($this, 'page_init'));
	   }
    }
	
    /**
     * Function to add the plugin page to the menu
     */
    public function add_plugin_page(){
        // This page will be under "Settings"
	add_options_page('Settings Admin', 'Storage', 'manage_options', 'ol-store', array($this, 'create_admin_page'));
    }

    /**
     * Function to create the Admin page. 
     */
    public function create_admin_page(){
        ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>Settings</h2>			
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields('test_option_group');	
		    do_settings_sections('ol-store');
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
	<?php
    }
	
    /**
     * Function to set up the admin page.
     */
    public function page_init(){	
    	// Register the setting.	
	    register_setting('test_option_group', 'array_key', array($this, 'check_storage'));
		
        add_settings_section(
	        'setting_section_id',
	        'Setting',
	        array($this, 'print_section_info'),
	        'ol-store'
	    );	
		
	    add_settings_field(
	        'ol_storage', 
	        'Storage (Filesystem, S3, etc)', 
	        array($this, 'create_storage'), 
	        'ol-store',
	        'setting_section_id'			
	    );		
    }
	
    /**
     * Function to store the selected backend type
     * from the admin form
     * 
     * @param string $input
     *   Incoming input from the Storage form
     * @return string
     *   Return the options
     */
    public function check_storage($input){

	    $mid = $input['ol_storage'];			
	    if(get_option('ol_backend') === FALSE){
		  add_option('ol_backend', $mid);
	    }else{
		  update_option('ol_backend', $mid);
	    }
	    return $mid;
    }
	
    /**
     * Function to print the help text for the form.
     */
    public function print_section_info(){
	    print 'Select your storage:';
    }
	
    /**
     * Function to show an HTML admin form to choose a storage system
     */
    public function create_storage(){
        ?><input type="radio" id="ol_storage" name="array_key[ol_storage]" value="<?=get_option('ol_backend');?>" /><?php
    }
}
?>