<?php
/*
Plugin Name: Page Blurb
Plugin URI:  http://URI_Of_Page_Describing_Plugin_and_Updates
Description: This plugin helps to add custom page title, excerpt and featured image which can be used as a page blurb.
Version:     1.0
Author:      Vaishali Jitesh
Author URI:  http://URI_Of_The_Plugin_Author
LICENSE:     GPL2
LICENSE URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Add meta fields (alternate title, excerpt, featured image) to meta box 
*/
function page_blurb_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "page-blurb-meta-box-nonce");
	
	// Meta fields for page blurb on Edit Page view in Dashboard.
	?>
        <div>
		<!--Page Blurb Title-->
            <label for="page-blurb-meta-box-title">Alternate Title</label>
            <input name="page-blurb-meta-box-title" type="text" value="<?php echo get_post_meta($object->ID, "page-blurb-meta-box-title", true); ?>">

            <br/>
		<!--Page Blurb Excerpt-->
			 <label for="page-blurb-meta-box-excerpt">Alternate Excerpt</label>
			 <input name="page-blurb-meta-box-excerpt" type="text" value="<?php echo get_post_meta($object->ID, "page-blurb-meta-box-excerpt", true); ?>">
		</div>
					 
		<!--Page Blurb Featured Image-->
	<?php
	global $content_width, $_wp_additional_image_sizes;
	$image_id = get_post_meta( $object->ID, '_listing_image_id', true );
	$old_content_width = $content_width;
	$content_width = 254;

	if ( $image_id && get_post( $image_id ) ) {
		
		if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
		
			$thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
		} else {
			$thumbnail_html = wp_get_attachment_image( $image_id, 'post-thumbnail' );
		
		}
		if ( ! empty( $thumbnail_html ) ) {
			$content = '<div id="page_blurb_image">'.$thumbnail_html;
			$content .= '<p class="hide-if-no-js "><a href="javascript:;" id="page_blurb_remove_image_button" >' . esc_html__( 'Remove listing image', 'text-domain' ) . '</a></p></div>';
			$content .= '<input type="hidden" id="page_blurb_upload_image" name="_listing_cover_image" value="' . esc_attr( $image_id ) . '" />';
			
		}
		$content_width = $old_content_width;
	} else {
		$content = '<div id="page_blurb_image"><img src="" style="width:' . esc_attr( $content_width ) . 'px;height:auto;border:0;display:none;" />';
		$content .= '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set listing image', 'text-domain' ) . '" href="javascript:;" id="page_blurb_upload_image_button" id="set-listing-image" data-uploader_title="' . esc_attr__( 'Choose an image', 'text-domain' ) . '" data-uploader_button_text="' . esc_attr__( 'Set listing image', 'text-domain' ) . '">' . esc_html__( 'Set listing image', 'text-domain' ) . '</a></p></div>';
		$content .= '<input type="hidden" id="page_blurb_upload_image" name="_listing_cover_image" value="" />';
		
	}
	
echo $content;

}

/**
 * Add meta box to add/edit page in WordPress dashboard
*/
function page_blurb_add_meta_box()
{
    add_meta_box("demo-page-blurb-meta-box", "Custom Page Blurb", "page_blurb_meta_box_markup", "page", "side", "high", null);
}

add_action("add_meta_boxes", "page_blurb_add_meta_box");

/**
 * Save custom fields in the database
*/
function page_blurb_save_meta_data($post_id, $post, $update)
{
    if (!isset($_POST["page-blurb-meta-box-nonce"]) || !wp_verify_nonce($_POST["page-blurb-meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "page";
   
   if($slug != $post->post_type)
        return $post_id;
		
    $page_blurb_meta_box_title_value = "";
    $page_blurb_meta_box_excerpt_value = "";
    $page_blurb_meta_box_image_id = "";
	
    if(isset($_POST["page-blurb-meta-box-title"]))
    {
		$page_blurb_meta_box_title_value = sanitize_title($_POST["page-blurb-meta-box-title"]);
	}   
    update_post_meta($post_id, "page-blurb-meta-box-title", $page_blurb_meta_box_title_value);
	


    if(isset($_POST["page-blurb-meta-box-excerpt"]))
    {
        $page_blurb_meta_box_excerpt_value = sanitize_text_field($_POST["page-blurb-meta-box-excerpt"]);
    }   
    update_post_meta($post_id, "page-blurb-meta-box-excerpt", $page_blurb_meta_box_excerpt_value);
	
	if( isset( $_POST['_listing_cover_image'] )){// && is_int( $_POST[ '_listing_cover_image' ] )) {
		
		$page_blurb_meta_box_image_id = (int) $_POST['_listing_cover_image'];
		update_post_meta( $post_id, '_listing_image_id', $page_blurb_meta_box_image_id );
	}
}
add_action("save_post", "page_blurb_save_meta_data", 10, 3);


/**
 * Display page or sub-pages blurb shorcode
*/
add_shortcode( 'display-pages-blurb', 'page_blurb_display_shortcode' );
function page_blurb_display_shortcode( $atts ) {

	//Prepare query parameters
	$args = array(
		'numberposts' => 1,
		'order' => 'ASC',
		'post_type' => 'page',
		'post_status' => 'publish'	,
	);
	// Pull in shortcode attributes and set defaults
	$atts = shortcode_atts( array(	'id'  => false), $atts, 'display-pages-blurb' );
	
	// If the post id is provided, fetch the pages with IDs provided
	if($atts['id']!=0 ){
		$args['post__in']= array($atts['id']);
	}
		// If the post id is not provided, fetch the child pages of the current page
	else{
		$args['post_parent__in'] = array(get_the_ID());
	}
	
	$blurb_output= page_blurb_get_blurb_html($args);
	return $blurb_output;
}
	
// This function executes the query based on arguments, fetches results and returns blurb meta data to be printed in HTML format.	

function page_blurb_get_blurb_html($args){
	$blurb_pages = new WP_Query($args);
	$return_str="<ul class='page_blurb'>";

	while( $blurb_pages->have_posts() ): $blurb_pages->the_post();
			$return_str.= 	"<li class='page_blurb_page_meta_title'>".esc_attr(get_post_meta(get_the_ID(), 'page-blurb-meta-box-title', true))."</li>";
			$return_str.= 	"<li class='page_blurb_page_meta_excerpt'>".esc_attr(get_post_meta(get_the_ID(), 'page-blurb-meta-box-excerpt', true))."<li>";
			$return_str.="<li class='page_blurb_page_meta_thumb'>".wp_get_attachment_image( esc_attr(get_post_meta(get_the_ID(), '_listing_image_id', true)), 'thumbnail' )."<li>";
	endwhile;
	$return_str.="</ul>";
	wp_reset_postdata();
	return $return_str;
}

//Include the page blurb plugin javascript 
function page_blurb_enqueue_scripts( $hook ) {	
	wp_enqueue_script( 'blurb_upload_script', plugin_dir_url( __FILE__) . 'js/blurb-upload.js' ,false, null, true );
}
add_action( 'admin_enqueue_scripts', 'page_blurb_enqueue_scripts' );

//Include the page blurb plugin stylesheet


function page_blurb_enqueue_stylesheet() 
{
	wp_enqueue_style( 'blurb_style', plugins_url( '/css/blurb_style.css', __FILE__ ) );
}

add_action('wp_enqueue_scripts', 'page_blurb_enqueue_stylesheet');
add_action('admin_enqueue_scripts', 'page_blurb_enqueue_stylesheet');