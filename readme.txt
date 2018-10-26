=== Recommendations TasteDive ===
Contributors: yugensoft
Tags: recommendations, movies, music, tv shows, games, books, authors, tastedive
Requires at least: 4.7
Tested up to: 4.9
Requires PHP: 5.6
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically recommend similar music, movies, TV shows, books and games with TasteDive.

== Description ==

TasteDive is a recommendation service that helps people discover new music, movies, TV shows, books, authors, games based on their existing tastes.

This plugin allows you to insert those recommendations into your blog posts and pages.

More information about TasteDive:
https://tastedive.com/read/about

== Installation ==

You will need an API key from TasteDive to use this plugin.

1. Create an account on TasteDive: https://tastedive.com/account/signup
1. Request an API access key: https://tastedive.com/account/api_access
1. In your admin dashboard, go to Settings -> TasteDive, and set the API key.
1. You can also modify the other settings to your taste.

To insert recommendations into your post or page:

1. Use the shortcode as follows: [tastedive search="the name"]
1. You can also set the following shortcode attributes: *count* and *type*.
1. *Count* is the number of recommendations to display. *Type* is the type of results to return (default if not set is all); you can see the options for this attribute at: https://tastedive.com/read/api
1. Full example: [tastedive search="the matrix" count=2 type="movie"]

== Screenshots ==

1. Example recommendation for a movie
