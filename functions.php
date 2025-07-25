<?php

// This adds a query var!
function universityQueryVars($vars)
{
  $vars[] = 'skyColor';
  $vars[] = 'grassColor';
  return $vars;
}

add_filter('query_vars', 'universityQueryVars');


require get_theme_file_path('/inc/like-route.php');
require get_theme_file_path('/inc/search-route.php');

function university_custom_rest()
{   // To add a new propertyname (API): 'author name' 
  register_rest_field('post', 'authorName', array(   //1. post type 2. name field 3.how to manage field.
    'get_callback' => function () {
      return get_the_author();
    }
  ));
  register_rest_field('note', 'userNoteCount', array(
    'get_callback' => function () {
      return count_user_posts(get_current_user_id(), 'note');
    }
  ));
}
add_action('rest_api_init', 'university_custom_rest');

// This function calls a wordpress function that points to the css files it wants to to load
// NULL gets rid of err msg, means argument is optional
function pageBanner($args = NULL)
{

  if (!isset($args['title'])) {
    $args['title'] = get_the_title();
  }

  if (!isset($args['subtitle'])) {
    $args['subtitle'] = get_field('page_banner_subtitle');
  }

  if (!isset($args['photo'])) {
    if (get_field('page_banner_background_image') and !is_archive() and !is_home()) {
      $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
    } else {
      $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
    }
  }
  ?>

  <div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);">
    </div>
    <div class="page-banner__content container container--narrow">
      <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
      <div class="page-banner__intro">
        <p><?php echo $args['subtitle']; ?></p>
      </div>
    </div>
  </div>

<?php }

function university_files()
{
  wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
  wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
  wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
  wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
  wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));

  wp_localize_script('main-university-js', 'universityData', array(
    'root_url' => get_site_url(),
    'nonce' => wp_create_nonce('wp_rest') //to delete a note
  ));
}

add_action('wp_enqueue_scripts', 'university_files');

function university_features()
{
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails'); // to support images
  add_image_size('professorLandscape', 400, 260, true);
  add_image_size('professorPortrait', 480, 650, true);
  add_image_size('pageBanner', 1500, 350, true);
}

// Add new menu locations -> 'after_setup_theme'
add_action('after_setup_theme', 'university_features');

function university_adjust_queries($query)
{
  if (!is_admin() and is_post_type_archive('program') and is_main_query()) {
    $query->set('orderby', 'title');
    $query->set('order', 'ASC');
    $query->set('posts_per_page', -1);
  }
  if (!is_admin() and is_post_type_archive('event') and $query->is_main_query()) {
    $today = date('Ymd');
    $query->set('meta_key', 'event_date');
    $query->set('orderby', 'meta_value_num');
    $query->set('order', 'ASC');
    $query->set('meta_query', array(
      array(
        'key' => 'event_date',
        'compare' => '>=',
        'value' => $today,
        'type' => 'numeric'
      )
    ));
  }
}
add_action('pre_get_posts', 'university_adjust_queries');

// Redirect subscriber accounts of of admin and onto homepage
function redirectSubsToFrontend()
{
  $currentUser = wp_get_current_user();
  if (count($currentUser->roles) == 1 and $currentUser->roles[0] == 'subscriber') {
    wp_redirect(site_url('/'));
    exit;
  }
}

add_action('admin_init', 'redirectSubsToFrontend');

// Hiding top admin bar for subscribers
function noSubsAdminBar()
{
  $currentUser = wp_get_current_user();
  if (count($currentUser->roles) == 1 and $currentUser->roles[0] == 'subscriber') {
    show_admin_bar(false);
  }
}

add_action('wp_loaded', 'noSubsAdminBar');

// Customize login screen (the image that redirects to wordpress.org)
add_filter('login_headerurl', 'ourHeaderUrl');
function ourHeaderUrl()
{
  return esc_url(site_url('/'));
}

// Change the style login page (go to modules --> select login.css)
function ourLoginCSS()
{
  wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
  wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
  wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
  wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
}
add_action('login_enqueue_scripts', 'ourLoginCSS');

//Change the title
function ourLoginTitle()
{
  return get_bloginfo('name');
}
add_filter('login_headertitle', 'ourLoginTitle');

// Force note posts to be private
function makeNotePrivate($data, $postarr)
{
  //intercept a request before sending data to db (security), strip it of html and then  send
  if ($data['post_type'] == 'note') {
    // cant make more than 5 notes
    if (count_user_posts(get_current_user_id(), 'note') > 4 and !$postarr['ID']) {
      die("You have reached your note limit.");
    }

    $data['post_content'] = sanitize_textarea_field($data['post_content']);
    $data['post_title'] = sanitize_text_field($data['post_title']);
  }

  if ($data['post_type'] == 'note' and $data['post_status'] != 'trash') {
    $data['post_status'] = "private";
  }
  return $data;
}

add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);