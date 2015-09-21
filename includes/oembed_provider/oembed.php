<?php

add_action('init', array('wpgs_OembedProviderPlugin', 'init'));

/**
 * oEmbed Provider for WordPress
 *
 * @author Matthias Pfefferle
 * @author Craig Andrews
 */
class wpgs_OembedProviderPlugin {
  /**
   * initialize plugin
   */
  public static function init() {
    add_action('wp_head', array('wpgs_OembedProviderPlugin', 'wpgs_add_oembed_links'));
    add_action('parse_query', array('wpgs_OembedProviderPlugin', 'wpgs_parse_query'));
    add_filter('query_vars', array('wpgs_OembedProviderPlugin', 'wpgs_query_vars'));
    add_filter('wpgs_oembed_provider_data', array('wpgs_OembedProviderPlugin', 'wpgs_generate_default_content'), 90, 3);
    add_filter('wpgs_oembed_provider_data_attachment', array('wpgs_OembedProviderPlugin', 'wpgs_generate_attachment_content'), 91, 2);
    add_filter('wpgs_oembed_provider_data_post', array('wpgs_OembedProviderPlugin', 'wpgs_generate_post_content'), 91, 2);
    add_filter('wpgs_oembed_provider_data_page', array('wpgs_OembedProviderPlugin', 'wpgs_generate_post_content'), 91, 2);
    add_action('wpgs_oembed_provider_render_json', array('wpgs_OembedProviderPlugin', 'wpgs_render_json'), 99, 2);
    add_action('wpgs_oembed_provider_render_xml', array('wpgs_OembedProviderPlugin', 'wpgs_render_xml'), 99);
  }

  /**
   * auto discovery links
   */
  public static function wpgs_add_oembed_links() {
    if (is_singular()) {
      echo '<link rel="alternate" type="application/json+oembed" href="' . site_url('/?oembed=true&amp;format=json&amp;url=' . get_permalink()) . '" title="oEmbed Profile" />'."\n";
      echo '<link rel="alternate" type="text/xml+oembed" href="' . site_url('/?oembed=true&amp;format=xml&amp;url=' . get_permalink()) . '" title="oEmbed Profile" />'."\n";
    }
  }

  /**
   * adds query vars
   */
  public static function wpgs_query_vars($query_vars) {
    $query_vars[] = 'oembed';
    $query_vars[] = 'format';
    $query_vars[] = 'url';
    $query_vars[] = 'callback';
    $query_vars[] = 'maxwidth';
    $query_vars[] = 'maxheight';

    return $query_vars;
  }

  /**
   * handles request
   */
  public static function wpgs_parse_query($wp) {
    if (!array_key_exists('oembed', $wp->query_vars) ||
        !array_key_exists('url', $wp->query_vars)) {
      return;
    }

    $post_ID = url_to_postid(apply_filters('oembed_url', $wp->query_vars['url']));
    $post = get_post($post_ID);

    if (!$post) {
      status_header(404);
      wp_die("Not found");
    }

    $post_type = get_post_type($post);

    // add support for alternate output formats
    $oembed_provider_formats = apply_filters("wpgs_oembed_provider_formats", array('json', 'xml'));

    // check output format
    $format = "json";
    if (array_key_exists('format', $wp->query_vars) && in_array(strtolower($wp->query_vars['format']), $oembed_provider_formats)) {
      $format = $wp->query_vars['format'];
    }

    // content filter
    $oembed_provider_data = apply_filters("wpgs_oembed_provider_data", array(), $post_type, $post);
    $oembed_provider_data = apply_filters("wpgs_oembed_provider_data_{$post_type}", $oembed_provider_data, $post);

    do_action("wpgs_oembed_provider_render", $format, $oembed_provider_data, $wp->query_vars);
    do_action("wpgs_oembed_provider_render_{$format}", $oembed_provider_data, $wp->query_vars);
  }

  /**
   * adds default content
   *
   * @param array $oembed_provider_data
   * @param string $post_type
   * @param Object $post
   */
  public static function wpgs_generate_default_content($oembed_provider_data, $post_type, $post) {
    $author = get_userdata($post->post_author);


    $oembed_provider_data['version'] = '1.0';
    $oembed_provider_data['type'] = 'rich';




    $oembed_provider_data['provider_name'] = get_bloginfo('name');
    $oembed_provider_data['provider_url'] = home_url();
    $oembed_provider_data['title'] = $post->post_title;
    $oembed_provider_data['url'] = get_permalink($post->ID);
    $oembed_provider_data['author_name'] = $author->display_name;
    $oembed_provider_data['author_url'] = get_author_posts_url($author->ID, $author->nicename);




    return $oembed_provider_data;
  }

  /**
  * adds post/page specific content
  *
  * @param array $oembed_provider_data
  * @param Object $post
  */
  public static function wpgs_generate_post_content($oembed_provider_data, $post) {
    if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
      $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),large);
      $oembed_provider_data['thumbnail_url'] = $image[0];
      $oembed_provider_data['thumbnail_width'] = $image[1];
      $oembed_provider_data['thumbnail_height'] = $image[2];
    }

       $oembed_provider_data['html'] = $post->post_excerpt;

    return $oembed_provider_data;
  }

  /**
   * adds attachement specific content
   *
   * @param array $oembed_provider_data
   * @param Object $post
   */
  public static function wpgs_generate_attachment_content($oembed_provider_data, $post) {
    if (wp_attachment_is_image($post->ID)) {
      $oembed_provider_data['type'] = 'photo';
    } else {
      $oembed_provider_data['type'] = 'link';
    }

    $oembed_provider_data['url'] = wp_get_attachment_url($post->ID);

    $metadata = wp_get_attachment_metadata($post->ID);

    if (isset($metadata['width'])) {
      $oembed_provider_data['width'] = $metadata['width'];
    }

    if (isset($metadata['height'])) {
      $oembed_provider_data['height'] = $metadata['height'];
    }

    return $oembed_provider_data;
  }

  /**
   * render json output
   *
   * @param array $oembed_provider_data
   */
  public static function wpgs_render_json($oembed_provider_data, $wp_query) {
    header('Content-Type: application/json; charset=' . get_bloginfo('charset'), true);

    // render json output
    $json = json_encode($oembed_provider_data);

    // add callback if available
    if (array_key_exists('callback', $wp_query)) {
      $json = $wp_query['callback'] . "($json);";
    }

    echo $json;
    exit;
  }

  /**
   * render xml output
   *
   * @param array $oembed_provider_data
   */
  public static function wpgs_render_xml($oembed_provider_data) {
    header('Content-Type: text/xml; charset=' . get_bloginfo('charset'), true);

    // render xml-output
    echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '" ?>';
    echo '<oembed>';
    foreach (array_keys($oembed_provider_data) as $element) {
      echo '<' . $element . '>' . esc_html($oembed_provider_data[$element]) . '</' . $element . '>';
    }
    echo '</oembed>';
    exit;
  }
}

