<?php
namespace Davidized;

class Bookmarks_ToRead_Widget {

    private $query;

    function __construct() {

        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );

    }

    function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'bookmarks_toread_dashboard_widget',
            __( 'Bookmarks To Read', 'bookmarks' ),
            array( $this, 'dashboard_widget_display' )
        );
    }

    function dashboard_widget_display() {

        $posts = $this->get_unread_bookmarks();

        if ( $posts->have_posts() ) :
            echo '<ul>';
            while ( $posts->have_posts() ) :
                $posts->the_post();

                echo '<li>';
                printf( '<a href="%1$s">%2$s</a>', get_post_meta( get_the_ID(), 'bookmark_url', true ), get_the_title() );
                edit_post_link( '<span class="screen-reader-text">Edit Bookmark</span>', '', '', get_the_ID(), 'dashicons dashicons-edit' );
                echo '</li>';

            endwhile;
            echo '</ul>';
        else :
            echo "No posts";
        endif;
    }

    function get_unread_bookmarks( ) {
        $args = array(
        	'post_type'              => array( 'bookmark' ),
            'posts_per_page'         => '5',
        	'order'                  => 'DESC',
        	'orderby'                => 'date',
        	'tax_query'              => array(
        		'relation' => 'AND',
        		array(
        			'taxonomy'         => 'bookmark_tags',
        			'terms'            => 'to-read',
        			'field'            => 'slug',
        			'operator'         => 'IN',
        		),
        	),
        );

        $this->query = new \WP_Query( $args );

        return $this->query;
    }

}

new \Davidized\Bookmarks_ToRead_Widget;
