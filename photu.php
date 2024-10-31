<?php
/*
Plugin Name: Photu
Description: A WordPress plugin to automatically fetch your WordPress images via <a href="https://mogiio.com" target="_blank">Photu</a> for optimization and super fast delivery. <a href="https://docs.google.com/document/d/1P8sulREpvvAc00Mgc0Z25FGjEdkhQKZi-kcSlNRmAQU/" target="_blank">Learn more</a> from documentation.
Author: Mogiio
Author URI: https://mogiio.com/photu
Version: 1.3
*/

// Variables
$photu_options = get_option('photu_settings');

if (!defined('ABSPATH')) {
  exit;
}

if (!defined('PHOTU_PLUGIN_PATH')) {
  define('PHOTU_PLUGIN_PATH', __DIR__);
}

if (!defined('PHOTU_PLUGIN_ENTRYPOINT')) {
  define('PHOTU_PLUGIN_ENTRYPOINT', __FILE__);
}

if (!defined(('PHOTU_DEBUG'))) {
  define('PHOTU_DEBUG', false);
}

add_action('template_redirect', function () {

  global $photu_options;

  if (isset($photu_options['photu_id'])) {
    $photuId = $photu_options['photu_id'];
  }

  if (isset($photu_options['photu_url_endpoint'])) {
    $photuUrlEndpoint = $photu_options['photu_url_endpoint'];
  }

  if (empty($photuId) && empty($photuUrlEndpoint)) {
    return;
  }

  // load class
  require_once __DIR__ . '/includes/PhotuReWriter.php';
  require_once __DIR__ . '/includes/PhotuHelper.php';

  // get url of cdn & site
  if (!empty($photuId)) {
    $cdn_url = "https://apis-z.mogiio.com/mogi-enhance/" . $photuId . "/fwebp,q80,ptrue";
  }

  if (!empty($photu_options["cname"])) {
    $cdn_url = $photu_options["cname"];
  }

  if (!empty($photuUrlEndpoint)) {
    $cdn_url = $photuUrlEndpoint;
  }

  $cdn_url = ensure_valid_photu_url($cdn_url);
  if (empty($cdn_url)) {
    return;
  }

  $site_url = get_home_url();

  // instantiate class
  $photu = new photuReWriter($cdn_url, $site_url, $photu_options);
  ob_start(array(&$photu,
    'replace_all_links'
  ));
});

include ('includes/setting.php');

// Settings
function photu_plugin_admin_links($links, $file) {
  static $my_plugin;
  if (!$my_plugin) {
    $my_plugin = plugin_basename(__FILE__);
  }
  if ($file == $my_plugin) {
    $settings_link = '<a href="options-general.php?page=photu-setting">Settings</a>';
    array_unshift($links, $settings_link);
  }
  return $links;
}

function ensure_valid_photu_url($url) {

  $parsed_url = parse_url($url);

  $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '//';
  $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
  $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
  $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
  $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
  $pass = ($user || $pass) ? "$pass@" : '';
  $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
  $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

  $result = "$scheme$user$pass$host$port$path$query$fragment";

  if ($result) return substr($result, -1) == "/" ? $result : $result . '/';

  return NULL;
}

add_filter('plugin_action_links', 'photu_plugin_admin_links', 10, 2);
?>
