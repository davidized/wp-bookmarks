<?php
/**
 * Plugin Name: Bookmarks
 * Description: Adds a custom post type for storing bookmarks with tags. Meant to replace my use of Pinboard.in for storing bookmarks.
 * Version: 1.3
 * Author: David Williamson
 * Author URI: https://davidized.com/
 * Text Domain: bookmarks
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
            $instance->includes();
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

        $this->version = '1.3';

        /** Paths *************************************************************/
        // Base name
        $this->file             = __FILE__;

        // Path and URL
        $this->plugin_dir       = plugin_dir_path( $this->file );
        $this->plugin_url       = plugin_dir_url( $this->file );

        $this->admin_css        = $this->plugin_dir_url . 'admin.css';
    }

    private function includes() {
        require_once( $this->plugin_dir . 'includes/class-bookmarks-toread-widget.php' );
    }

    private function add_actions() {

            add_action( 'init', array( $this, 'register_post_type' ), 1 );
            add_action( 'init', array( $this, 'register_taxonomy'), 0 );
            add_action( 'init', array( $this, 'register_meta' ), 0 );
            add_action( 'init', array( $this, 'load_textdomain' ) );

            add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ), 10, 1 );
            add_action( 'save_post_bookmark', array( $this, 'save_bookmark' ), 10, 3 );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

            add_filter( 'dashboard_glance_items', array( $this, 'custom_glance_items' ), 10, 1 );

            add_filter( 'manage_edit-bookmark_columns', array( $this, 'add_list_column' ) );
            add_action( 'manage_bookmark_posts_custom_column', array( $this, 'custom_column_output' ), 10, 2 );

            add_action( 'post_submitbox_misc_actions' , array( $this, 'change_visibility_metabox_value' ) );

    }

    /** Public Methods ********************************************************/

    public function load_textdomain() {
        load_plugin_textdomain( 'bookmarks' );
    }

    public function register_post_type() {

    	$labels = array(
    		'name'                  => _x( 'Bookmarks', 'Post Type General Name', 'bookmarks' ),
    		'singular_name'         => _x( 'Bookmark', 'Post Type Singular Name', 'bookmarks' ),
    		'menu_name'             => __( 'Bookmarks', 'bookmarks' ),
    		'name_admin_bar'        => __( 'Bookmark', 'bookmarks' ),
    		'archives'              => __( 'Bookmark Archives', 'bookmarks' ),
    		'attributes'            => __( 'Bookmark Attributes', 'bookmarks' ),
    		'parent_item_colon'     => __( 'Parent Bookmark:', 'bookmarks' ),
    		'all_items'             => __( 'All Bookmarks', 'bookmarks' ),
    		'add_new_item'          => __( 'Add New Bookmark', 'bookmarks' ),
    		'add_new'               => __( 'Add New', 'bookmarks' ),
    		'new_item'              => __( 'New Bookmark', 'bookmarks' ),
    		'edit_item'             => __( 'Edit Bookmark', 'bookmarks' ),
    		'update_item'           => __( 'Update Bookmark', 'bookmarks' ),
    		'view_item'             => __( 'View Bookmark', 'bookmarks' ),
    		'view_items'            => __( 'View Bookmarks', 'bookmarks' ),
    		'search_items'          => __( 'Search Bookmark', 'bookmarks' ),
    		'not_found'             => __( 'Not found', 'bookmarks' ),
    		'not_found_in_trash'    => __( 'Not found in Trash', 'bookmarks' ),
    		'featured_image'        => __( 'Featured Image', 'bookmarks' ),
    		'set_featured_image'    => __( 'Set featured image', 'bookmarks' ),
    		'remove_featured_image' => __( 'Remove featured image', 'bookmarks' ),
    		'use_featured_image'    => __( 'Use as featured image', 'bookmarks' ),
    		'insert_into_item'      => __( 'Insert into ', 'bookmarks' ),
    		'uploaded_to_this_item' => __( 'Uploaded to this ', 'bookmarks' ),
    		'items_list'            => __( 'Bookmarks list', 'bookmarks' ),
    		'items_list_navigation' => __( 'Bookmarks list navigation', 'bookmarks' ),
    		'filter_items_list'     => __( 'Filter bookmarks list', 'bookmarks' ),
    	);
    	$args = array(
    		'label'                 => __( 'Bookmark', 'bookmarks' ),
    		'description'           => __( 'Bookmark', 'bookmarks' ),
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
    		'has_archive'           => 'bookmarks',
    		'exclude_from_search'   => false,
    		'publicly_queryable'    => true,
    		'capability_type'       => 'post',
    		'show_in_rest'          => true,
    	);
    	register_post_type( 'bookmark', $args );

    }

    public function register_taxonomy() {

    	$labels = array(
    		'name'                       => _x( 'Bookmark Tags', 'Taxonomy General Name', 'bookmarks' ),
    		'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'bookmarks' ),
    		'menu_name'                  => __( 'Tags', 'bookmarks' ),
    		'all_items'                  => __( 'All Tags', 'bookmarks' ),
    		'parent_item'                => __( 'Parent Tag', 'bookmarks' ),
    		'parent_item_colon'          => __( 'Parent Tag:', 'bookmarks' ),
    		'new_item_name'              => __( 'New Tag Name', 'bookmarks' ),
    		'add_new_item'               => __( 'Add New Tag', 'bookmarks' ),
    		'edit_item'                  => __( 'Edit Tag', 'bookmarks' ),
    		'update_item'                => __( 'Update Tag', 'bookmarks' ),
    		'view_item'                  => __( 'View Tag', 'bookmarks' ),
    		'separate_items_with_commas' => __( 'Separate Tags with commas', 'bookmarks' ),
    		'add_or_remove_items'        => __( 'Add or remove tags', 'bookmarks' ),
    		'choose_from_most_used'      => __( 'Choose from the most used', 'bookmarks' ),
    		'popular_items'              => __( 'Popular Tags', 'bookmarks' ),
    		'search_items'               => __( 'Search Tags', 'bookmarks' ),
    		'not_found'                  => __( 'Not Found', 'bookmarks' ),
    		'no_terms'                   => __( 'No tags', 'bookmarks' ),
    		'items_list'                 => __( 'Tags list', 'bookmarks' ),
    		'items_list_navigation'      => __( 'Tags list navigation', 'bookmarks' ),
    	);
        $rewrite = array(
            'slug'                       => 'bookmarks/tag',
            'with_front'                 => false,
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
            'update_count_callback'      => 'wp_update_term_count_now',
            'rewrite'                    => $rewrite,
    	);
    	register_taxonomy( 'bookmark_tags', array( 'bookmark' ), $args );

    }

    public function register_meta() {
        register_meta( 'post', 'bookmark', array(
            'type' => 'string',
            'description' => __( 'URL for ', 'bookmarks' ),
            'single' => true,
            'sanitize_callback' => 'esc_url',
            'show_in_rest' => true,
        ) );
    }

    public function admin_enqueue_scripts() {

        $current_screen = get_current_screen();

        if ( 'bookmark' == $current_screen->post_type ) {
            wp_enqueue_script( 'dizebookmarks-admin', $this->plugin_url . 'admin.js', array( 'jquery' ) );
            wp_enqueue_style( 'dizebookmarks-admin', $this->plugin_url . 'admin.css' );
        }

        wp_enqueue_style( 'dizebookmarks-admin-dashboard', $this->plugin_url . 'admin-dashboard.css' );

    }

    function change_visibility_metabox_value(){
        global $post;
        if ( $post->post_type != 'bookmark' )
            return;
        $post->post_password = '';
        $visibility = 'private';
        $visibility_trans = __( 'Private', 'bookmarks' );
        ?>
        <script type="text/javascript">
            (function($){
                try {
                    $('#post-visibility-display').text('<?php echo $visibility_trans; ?>');
                    $('#hidden-post-visibility').val('<?php echo $visibility; ?>');
                    $('#visibility-radio-<?php echo $visibility; ?>').attr('checked', true);
                } catch(err){}
            }) (jQuery);
        </script>
        <?php
    }

    public function edit_form_after_title( $post ) {

        $current_screen = get_current_screen();

        if ( 'bookmark' == $current_screen->post_type ) {
            $bookmark_url = get_post_meta( $post->ID, 'bookmark_url', true );
            $label_class = ! empty( $bookmark_url ) ? 'screen-reader-text' : '';
            ?>
            <div id="bookmark_urlwrap">
                <label id="url-prompt-text" class="<?php echo $label_class; ?>" for="url">Enter url here</label>
                <input id="url" name="bookmark_url" id="bookmark_url" size="30" value="<?php echo $bookmark_url; ?>" spellcheck="false" autocomplete="off" type="text">
                <?php wp_nonce_field( 'bookmark_save_url', '_bookmark_nonce' ); ?>
            </div>
            <?php
        }
    }

    public function save_bookmark( $post_id, $post, $update ) {

        if ( ! isset( $_POST['_bookmark_nonce'] )
            || ! wp_verify_nonce( $_POST['_bookmark_nonce'], 'bookmark_save_url' ) ) {
            return $post_id;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        update_post_meta( $post_id, 'bookmark_url', $_POST['bookmark_url'] );

    }

    function custom_glance_items( $items = array() ) {

        $type = 'bookmark';

        $num_posts = wp_count_posts( $type );

        if( $num_posts ) {
            $published = intval( $num_posts->publish );
            $private = intval( $num_posts->private );
            $total = $published + $private;
            $post_type = get_post_type_object( $type );
            $text = _n( '%s Bookmark', '%s Bookmarks', $total, 'bookmarks' );
            $text = sprintf( $text, number_format_i18n( $total ) );
            if ( current_user_can( $post_type->cap->edit_posts ) ) {
                $items[] = sprintf( '<span class="bookmark-count">%2$s</span>', $type, $text ) . "\n";
            } else {
                $items[] = sprintf( '<a href="edit.php?post_type=bookmark" class="bookmark-count">%s</a>', $text );
            }
        }
        return $items;
    }

    function add_list_column( $columns ) {

        do_action( 'add_debug_info', $columns, 'Columns' );

        $new_columns = array(
            'cb' => $columns['cb'],
            'bookmark_link' => __( 'Link', 'bookmarks' ),
            'title' => $columns['title'],
            'taxonomy-bookmark_tags' => $columns['taxonomy-bookmark_tags'],
            'comments' => $columns['comments'],
            'date' => $columns['date']
        );
        return $new_columns;
    }

    function custom_column_output( $colname, $cptid ) {

        if ( 'bookmark_link' == $colname ) {
            $link_url = get_post_meta( $cptid, 'bookmark_url', true );
            printf( '<a href="%s"><span class="dashicons dashicons-admin-links"></span></a>', $link_url );
        }
    }

}

function dizebookmarks() {
    return \Davidized\Bookmarks_Plugin::instance();
}

dizebookmarks();
