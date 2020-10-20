<?php
/**
 * Purpose:       Child theme’s "functions.php"
 * Courtesy:      WordPress.org  <https://developer.wordpress.org/themes/advanced-topics/child-themes/>
 * Date:          2020-05-26T1724+0200
 * Last modified: 2020-10-19T0201+0200
 */
defined( 'ABSPATH' ) or die( nl2br( "\r\n\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;en:&nbsp;&nbsp;&nbsp;&nbsp;This PHP file cannot be displayed in the browser.\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;For a quick look, please open this content as a plain text file if there is any with the same name.\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You may also wish to download the target and open the file in a text editor.\r\n\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;fr&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;Ce fichier ne peut pas s'afficher dans le navigateur.\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pour un aperçu du contenu, ouvrez s.v.p. le fichier texte de même nom s’il existe.\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vous pouvez aussi télécharger la cible du lien et ouvrir le fichier dans votre éditeur de texte." ) );

/**
 * Versioning style sheets
 *
 * For browsers to reload stylesheets, the WP version number
 * needs to be incremented, so we need to append a build number.
 * Referring to: wp-includes/version.php(16)
 * See: <https://stackoverflow.com/questions/118884/how-to-force-the-browser-to-reload-cached-css-js-files>
 * We’ll append a BUILD number to:
 * 
 * The WordPress version string.
 *
 * @global string $wp_version
 */
$wp_version .= '.34';

/**
 * Enqueuing style sheets
 *
 * This is from the WordPress child theme enqueuing template.
 * <https://developer.wordpress.org/themes/advanced-topics/child-themes/>
 */
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_styles' );
function custom_theme_enqueue_styles() {
  $parenthandle = 'catch-everest'; // This is the only field to edit in this.
  $theme = wp_get_theme();
  // This enqueues the parent theme configuration files:
  wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css', 
    array(), // If the parent theme code has a dependency, copy it to here.
    $theme->parent()->get('Version') // Parsed in "style.css" header.
  );
  // This enqueues the child or custom theme configuration files:
  wp_enqueue_style( $theme, get_stylesheet_uri(),
    array( $parenthandle ),
    $theme->get('Version') // Needs Version in "style.css" header to work.
  );
}
load_theme_textdomain( 'catch-everest-custom', get_template_directory().'/languages' );

/**
 * Disable wptexturize
 * 
 * With complete keyboard layouts in use, guessing typographic quotes
 * from generic quotes is no longer needed, and these can be used 
 * without marking them up as code.
 * 
 * <https://www.thewebflash.com/how-to-properly-disable-wptexturize-in-wordpress/>
 * 2020-08-27T2234+0200
 */
add_filter( 'run_wptexturize', '__return_false' );

/**
 * Add slug as a class to the body element
 * <https://www.wpbeginner.com/wp-themes/how-to-add-page-slug-in-body-class-of-your-wordpress-themes/>
 * 2020-09-05T1704+0200
 */
function add_slug_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
    $classes[] = $post->post_name;
  }
  return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );

/**
 * Add Theme Support for wide and full-width images.
 *
 * Add this to your theme's functions.php, or wherever else 
 * you are adding your add_theme_support() functions.
 * 
 * Courtesy: John Regan, How to add full width images in Gutenberg
 * <https://johnregan3.wordpress.com/2018/12/19/how-to-add-full-width-images-in-gutenberg/>
 * 
 * @action after_setup_theme
 */
function jr3_theme_setup() {
  add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'jr3_theme_setup' );

/**
 * Add last modified date in posts and pages
 * 
 * Courtesy: Shaun Quarton, How to Display when a Post was Last Updated in WordPress, 07/04/2019.
 * <https://pagely.com/blog/display-post-last-updated-wordpress/>
 */
function display_date_metadata( $content ) {
  $id = get_the_id();
  $current_content_type = get_post_type( $id );  // whether it’s a post or a page.
  $post_page_url = get_permalink( $id );  // published dates often have a url hyperlink.
  //$original_time = get_the_time( 'U' );  // raw time for comparison, not needed.
  //$modified_time = get_the_modified_time( 'U' );  // modified times are provided without delay.
  //if ( $modified_time >= $original_time + 86400 ) {
  $updated_time = get_the_modified_time('H:i');  // time for the tooltip.
  $updated_day = get_the_modified_time('d/m/Y');  // French numeric date format.
  $updated_iso8601 = get_the_modified_time( 'Y-m-d\TH:i:sO' ); // for the time argument.
  $date_tag_content = "\r\n\r\n<p class=\"$current_content_type-last-modified\">Mis à jour le <a href=\"$post_page_url\" time=\"$updated_iso8601\" title=\"$updated_time\">$updated_day</a>";
  //}
  if ( $current_content_type == 'post' ) {  // posts’ published date is already present.
    $date_tag_content .= "</p>\r\n\r\n";  // close the tag.
    $content = $date_tag_content . $content;  // modified date inserted before content.
  }
  else if ( $current_content_type == 'page' ) {  // pages are lacking the published date.
    $published_time = get_the_time('H:i');
    $published_day = get_the_time('d/m/Y');
    $published_iso8601 = get_the_time( 'Y-m-d\TH:i:sO' );
    $date_tag_content .= "<br />\r\nPublié le <a href=\"$post_page_url\" time=\"$published_iso8601\" title=\"$published_time\">$published_day</a></p>\r\n\r\n";
    $content .= $date_tag_content;  // both dates are appended below.
  }
  return $content;
}
add_filter( 'the_content', 'display_date_metadata' );

/**
 * Add published and last modified date meta tags
 * 
 * WordPress doesn’t routinely insert the published and last modified dates,
 * with respect to some authors who do not wish to disclose this information.
 * That backfires when it comes to bibliography. Software grabs metadata from
 * the page head, and citation styles replace missing dates with "(n.d.)".
 * 
 * This code goes into the child theme’s functions.php.
 * 
 * Courtesy: Aurovrata Venet, Re: wp_head, Code Reference WordPress.org, 2017
 * <https://developer.wordpress.org/reference/hooks/wp_head/>
 * the_modified_date(), Code Reference, Source
 * <https://developer.wordpress.org/reference/functions/the_modified_date/#source>
 */
function add_date_meta_tags() {
  echo "\r\n\r\n";
  ?>
  <meta name="date" content="<?php printf( __( '%s', 'textdomain' ), get_the_date( 'Y-m-d\TH:i:sO' ) ); ?>" />
  <meta name="last-modified" content="<?php printf( __( '%s', 'textdomain' ), get_the_modified_date( 'Y-m-d\TH:i:sO' ) ); ?>" />
  <?php
}
add_action('wp_head', 'add_date_meta_tags');
