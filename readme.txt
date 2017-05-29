=== Bookmarks ===
Contributors: davidwilliamson
Tags: bookmarks, links
Tested up to: 4.7
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a custom post type for storing bookmarks with tags. Meant to replace my use
of Pinboard.in for storing bookmarks.

== Description ==
I have wanted to move my bookmarks off of Pinboard for a while and onto my own
WordPress powered site. This plugin creates a custom post type to store
those bookmarks.

=== To Do ===
 * Hum URL Shortener prefix for bookmarks
 * Tab order - Title -> URL -> Content (this is going to require some more JS knowledge)
 * Press This style quick add page
 * Customize Quick Edit to include URL (this is harder than I thought)
 * Require URL when creating a bookmark
 * Modify theme
    - Frontend posting
    - Custom templates
 * Remove dize from things

== Changelog ==

=== 1.2 ===
 * Taxonomy permalinks /bookmarks/tag/blah/
 * Changed text domain to 'bookmarks' (working to change to 'bookmarks' instead of 'dizebookmarks')
 * URL is now stored in 'bookmark_url' instead of '_dizebookmark_url' - requires manually updating database (UPDATE `wp_postmeta` SET `meta_key` = 'bookmark_url' WHERE `wp_postmeta`.`meta_key` = '_dizebookmark_url'; )


=== 1.1 ===
 * Added Dashboard widget to display most recent 5 unread bookmarks.
 * Added link to the admin list screen
 * Changed CPT to 'bookmark' for simplicity sake
 * New bookmarks are Private by default

=== 1.0 ===
 * Initial Release
