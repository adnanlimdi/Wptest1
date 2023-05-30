<?php
/*
Plugin Name: Api Developement Plugin
Description: Add post types for custom Event And Api Calls
Author: Adnan 
*/
// Hook ht_custom_post_custom_article() to the init action hook

/**
 * Activate the plugin.
 */
function testone_activate() { 
	// Trigger our function that registers the custom post type plugin.
	testone_custom_post_custom_article(); 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'testone_activate' );

/**
 * Register the "Event" custom post type
 */
add_action( 'init', 'testone_custom_post_custom_article' );
// The custom function to register a custom article post type
function testone_custom_post_custom_article() {
    // Set the labels. This variable is used in the $args array
    $labels = array(
        'name'               => __( 'Custom Event' ),
        'singular_name'      => __( 'Custom Event' ),
        'add_new'            => __( 'Add New Custom Event' ),
        'add_new_item'       => __( 'Add New Custom Event' ),
        'edit_item'          => __( 'Edit Custom Event' ),
        'new_item'           => __( 'New Custom Event' ),
        'all_items'          => __( 'All Custom Event' ),
        'view_item'          => __( 'View Custom Event' ),
        'search_items'       => __( 'Search Custom Event' ),
        'featured_image'     => 'Poster',
        'set_featured_image' => 'Add Poster'
    );
// The arguments for our post type, to be entered as parameter 2 of register_post_type()
    $args = array(
        'labels'            => $labels,
        'description'       => 'Holds our custom events post specific data',
        'public'            => true,
        'menu_position'     => 5,
        'supports'          => array( 'title', 'editor', 'comments', 'custom-fields' ),
        'has_archive'       => true,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'query_var'         => true,

         // This is where we add taxonomies to our CPT
         'taxonomies'          => array( 'eventcategory' ),
    );
    // Call the actual WordPress function
    // Parameter 1 is a name for the post type
    // Parameter 2 is the $args array
    register_post_type('events', $args);

    register_taxonomy('eventcategory', ['events'], [
		'label' => __('Category', 'testone'),
		'hierarchical' => false,
		'rewrite' => ['slug' => 'eventcategory'],
		'show_admin_column' => true,
		'show_in_rest' => true,
		'labels' => [
			'singular_name' => __('Category', 'testone'),
			'all_items' => __('All Category', 'testone'),
			'edit_item' => __('Edit Category', 'testone'),
			'view_item' => __('View Category', 'testone'),
			'update_item' => __('Update Category', 'testone'),
			'add_new_item' => __('Add New Category', 'testone'),
			'new_item_name' => __('New Category Name', 'testone'),
			'search_items' => __('Search Category', 'testone'),
			'popular_items' => __('Popular Category', 'testone'),
			'separate_items_with_commas' => __('Separate Category with comma', 'testone'),
			'choose_from_most_used' => __('Choose from most used Category', 'testone'),
			'not_found' => __('No Category found', 'testone'),
		]
	]);

    
}


// All Buid Using Get api , If you want to change Get to post then please change GET parameter.
// Register REST API endpoints
class Login_REST_API_Endpoints {

    /**
     * Register the routes for the objects of the controller.
     */
    public static function register_endpoints() {
        // endpoints will be registered here
        // register_rest_route( 'wp', '/login', array(
        //     'methods' => 'GET',
        //     'callback' => array( 'Login_REST_API_Endpoints', 'login' ),
        // ) );

        register_rest_route( 'events', 'create', array(
            'methods' => 'GET',
            'callback' => array( 'Login_REST_API_Endpoints', 'create_event' ),
        ) );

        register_rest_route( 'events', '/getevents', array(
            'methods' => 'GET',
            'callback' => array( 'Login_REST_API_Endpoints', 'getevents' ),
        ) );


        register_rest_route( 'events', '/delete', array(
            'methods' => 'GET',
            'callback' => array( 'Login_REST_API_Endpoints', 'deleteevent' ),
        ) );

        register_rest_route( 'events', '/update', array(
            'methods' => 'GET',
            'callback' => array( 'Login_REST_API_Endpoints', 'updateevent' ),
        ) );

        register_rest_route( 'events', '/show', array(
            'methods' => 'GET',
            'callback' => array( 'Login_REST_API_Endpoints', 'showevent' ),
        ) );


    }

    // Add Event Start date {startdate} ,End date {enddate}, descriptions ,Category and title 

    public static function create_event($request){
        // Gather post data.
            // Product Title
            $post_title        = $request['title'];
            $post_startdate    = date("d-m-Y", strtotime($request['startdate']));
            $post_enddate      = date("d-m-Y", strtotime($request['enddate']));

            $post_descriptions = $request['descriptions'];
            $postcategory      = $request['category'];

            $result            = array(); 


            if(empty($post_title)){
                $result['error'][] = '{title}  Title Is Missing!';
            }


            if(empty($request['startdate'])){
                $result['error'][] = '{startdate}  Start Date Is Missing!';
            }


            if(empty($request['enddate'])){
                $result['error'][] = '{enddate} End Date Is Missing!';
            }

            if ((strtotime($post_startdate)) > (strtotime($post_enddate)))
            {
                $result['error'][] = '{startdate} Start date is in front of end date!';
            }

            if (get_page_by_title($post_title, OBJECT, 'events')) {
                // Exists
                $result['error'][] = "{title} Title Already Exists";
            }

            if(empty($result['error'])  ){
                    // Add Product

                    
                    $post_title = sanitize_text_field( $post_title);


                    $new_post = array(
                    'post_title' => $post_title,
                    'post_type' => 'events',
                    'post_status' => 'publish', 
                    'post_content' => $post_descriptions,
                    );

                    // Catch post ID
                    $post_id = wp_insert_post( $new_post );
                    $metastartdate_key = 'startdate';
                    $metastartdate_value = sanitize_text_field($post_startdate);
                    $unique = true;
                    $metaendate_key = 'enddate';
                    $meta_enddatevalue = sanitize_text_field($post_enddate);
        

                    add_post_meta( $post_id, $metastartdate_key, $metastartdate_value, $unique );
                    add_post_meta( $post_id, $metaendate_key, $meta_enddatevalue, $unique );  
                    if($post_id){

                        $result['statuscode'] =  200;
                        $result['postid']     =  $post_id;
                        $result['message']    = 'Event Added Sccssfully';
                        wp_set_post_terms( $post_id, array($postcategory), 'eventcategory', true );

                        
                    }

            }else{

                $result['statuscode'] =  400;

            }

       

            return $result;

    }


    // Get Event List using start date ,end date

    //exampletesting case: https://demo.test/wp-json/events/getevents?startdate=22-11-2021&enddate=23-11-2025
    //exampletesting case start date: https://demo.test/wp-json/events/getevents?startdate=22-11-2021
    

    public static function getevents($request){

                    $date = array();
                    if($request['startdate']){
                        $date =  array(
                            'key' => 'startdate',
                            'value' => $request['startdate'],
                            'compare' => '=',
                        );
                    }


                    if($request['enddate']){
                        $date =  array(
                            'key' => 'enddate',
                            'value' => $request['enddate'],
                            'compare' => '=',
                        );
                    }
                    
                    if(!empty($request['startdate']) && !empty($request['enddate']) ){
                        $date = array(
                        'relation' => 'and',
                        array(
                            'key'       => 'startdate',
                            'value'     => $request['startdate'],
                            'compare'   => '=',
                        ),
                        array(
                            'key'       => 'enddate',
                            'value'     => $request['enddate'],
                            'compare'   => '=',
                        ));

                    }   
            
                    $posts = array();
                    $query = new WP_Query( 
                    array( 'post_type' => 'events',
                    'orderby' => 'date',
                    'order' => 'DESC', 
                    'posts_per_page' => -1,
                    'post_status'  => 'publish',
                    'meta_query' => array(
                        $date
                    )
                    ) );

                    $posts = $query->posts;
                    $postsd = array();
                    foreach  ($posts as $post) {
                        $postsd[$post->ID]['id'] = $post->ID;
                        $postsd[$post->ID]['post_date'] = $post->post_date;
                        $postsd[$post->ID]['post_title'] = $post->post_title;
                        $postsd[$post->ID]['pageurl'] = get_permalink($post->ID);
                        $postsd[$post->ID]['startdate']  = get_post_meta( $post->ID, 'startdate', TRUE);
                        $postsd[$post->ID]['enddate']  = get_post_meta( $post->ID, 'enddate', TRUE); 
                        $postsd[$post->ID]['eventcategory']  = wp_get_object_terms($post->ID, 'eventcategory', array( 'fields' => 'names' ) );

                        

                    }

                    return $postsd;

    }

    // Delete Event List. 
    public static function deleteevent($request){
            $postid = $request['id'];

            if( is_null(get_post($postid))){

                $result['error'] = "Event $postid does not exists or was deleted";
   
           }else{
            $delete = wp_delete_post($postid, true); // `true` indicated you would like to force delete (skip trash)

                if($delete){
                    $result['message'] = "Post Delete Successfully";
                    $result['statuscode'] =  200;
                }else{
                    $result['statuscode'] = 400;
                    $result['Message'] = "Post Id Not Found.";
                }   

            } 

            return $result;

    }

    //Show Event Using Id.
    public static function showevent($request){
            $ID = $request['id'];
            $args = array('p' => $ID, 'post_type' => 'events');
            $loop = new WP_Query($args);
            $posts = $loop->posts;
            if(!empty($posts)){
                $postsd = array();
                foreach  ($posts as $post) {
                    $postsd[$post->ID]['id'] = $post->ID;
                    $postsd[$post->ID]['post_date'] = $post->post_date;
                    $postsd[$post->ID]['post_title'] = $post->post_title;
                    $postsd[$post->ID]['pageurl'] = get_permalink($post->ID);
                    $postsd[$post->ID]['startdate']  = get_post_meta( $post->ID, 'startdate', TRUE);
                    $postsd[$post->ID]['enddate']  = get_post_meta( $post->ID, 'enddate', TRUE); 
                    $postsd[$post->ID]['eventcategory']  = wp_get_object_terms($post->ID, 'eventcategory', array( 'fields' => 'names' ) );

                    

                }
                $result['eventdetails'] = $postsd;
                $result['statuscode'] = 200;
            }else{
                $result['statuscode'] = 400;
                $result['Message'] = "Post Id Not Found.";
            }
            return $result;

    }



    // Update Event using Post id.
    public static function updateevent($request){

        $eventid = $request['id'];

        if(!empty($request['title'])){
            $post_title        = $request['title'];
        }else{
            $post_title           = get_the_title($eventid);
        }

        $post_startdate    = date("d-m-Y", strtotime($request['startdate']));
        $post_enddate      = date("d-m-Y", strtotime($request['enddate']));

        $post_descriptions = $request['descriptions'];
        $postcategory      = $request['category'];

        if( is_null(get_post($eventid))){

             $result['error'] = "post $eventid does not exists or was deleted";

        }else{

            $updateevent = array(
                'ID' =>  $eventid,    
                'post_title' => $post_title,
                'post_type' => 'events',
                'post_status' => 'publish', 
                'post_content' => $post_descriptions,
            );

           $postid =  wp_update_post( $updateevent );
           if(!empty( $postcategory )){ 
             wp_set_post_terms( $postid, array($postcategory), 'eventcategory', true );
           }

           if(!empty($post_startdate) && !empty($post_enddate)){

           }
           $result['message'] = 'Event Updated Successfully';
           $result['statuscode'] = 200;

        }
        return $result;



    }


}

add_action( 'rest_api_init', array( 'Login_REST_API_Endpoints', 'register_endpoints' ) );