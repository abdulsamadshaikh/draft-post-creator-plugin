<?php
/**
 * Plugin Name: Draft Post Creator
 * Description: Creates draft posts via a POST request with SEO meta fields and featured image.
 * Version: 1.1
 * Author: Abdul Samad
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Register REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('draft-post-creator/v1', '/create-post', array(
        'methods' => 'POST',
        'callback' => 'dpc_create_draft_post',
        'permission_callback' => '__return_true',
    ));
});

// Creates a draft post with meta fields, sets a featured image, and supports REST API requests
if ( ! function_exists('dpc_create_draft_post') ) {
    function dpc_create_draft_post($request) {
        $token = 'hardcoded_token_here';
        $auth_header = $request->get_header('authorization');

        // Authorization Check
        if (strpos($auth_header, 'Bearer ') !== 0 || substr($auth_header, 7) !== $token) {
            return new WP_REST_Response(['error' => 'Unauthorized'], 401);
        }

        $params = $request->get_json_params();

        // Validate Required Fields
        $required_fields = ['title', 'description', 'meta_title', 'meta_keywords', 'meta_description', 'img_url'];
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new WP_REST_Response(['error' => "Missing field: $field"], 400);
            }
        }

        // Image URL Validation Using wp_remote_head
        $image_url = esc_url_raw($params['img_url']);
        $response = wp_remote_head($image_url, ['redirection' => 5]); // Allows up to 5 redirects

        if (is_wp_error($response)) {
            return new WP_REST_Response(['error' => 'Invalid image URL.'], 400);
        }

        // Check for HTTP 200 OK
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_REST_Response(['error' => 'Image URL returned status code ' . $response_code], 400);
        }

        // Check if the Content-Type is an image
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if (strpos($content_type, 'image') === false) {
            return new WP_REST_Response(['error' => 'URL does not point to a valid image.'], 400);
        }

        // Proceed to create the post after validation
        $post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($params['title']),
            'post_content' => wp_kses_post($params['description']),
            'post_status'  => 'draft',
            'post_type'    => 'post'
        ]);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(['error' => 'Post creation failed'], 500);
        }

        // Store Meta Fields
        update_post_meta($post_id, 'meta_title', sanitize_text_field($params['meta_title']));
        update_post_meta($post_id, 'meta_keywords', sanitize_text_field($params['meta_keywords']));
        update_post_meta($post_id, 'meta_description', sanitize_text_field($params['meta_description']));

        // Upload and Set Featured Image
        $image_id = dpc_upload_image($image_url, $post_id);
        if ($image_id) {
            set_post_thumbnail($post_id, $image_id);
        } else {
            return new WP_REST_Response(['error' => 'Image upload failed'], 500);
        }

        return new WP_REST_Response(['success' => 'Post created successfully', 'post_id' => $post_id], 200);
    }
}

// Downloads an image from a remote URL and uploads it to the WordPress Media Library 
if ( ! function_exists('dpc_upload_image') ) {
    function dpc_upload_image($image_url, $post_id) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $tmp = download_url($image_url);

        if (is_wp_error($tmp)) {
            return false;
        }

        $file = [
            'name'     => basename($image_url),
            'tmp_name' => $tmp
        ];

        $attachment_id = media_handle_sideload($file, $post_id);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            return false;
        }

        return $attachment_id;
    }
}