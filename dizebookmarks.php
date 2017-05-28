<?php
/**
 * Plugin Name: Davidized Bookmarks
 * Description: Adds a custom post type for storing bookmarks with tags. Meant to replace my use of Pinboard.in for storing bookmarks.
 * Version: 1.0
 * Author: David Williamson
 * Author URI: https://davidized.com/
 * Text Domain: dizebookmarks
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * Copyright (C) 2017 David Williamson
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace Davidized;

defined( 'ABSPATH' ) || exit;

class Bookmarks_Plugin {

    private $data;

    public static function instance() {
        static $instance = null;

        if ( null === $instance ) {
            $instance = new \Davidized\Bookmarks_Plugin;
            $instance->setup_globals();
            $instance->add_actions();
        }

        return $instance;
    }

    /** Magic Methods *********************************************************/

    /**
     * A dummy constructor to prevent Bookmarks_Plugin from being loaded more than once.
     *
     * @see \Davidized\Bookmarks_Plugin::instance()
     * @see metrics();
     */
    private function __construct() { /* Do nothing here */ }

    /**
     * A dummy magic method to prevent Bookmarks_Plugin from being cloned.
     */
    public function __clone() { /* Do nothing here */ }

    /**
     * A dummy magic method to prevent Bookmarks_Plugin from being unserialized.
     */
    public function __wakeup() { /* Do nothing here */ }

    /**
     * Magic method for checking the existence of a certain custom field
     */
    public function __isset( $key ) { return isset( $this->data[ $key ] ); }

    /**
     * Magic method for getting Bookmarks_Plugin variables
     */
    public function __get( $key ) { return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null; }

    /**
     * Magic method for setting Bookmarks_Plugin variables
     */
    public function __set( $key, $value ) { $this->data[ $key ] = $value; }

    /**
     * Magic method for unsetting Bookmarks_Plugin variables
     */
    public function __unset( $key ) {
        if ( isset( $this->data[ $key ] ) ) {
            unset( $this->data[ $key ] );
        }
    }

    /**
     * Magic method to prevent notices and errors from invalid method calls
     */
    public function __call( $name = '', $args = array() ) { unset( $name, $args); return null; }

    /** Private Methods *******************************************************/

    private function setup_globals() {

        $this->version = '1.0';

        /** Paths *************************************************************/
        // Base name
        $this->file             = __FILE__;

        // Path and URL
        $this->plugin_dir       = plugin_dir_path( $this->file );
        $this->plugin_url       = plugin_dir_url( $this->file );

        $this->admin_css        = $this->plugin_dir_url . 'admin.css';
    }

    private function add_actions() {

            add_action( 'init', array( $this, 'register_post_type' ), 0 );
            add_action( 'init', array( $this, 'register_taxonomy'), 0 );
            add_action( 'init', array( $this, 'register_meta' ), 0 );

            add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ), 10, 1 );
            add_action( 'save_post_dizebookmark', array( $this, 'save_bookmark' ), 10, 3 );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

            add_filter( 'dashboard_glance_items', array( $this, 'custom_glance_items' ), 10, 1 );

    }

    /** Public Methods ********************************************************/

    public function register_post_type() {

    	$labels = array(
    		'name'                  => _x( 'Bookmarks', 'Post Type General Name', 'dizebookmarks' ),
    		'singular_name'         => _x( 'Bookmark', 'Post Type Singular Name', 'dizebookmarks' ),
    		'menu_name'             => __( 'Bookmarks', 'dizebookmarks' ),
    		'name_admin_bar'        => __( 'Bookmark', 'dizebookmarks' ),
    		'archives'              => __( 'Bookmark Archives', 'dizebookmarks' ),
    		'attributes'            => __( 'Bookmark Attributes', 'dizebookmarks' ),
    		'parent_item_colon'     => __( 'Parent Bookmark:', 'dizebookmarks' ),
    		'all_items'             => __( 'All Bookmarks', 'dizebookmarks' ),
    		'add_new_item'          => __( 'Add New Bookmark', 'dizebookmarks' ),
    		'add_new'               => __( 'Add New', 'dizebookmarks' ),
    		'new_item'              => __( 'New Bookmark', 'dizebookmarks' ),
    		'edit_item'             => __( 'Edit Bookmark', 'dizebookmarks' ),
    		'update_item'           => __( 'Update Bookmark', 'dizebookmarks' ),
    		'view_item'             => __( 'View Bookmark', 'dizebookmarks' ),
    		'view_items'            => __( 'View Bookmarks', 'dizebookmarks' ),
    		'search_items'          => __( 'Search Bookmark', 'dizebookmarks' ),
    		'not_found'             => __( 'Not found', 'dizebookmarks' ),
    		'not_found_in_trash'    => __( 'Not found in Trash', 'dizebookmarks' ),
    		'featured_image'        => __( 'Featured Image', 'dizebookmarks' ),
    		'set_featured_image'    => __( 'Set featured image', 'dizebookmarks' ),
    		'remove_featured_image' => __( 'Remove featured image', 'dizebookmarks' ),
    		'use_featured_image'    => __( 'Use as featured image', 'dizebookmarks' ),
    		'insert_into_item'      => __( 'Insert into bookmark', 'dizebookmarks' ),
    		'uploaded_to_this_item' => __( 'Uploaded to this bookmark', 'dizebookmarks' ),
    		'items_list'            => __( 'Bookmarks list', 'dizebookmarks' ),
    		'items_list_navigation' => __( 'Bookmarks list navigation', 'dizebookmarks' ),
    		'filter_items_list'     => __( 'Filter bookmarks list', 'dizebookmarks' ),
    	);
    	$args = array(
    		'label'                 => __( 'Bookmark', 'dizebookmarks' ),
    		'description'           => __( 'Bookmark', 'dizebookmarks' ),
    		'labels'                => $labels,
    		'supports'              => array( 'title', 'editor', 'comments' ),
    		'hierarchical'          => false,
    		'public'                => true,
    		'show_ui'               => true,
    		'show_in_menu'          => true,
    		'menu_position'         => 25,
    		'menu_icon'             => 'dashicons-admin-links',
    		'show_in_admin_bar'     => true,
    		'show_in_nav_menus'     => true,
    		'can_export'            => true,
    		'has_archive'           => true,
    		'exclude_from_search'   => false,
    		'publicly_queryable'    => true,
    		'capability_type'       => 'post',
    		'show_in_rest'          => true,
    	);
    	register_post_type( 'dizebookmark', $args );

    }

    public function register_taxonomy() {

    	$labels = array(
    		'name'                       => _x( 'Tags', 'Taxonomy General Name', 'dizebookmarks' ),
    		'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'dizebookmarks' ),
    		'menu_name'                  => __( 'Tags', 'dizebookmarks' ),
    		'all_items'                  => __( 'All Tags', 'dizebookmarks' ),
    		'parent_item'                => __( 'Parent Tag', 'dizebookmarks' ),
    		'parent_item_colon'          => __( 'Parent Tag:', 'dizebookmarks' ),
    		'new_item_name'              => __( 'New Tag Name', 'dizebookmarks' ),
    		'add_new_item'               => __( 'Add New Tag', 'dizebookmarks' ),
    		'edit_item'                  => __( 'Edit Tag', 'dizebookmarks' ),
    		'update_item'                => __( 'Update Tag', 'dizebookmarks' ),
    		'view_item'                  => __( 'View Tag', 'dizebookmarks' ),
    		'separate_items_with_commas' => __( 'Separate Tags with commas', 'dizebookmarks' ),
    		'add_or_remove_items'        => __( 'Add or remove tags', 'dizebookmarks' ),
    		'choose_from_most_used'      => __( 'Choose from the most used', 'dizebookmarks' ),
    		'popular_items'              => __( 'Popular Tags', 'dizebookmarks' ),
    		'search_items'               => __( 'Search Tags', 'dizebookmarks' ),
    		'not_found'                  => __( 'Not Found', 'dizebookmarks' ),
    		'no_terms'                   => __( 'No tags', 'dizebookmarks' ),
    		'items_list'                 => __( 'Tags list', 'dizebookmarks' ),
    		'items_list_navigation'      => __( 'Tags list navigation', 'dizebookmarks' ),
    	);
    	$args = array(
    		'labels'                     => $labels,
    		'hierarchical'               => false,
    		'public'                     => true,
    		'show_ui'                    => true,
    		'show_admin_column'          => true,
    		'show_in_nav_menus'          => true,
    		'show_tagcloud'              => true,
    		'show_in_rest'               => true,
    	);
    	register_taxonomy( 'bookmark_tags', array( 'dizebookmark' ), $args );

    }

    public function register_meta() {
        register_meta( 'post', array(
            'type' => 'string',
            'description' => __( 'URL for bookmark', 'dizebookmarks' ),
            'single' => true,
            'sanitize_callback' => 'esc_url',
            'show_in_rest' => true,
        ) );
    }

    public function admin_enqueue_scripts() {

        $current_screen = get_current_screen();

        if ( 'dizebookmark' == $current_screen->post_type ) {
            wp_enqueue_script( 'dizebookmarks-admin', $this->plugin_url . 'admin.js', array( 'jquery' ) );
            wp_enqueue_style( 'dizebookmarks-admin', $this->plugin_url . 'admin.css' );
        }

        wp_enqueue_style( 'dizebookmarks-admin-dashboard', $this->plugin_url . 'admin-dashboard.css' );

    }

    public function edit_form_after_title( $post ) {

        $bookmark_url = get_post_meta( $post->ID, '_dizebookmark_url', true );
        $label_class = ! empty( $bookmark_url ) ? 'screen-reader-text' : '';
        ?>
        <div id="dizebookmark_urlwrap">
            <label id="url-prompt-text" class="<?php echo $label_class; ?>" for="url">Enter url here</label>
            <input id="url" name="dizebookmark_url" size="30" value="<?php echo $bookmark_url; ?>" spellcheck="false" autocomplete="off" type="text">
            <?php wp_nonce_field( 'dizebookmark_save_url', '_dizebookmark_nonce' ); ?>
        </div>
        <?php
    }

    public function save_bookmark( $post_id, $post, $update ) {

        if ( ! isset( $_POST['_dizebookmark_nonce'] )
            || ! wp_verify_nonce( $_POST['_dizebookmark_nonce'], 'dizebookmark_save_url' ) ) {
            return $post_id;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        update_post_meta( $post_id, '_dizebookmark_url', $_POST['dizebookmark_url'] );

    }

    function custom_glance_items( $items = array() ) {

        $num_posts = wp_count_posts( 'dizebookmark' );

        if( $num_posts ) {
            $published = intval( $num_posts->publish );
            $post_type = get_post_type_object( $type );
            $text = _n( '%s Bookmark', '%s Bookmarks', $published, 'dizebookmarks' );
            $text = sprintf( $text, number_format_i18n( $published ) );
            if ( current_user_can( $post_type->cap->edit_posts ) ) {
                $items[] = sprintf( '<span class="dizebookmark-count">%2$s</span>', $type, $text ) . "\n";
            } else {
                $items[] = sprintf( '<a href="edit.php?post_type=dizebookmark" class="dizebookmark-count">%s</a>', $text );
            }
        }
        return $items;
    }

}

function dizebookmarks() {
    return \Davidized\Bookmarks_Plugin::instance();
}

dizebookmarks();
