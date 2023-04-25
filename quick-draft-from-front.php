<?php
/*
Plugin Name: Quick Draft
Plugin URI: Updating soon
Description: Allows users to create a quick draft from the front end of the website.
Version: 1.0.1
Author: Mayank Kumar
Author URI: http://example.com
License: GPL2
*/

function quick_draft_form() {
  // Check if the user is logged in
  if ( ! is_user_logged_in() ) {
    return;
  }
  
  // Output the form HTML
  ?>
  <form id="quick-draft-form">
    <div>
      <label for="quick-draft-title">Title:</label>
      <input type="text" id="quick-draft-title" name="title">
    </div>
    <div>
      <label for="quick-draft-content">Content:</label>
      <textarea id="quick-draft-content" name="content"></textarea>
    </div>
    <div>
      <label for="quick-draft-category">Category:</label>
      <?php wp_dropdown_categories( array( 'name' => 'category' ) ); ?>
    </div>
    <div>
      <label for="quick-draft-tags">Tags:</label>
      <input type="text" id="quick-draft-tags" name="tags">
    </div>
    <div>
      <label for="quick-draft-featured-image">Featured Image:</label>
      <input type="file" id="quick-draft-featured-image" name="featured_image">
    </div>
    <?php wp_nonce_field( 'quick_draft', 'quick_draft_nonce' ); ?>
    <input type="submit" value="Save Draft">
  </form>
  <?php
}


function save_quick_draft() {
  // Check if the form was submitted and the nonce is valid
  if ( isset( $_POST['quick_draft_nonce'] ) && wp_verify_nonce( $_POST['quick_draft_nonce'], 'quick_draft' ) ) {
    // Get the submitted form data
    $title = sanitize_text_field( $_POST['title'] );
    $content = wp_kses_post( $_POST['content'] );
    $category = (int) $_POST['category'];
    $tags = sanitize_text_field( $_POST['tags'] );
    
    // Create the new post draft
    $post_id = wp_insert_post( array(
      'post_title' => $title,
      'post_content' => $content,
      'post_category' => array( $category ),
      'tags_input' => $tags,
      'post_status' => 'draft'
    ) );
    
    // Upload and set the featured image, if provided
    if ( isset( $_FILES['featured_image'] ) && ! empty( $_FILES['featured_image']['tmp_name'] ) ) {
      require_once ABSPATH . 'wp-admin/includes/image.php';
      
      $attachment_id = media_handle_upload( 'featured_image', $post_id );
      set_post_thumbnail( $post_id, $attachment_id );
    }
  }
