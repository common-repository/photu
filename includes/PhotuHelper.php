<?php
class PhotuHelper {
  static $debug_logs = array();

  /**
   * Formats URL
   *
   * @param string  $url
   * @param array   $params
   * @param boolean $skip_empty
   * @param string  $separator
   * @return string
   */
  static public function url_format($url = '', $params = array() , $skip_empty = false, $separator = '&') {
    if ($url != '') {
      $parse_url = @parse_url($url);
      $url = '';

      if (!empty($parse_url['scheme'])) {
        $url .= $parse_url['scheme'] . '://';

        if (!empty($parse_url['user'])) {
          $url .= $parse_url['user'];

          if (!empty($parse_url['pass'])) {
            $url .= ':' . $parse_url['pass'];
          }
        }

        if (!empty($parse_url['host'])) {
          $url .= $parse_url['host'];
        }

        if (!empty($parse_url['port']) && $parse_url['port'] != 80) {
          $url .= ':' . (int)$parse_url['port'];
        }
      }

      if (!empty($parse_url['path'])) {
        $url .= $parse_url['path'];
      }

      if (!empty($parse_url['query'])) {
        $old_params = array();
        parse_str($parse_url['query'], $old_params);

        $params = array_merge($old_params, $params);
      }

      $query = PhotuHelper::url_query($params);

      if ($query != '') {
        $url .= '?' . $query;
      }

      if (!empty($parse_url['fragment'])) {
        $url .= '#' . $parse_url['fragment'];
      }
    }
    else {
      $query = PhotuHelper::url_query($params, $skip_empty, $separator);

      if ($query != '') {
        $url = '?' . $query;
      }
    }

    return $url;
  }

  /**
   * Formats query string
   *
   * @param array   $params
   * @param boolean $skip_empty
   * @param string  $separator
   * @return string
   */
  static public function url_query($params = array() , $skip_empty = false, $separator = '&') {
    $str = '';
    static $stack = array();

    foreach ((array)$params as $key => $value) {
      if ($skip_empty === true && empty($value)) {
        continue;
      }

      array_push($stack, $key);

      if (is_array($value)) {
        if (count($value)) {
          $str .= ($str != '' ? '&' : '') . PhotuHelper::url_query($value, $skip_empty, $key);
        }
      }
      else {
        $name = '';
        foreach ($stack as $key) {
          $name .= ($name != '' ? '[' . $key . ']' : $key);
        }
        $str .= ($str != '' ? $separator : '') . $name . '=' . rawurlencode($value);
      }

      array_pop($stack);
    }

    return $str;
  }

  /*
   * Returns URL from filename/dirname
   *
   * @return string
  */
  static public function filename_to_url($filename, $use_site_url = false) {
    // using wp-content instead of document_root as known dir since dirbased
    // multisite wp adds blogname to the path inside site_url
    if (substr($filename, 0, strlen(WP_CONTENT_DIR)) != WP_CONTENT_DIR) return '';
    $uri_from_wp_content = substr($filename, strlen(WP_CONTENT_DIR));

    if (DIRECTORY_SEPARATOR != '/') $uri_from_wp_content = str_replace(DIRECTORY_SEPARATOR, '/', $uri_from_wp_content);

    $url = content_url($uri_from_wp_content);
    $url = apply_filters('w3tc_filename_to_url', $url);

    return $url;
  }

  /**
   * Returns true if database cluster is used
   *
   * @return boolean
   */
  static public function is_dbcluster() {
    return defined('W3TC_FILE_DB_CLUSTER_CONFIG') && @file_exists(W3TC_FILE_DB_CLUSTER_CONFIG) && defined('W3TC_ENTERPRISE') && W3TC_ENTERPRISE;
  }

  /**
   * Returns true if WPMU uses vhosts
   *
   * @return boolean
   */
  static public function is_wpmu_subdomain() {
    return ((defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) || (defined('VHOST') && VHOST == 'yes'));
  }

  /**
   * Returns if there is multisite mode
   *
   * @return boolean
   */
  static public function is_wpmu() {
    static $wpmu = null;

    if ($wpmu === null) {
      $wpmu = (file_exists(ABSPATH . 'wpmu-settings.php') || (defined('MULTISITE') && MULTISITE) || defined('SUNRISE') || PhotuHelper::is_wpmu_subdomain());
    }

    return $wpmu;
  }

  static public function is_using_master_config() {
    static $result = null;
    if (is_null($result)) {
      if (!PhotuHelper::is_wpmu()) {
        $result = true;
      }
      elseif (is_network_admin()) {
        $result = true;
      }
      else {
        $blog_data = Util_WpmuBlogmap::get_current_blog_data();
        if (is_null($blog_data)) $result = true;
        $result = ($blog_data[0] == 'm');
      }
    }

    return $result;
  }

  /**
   * Check if URL is valid
   *
   * @param string  $url
   * @return boolean
   */
  static public function is_url($url) {
    return preg_match('~^(https?:)?//~', $url);
  }

  /**
   * Returns true if current connection is secure
   *
   * @return boolean
   */
  static public function is_https() {
    switch (true) {
      case (isset($_SERVER['HTTPS']) && PhotuHelper::to_boolean($_SERVER['HTTPS'])):
      case (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] == 443):
      case (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'):
        return true;
    }

    return false;
  }

  /**
   * Moves user to preview-mode or opposite
   */
  static public function set_preview($is_enabled) {
    if ($is_enabled) setcookie('w3tc_preview', '*', 0, '/');
    else setcookie("w3tc_preview", '', time() - 3600, '/');
  }

  /**
   * Retuns true if preview settings active
   *
   * @return boolean
   */
  static public function is_preview_mode() {
    return !empty($_COOKIE['w3tc_preview']);
  }

  /**
   * Returns true if server is Apache
   *
   * @return boolean
   */
  static public function is_apache() {
    // assume apache when unknown, since most common
    if (empty($_SERVER['SERVER_SOFTWARE'])) return true;

    return isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false;
  }

  /**
   * Check whether server is LiteSpeed
   *
   * @return bool
   */
  static public function is_litespeed() {
    return isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false;
  }

  /**
   * Returns true if server is nginx
   *
   * @return boolean
   */
  static public function is_nginx() {
    return isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false;
  }

  /**
   * Returns true if server is nginx
   *
   * @return boolean
   */
  static public function is_iis() {
    return isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'IIS') !== false;
  }

  /**
   * Returns domain from host
   *
   * @param string  $host
   * @return string
   */
  static public function url_to_host($url) {
    $a = parse_url($url);
    if (isset($a['host'])) return $a['host'];

    return '';
  }

  /**
   * Returns path from URL. Without trailing slash
   */
  static public function url_to_uri($url) {
    $uri = @parse_url($url, PHP_URL_PATH);

    // convert FALSE and other return values to string
    if (empty($uri)) return '';

    return rtrim($uri, '/');
  }

  /**
   * Returns current blog ID
   *
   * @return integer
   */
  static public function blog_id() {
    global $w3_current_blog_id;

    if (!is_null($w3_current_blog_id)) return $w3_current_blog_id;

    if (!PhotuHelper::is_wpmu() || is_network_admin()) {
      $w3_current_blog_id = 0;
      return $w3_current_blog_id;
    }

    $blog_data = Util_WpmuBlogmap::get_current_blog_data();
    if (!is_null($blog_data)) $w3_current_blog_id = substr($blog_data, 1);
    else $w3_current_blog_id = 0;

    return $w3_current_blog_id;
  }

  /**
   * Memoized version of wp_upload_dir. That function is quite slow
   * for a number of times CDN calls it
   */
  static public function wp_upload_dir() {
    return wp_upload_dir();
    // static $values_by_blog = array();
    // $blog_id = PhotuHelper::blog_id();
    // if ( !isset( $values_by_blog[$blog_id] ) )
    // 	$values_by_blog[$blog_id] = wp_upload_dir();
    // return $values_by_blog[$blog_id];
    
  }

  /**
   * Returns path to section's cache dir
   *
   * @param string  $section
   * @return string
   */
  static public function cache_dir($section) {
    return W3TC_CACHE_DIR . DIRECTORY_SEPARATOR . $section;
  }

  /**
   * Returns path to blog's cache dir
   *
   * @param string  $section
   * @param null|int $blog_id
   * @return string
   */
  static public function cache_blog_dir($section, $blog_id = null) {
    if (!PhotuHelper::is_wpmu()) $postfix = '';
    else {
      if (is_null($blog_id)) $blog_id = PhotuHelper::blog_id();

      $postfix = DIRECTORY_SEPARATOR . sprintf('%d', $blog_id);

      if (defined('W3TC_BLOG_LEVELS')) {
        for ($n = 0;$n < W3TC_BLOG_LEVELS;$n++) $postfix = DIRECTORY_SEPARATOR . substr($postfix, strlen($postfix) - 1 - $n, 1) . $postfix;
      }
    }

    return PhotuHelper::cache_dir($section) . $postfix;
  }

  static public function cache_blog_minify_dir() {
    // when minify manual used with a shared config - shared
    // minify urls has to be used too, since CDN upload is possible
    // only from network admin
    if (PhotuHelper::is_wpmu() && PhotuHelper::is_using_master_config() && !Dispatcher::config()->get_boolean('minify.auto')) $path = PhotuHelper::cache_blog_dir('minify', 0);
    else $path = PhotuHelper::cache_blog_dir('minify');

    return $path;
  }

  /**
   * Returns URL regexp from URL
   *
   * @param string  $url
   * @return string
   */
  static public function get_url_regexp($url) {
    $url = preg_replace('~(https?:)?//~i', '', $url);
    $url = preg_replace('~^www\.~i', '', $url);

    $regexp = '(https?:)?//(www\.)?' . PhotuHelper::preg_quote($url);

    return $regexp;
  }

  /**
   * Returns SSL URL if current connection is https
   *
   * @param string  $url
   * @return string
   */
  static public function url_to_maybe_https($url) {
    if (PhotuHelper::is_https()) {
      $url = str_replace('http://', 'https://', $url);
    }

    return $url;
  }

  /**
   * Get domain URL
   *
   * @return string
   */

  static public function home_domain_root_url() {
    $home_url = get_home_url();
    $parse_url = @parse_url($home_url);

    if ($parse_url && isset($parse_url['scheme']) && isset($parse_url['host'])) {
      $scheme = $parse_url['scheme'];
      $host = $parse_url['host'];
      $port = (isset($parse_url['port']) && $parse_url['port'] != 80 ? ':' . (int)$parse_url['port'] : '');
      $domain_url = sprintf('%s://%s%s', $scheme, $host, $port);

      return $domain_url;
    }

    return false;
  }

  /**
   * Returns domain url regexp
   *
   * @return string
   */
  static public function home_domain_root_url_regexp() {
    $domain_url = PhotuHelper::home_domain_root_url();
    $regexp = PhotuHelper::get_url_regexp($domain_url);

    return $regexp;
  }

  /**
   * Returns SSL home url
   *
   * @return string
   */
  static public function home_url_maybe_https() {
    $home_url = get_home_url();
    $ssl = PhotuHelper::url_to_maybe_https($home_url);

    return $ssl;
  }

  /**
   * Returns home url regexp
   *
   * @return string
   */
  static public function home_url_regexp() {
    $home_url = get_home_url();
    $regexp = PhotuHelper::get_url_regexp($home_url);

    return $regexp;
  }

  /**
   * Copy of wordpress get_home_path, but accessible not only for wp-admin
   * Get the absolute filesystem path to the root of the WordPress installation
   * (i.e. filesystem path of siteurl)
   *
   * @return string Full filesystem path to the root of the WordPress installation
   */
  static public function site_path() {
    $home = set_url_scheme(get_option('home') , 'http');
    $siteurl = set_url_scheme(get_option('siteurl') , 'http');

    $home_path = ABSPATH;
    if (!empty($home) && 0 !== strcasecmp($home, $siteurl)) {
      $wp_path_rel_to_home = str_ireplace($home, '', $siteurl); /* $siteurl - $home */
      $pos = strripos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']) , trailingslashit($wp_path_rel_to_home));
      // fix of get_home_path, used when index.php is moved outside of
      // wp folder.
      if ($pos !== false) {
        $home_path = substr($_SERVER['SCRIPT_FILENAME'], 0, $pos);
        $home_path = trailingslashit($home_path);
      }
    }

    return str_replace('\\', DIRECTORY_SEPARATOR, $home_path);
  }

  /**
   * Returns absolute path to document root
   *
   * No trailing slash!
   *
   * @return string
   */
  static public function document_root() {
    static $document_root = null;

    if (!is_null($document_root)) return $document_root;

    if (!empty($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['PHP_SELF'])) {
      $script_filename = PhotuHelper::normalize_path($_SERVER['SCRIPT_FILENAME']);
      $php_self = PhotuHelper::normalize_path($_SERVER['PHP_SELF']);
      if (substr($script_filename, -strlen($php_self)) == $php_self) {
        $document_root = substr($script_filename, 0, -strlen($php_self));
        $document_root = realpath($document_root);
        return $document_root;
      }
    }

    if (!empty($_SERVER['PATH_TRANSLATED'])) {
      $document_root = substr(PhotuHelper::normalize_path($_SERVER['PATH_TRANSLATED']) , 0, -strlen(PhotuHelper::normalize_path($_SERVER['PHP_SELF'])));
    }
    elseif (!empty($_SERVER['DOCUMENT_ROOT'])) {
      $document_root = PhotuHelper::normalize_path($_SERVER['DOCUMENT_ROOT']);
    }
    else {
      $document_root = ABSPATH;
    }

    $document_root = realpath($document_root);
    return $document_root;
  }

  /**
   * Returns absolute path to blog install dir
   *
   * Example:
   *
   * DOCUMENT_ROOT=/var/www/vhosts/domain.com
   * install dir=/var/www/vhosts/domain.com/site/blog
   * return /var/www/vhosts/domain.com/site/blog
   *
   * No trailing slash!
   *
   * @return string
   */
  static public function site_root() {
    $site_root = ABSPATH;
    $site_root = realpath($site_root);
    $site_root = PhotuHelper::normalize_path($site_root);

    return $site_root;
  }

  /**
   * Returns blog path
   *
   * Example:
   *
   * siteurl=http://domain.com/site/blog
   * return /site/blog/
   *
   * With trailing slash!
   *
   * @return string
   */
  static public function site_url_uri() {
    return PhotuHelper::url_to_uri(site_url()) . '/';
  }

  /**
   * Returns home domain
   *
   * @return string
   */
  static public function home_url_host() {
    $home_url = get_home_url();
    $parse_url = @parse_url($home_url);

    if ($parse_url && isset($parse_url['host'])) {
      return $parse_url['host'];
    }

    return PhotuHelper::host();
  }

  /**
   * Returns home path
   *
   * Example:
   *
   * home=http://domain.com/site/
   * siteurl=http://domain.com/site/blog
   * return /site/
   *
   * With trailing slash!
   *
   * @return string
   */
  static public function home_url_uri() {
    return PhotuHelper::url_to_uri(get_home_url()) . '/';
  }

  static public function network_home_url_uri() {
    $uri = network_home_url('', 'relative');

    /* There is a bug in WP where network_home_url can return
     * a non-relative URI even though scheme is set to relative.
    */
    if (PhotuHelper::is_url($uri)) $uri = parse_url($uri, PHP_URL_PATH);

    if (empty($uri)) return '/';

    return $uri;
  }

  /**
   * Returns server hostname with port
   *
   * @return string
   */
  static public function host_port() {
    static $host = null;

    if ($host === null) {
      if (!empty($_SERVER['HTTP_HOST'])) {
        // HTTP_HOST sometimes is not set causing warning
        $host = $_SERVER['HTTP_HOST'];
      }
      else {
        $host = '';
      }
    }

    return $host;
  }

  static public function host() {
    $host_port = PhotuHelper::host_port();

    $pos = strpos($host_port, ':');
    if ($pos === false) return $host_port;

    return substr($host_port, 0, $pos);
  }

  /**
   * Parses path
   *
   * @param string  $path
   * @return mixed
   */
  static public function parse_path($path) {
    $path = str_replace(array(
      '%BLOG_ID%',
      '%POST_ID%',
      '%BLOG_ID%',
      '%HOST%'
    ) , array(
      (isset($GLOBALS['blog_id']) ? (int)$GLOBALS['blog_id'] : 0) ,
      (isset($GLOBALS['post_id']) ? (int)$GLOBALS['post_id'] : 0) ,
      PhotuHelper::blog_id() ,
      PhotuHelper::host()
    ) , $path);

    return $path;
  }

  /**
   * Normalizes file name
   *
   * Relative to site root!
   *
   * @param string  $file
   * @return string
   */
  static public function normalize_file($file) {
    if (PhotuHelper::is_url($file)) {
      if (strstr($file, '?') === false) {
        $home_url_regexp = '~' . PhotuHelper::home_url_regexp() . '~i';
        $file = preg_replace($home_url_regexp, '', $file);
      }
    }

    if (!PhotuHelper::is_url($file)) {
      $file = PhotuHelper::normalize_path($file);
      $file = str_replace(PhotuHelper::site_root() , '', $file);
      $file = ltrim($file, '/');
    }

    return $file;
  }

  /**
   * Normalizes file name for minify
   *
   * Relative to document root!
   *
   * @param string  $file
   * @return string
   */
  static public function normalize_file_minify($file) {
    if (PhotuHelper::is_url($file)) {
      if (strstr($file, '?') === false) {
        $domain_url_regexp = '~' . PhotuHelper::home_domain_root_url_regexp() . '~i';
        $file = preg_replace($domain_url_regexp, '', $file);
      }
    }

    if (!PhotuHelper::is_url($file)) {
      $file = PhotuHelper::normalize_path($file);
      $file = str_replace(PhotuHelper::document_root() , '', $file);
      $file = ltrim($file, '/');
    }

    return $file;
  }

  /**
   * Normalizes file name for minify
   * Relative to document root!
   *
   * @param string  $file
   * @return string
   */
  static public function url_to_docroot_filename($url) {
    $data = array(
      'home_url' => get_home_url() ,
      'url' => $url
    );
    $data = apply_filters('w3tc_url_to_docroot_filename', $data);

    $home_url = $data['home_url'];
    $normalized_url = $data['url'];
    $normalized_url = PhotuHelper::remove_query_all($normalized_url);

    // cut protocol
    $normalized_url = preg_replace('~^http(s)?://~', '//', $normalized_url);
    $home_url = preg_replace('~^http(s)?://~', '//', $home_url);

    if (substr($normalized_url, 0, strlen($home_url)) != $home_url) {
      // not a home url, return unchanged since cant be
      // converted to filename
      return $url;
    }
    else {
      $path_relative_to_home = str_replace($home_url, '', $normalized_url);

      $home = set_url_scheme(get_option('home') , 'http');
      $siteurl = set_url_scheme(get_option('siteurl') , 'http');

      $home_path = rtrim(PhotuHelper::site_path() , '/');
      // adjust home_path if site is not is home
      if (!empty($home) && 0 !== strcasecmp($home, $siteurl)) {
        // $siteurl - $home
        $wp_path_rel_to_home = rtrim(str_ireplace($home, '', $siteurl) , '/');
        if (substr($home_path, -strlen($wp_path_rel_to_home)) == $wp_path_rel_to_home) {
          $home_path = substr($home_path, 0, -strlen($wp_path_rel_to_home));
        }
      }

      // common encoded characters
      $path_relative_to_home = str_replace('%20', ' ', $path_relative_to_home);

      $full_filename = $home_path . DIRECTORY_SEPARATOR . trim($path_relative_to_home, DIRECTORY_SEPARATOR);

      $docroot = PhotuHelper::document_root();
      if (substr($full_filename, 0, strlen($docroot)) == $docroot) $docroot_filename = substr($full_filename, strlen($docroot));
      else $docroot_filename = $path_relative_to_home;
    }

    // sometimes urls (coming from other plugins/themes)
    // contain multiple "/" like "my-folder//myfile.js" which
    // fails to recognize by filesystem, while url is accessible
    $docroot_filename = str_replace('//', DIRECTORY_SEPARATOR, $docroot_filename);

    return ltrim($docroot_filename, DIRECTORY_SEPARATOR);
  }

  /**
   * Translates remote file to local file
   *
   * @param string  $file
   * @return string
   */
  static public function translate_file($file) {
    return $file;
  }

  /**
   * Removes WP query string from URL
   */
  static public function remove_query($url) {
    $url = preg_replace('~[&\?]+(ver=([a-z0-9-_\.]+|[0-9-]+))~i', '', $url);

    return $url;
  }

  /**
   * Removes all query strings from url
   */
  static public function remove_query_all($url) {
    $pos = strpos($url, '?');
    if ($pos === false) return $url;

    return substr($url, 0, $pos);
  }

  /**
   * Converts win path to unix
   *
   * @param string  $path
   * @return string
   */
  static public function normalize_path($path) {
    $path = preg_replace('~[/\\\]+~', '/', $path);
    $path = rtrim($path, '/');

    return $path;
  }

  /**
   * Returns real path of given path
   *
   * @param string  $path
   * @return string
   */
  static public function realpath($path) {
    $path = PhotuHelper::normalize_path($path);
    $parts = explode('/', $path);
    $absolutes = array();

    foreach ($parts as $part) {
      if ('.' == $part) {
        continue;
      }
      if ('..' == $part) {
        array_pop($absolutes);
      }
      else {
        $absolutes[] = $part;
      }
    }

    return implode('/', $absolutes);
  }

  /**
   * Returns real path of given path
   *
   * @param string  $path
   * @return string
   */
  static public function path_remove_dots($path) {
    $parts = explode('/', $path);
    $absolutes = array();

    foreach ($parts as $part) {
      if ('.' == $part) {
        continue;
      }
      if ('..' == $part) {
        array_pop($absolutes);
      }
      else {
        $absolutes[] = $part;
      }
    }

    return implode('/', $absolutes);
  }

  /**
   * Returns full URL from relative one
   */
  static public function url_relative_to_full($relative_url) {
    $relative_url = PhotuHelper::path_remove_dots($relative_url);

    if (version_compare(PHP_VERSION, '5.4.7') < 0) {
      if (substr($relative_url, 0, 2) == '//') {
        $relative_url = (PhotuHelper::is_https() ? 'https' : 'http') . ':' . $relative_url;
      }
    }

    $rel = parse_url($relative_url);
    // it's full url already
    if (isset($rel['scheme']) || isset($rel['host'])) return $relative_url;

    if (!isset($rel['host'])) $rel['host'] = parse_url(get_home_url() , PHP_URL_HOST);

    $scheme = isset($rel['scheme']) ? $rel['scheme'] . '://' : '//';
    $host = isset($rel['host']) ? $rel['host'] : '';
    $port = isset($rel['port']) ? ':' . $rel['port'] : '';
    $path = isset($rel['path']) ? $rel['path'] : '';
    $query = isset($rel['query']) ? '?' . $rel['query'] : '';
    return "$scheme$host$port$path$query";
  }

  /**
   * Redirects to URL
   *
   * @param string  $url
   * @param array   $params
   * @return string
   */
  static public function redirect($url = '', $params = array()) {
    $url = PhotuHelper::url_format($url, $params);
    if (function_exists('do_action')) do_action('w3tc_redirect');

    @header('Location: ' . $url);
    exit();
  }

  /**
   * Redirects to URL
   *
   * @param string  $url
   * @param array   $params
   *
   * @return string
   */
  static public function redirect_temp($url = '', $params = array()) {
    $url = PhotuHelper::url_format($url, $params);
    if (function_exists('do_action')) do_action('w3tc_redirect');

    $status_code = 301;

    $protocol = $_SERVER["SERVER_PROTOCOL"];
    if ('HTTP/1.1' === $protocol) {
      $status_code = 307;
    }

    $text = get_status_header_desc($status_code);
    if (!empty($text)) {
      $status_header = "$protocol $status_code $text";
      @header($status_header, true, $status_code);
    }
    @header('Cache-Control: no-cache');
    @header('Location: ' . $url, true, $status_code);
    exit();
  }

  /**
   * Detects post ID
   *
   * @return integer
   */
  static public function detect_post_id() {
    global $posts, $comment_post_ID, $post_ID;

    if ($post_ID) {
      return $post_ID;
    }
    elseif ($comment_post_ID) {
      return $comment_post_ID;
    }
    elseif ((is_single() || is_page()) && is_array($posts)) {
      return $posts[0]->ID;
    }
    elseif (is_object($posts) && property_exists($posts, 'ID')) {
      return $posts->ID;
    }
    elseif (isset($_REQUEST['p'])) {
      return (integer)$_REQUEST['p'];
    }

    return 0;
  }

  static public function instance_id() {
    static $instance_id;

    if (!isset($instance_id)) {
      $config = Dispatcher::config();
      $instance_id = $config->get_integer('common.instance_id', 0);
    }
    return $instance_id;
  }

  /**
   *
   *
   * @var Config $config
   * @return string
   */
  static public function w3tc_edition($config = null) {
    if (PhotuHelper::is_w3tc_enterprise($config)) return 'enterprise';
    if (PhotuHelper::is_w3tc_pro($config) && PhotuHelper::is_w3tc_pro_dev()) return 'pro development';
    if (PhotuHelper::is_w3tc_pro($config)) return 'pro';
    return 'community';
  }

  /**
   *
   *
   * @param Config  $config
   * @return bool
   */
  static public function is_w3tc_pro($config = null) {
    if (defined('W3TC_PRO') && W3TC_PRO) return true;

    if (is_object($config)) {
      $plugin_type = $config->get_string('plugin.type');

      if ($plugin_type == 'pro' || $plugin_type == 'pro_dev') return true;
    }

    if (PhotuHelper::is_w3tc_enterprise($config)) return true;

    return false;
  }

  /**
   * Enable Pro Dev mode support
   *
   * @return bool
   */
  static public function is_w3tc_pro_dev() {
    return defined('W3TC_PRO_DEV_MODE') && W3TC_PRO_DEV_MODE;
  }

  /**
   *
   *
   * @param Config  $config
   * @return bool
   */
  static public function is_w3tc_enterprise($config = null) {
    if (defined('W3TC_ENTERPRISE') && W3TC_ENTERPRISE) return true;

    if (is_object($config) && $config->get_string('plugin.type') == 'enterprise') return true;

    return false;
  }

  /**
   * Checks if site is using edge mode.
   *
   * @return bool
   */
  static public function is_w3tc_edge($config) {
    return $config->get_boolean('common.edge');
  }

  /**
   * Quotes regular expression string
   *
   * @param string  $string
   * @param string  $delimiter
   * @return string
   */
  static public function preg_quote($string, $delimiter = '~') {
    $string = preg_quote($string, $delimiter);
    $string = strtr($string, array(
      ' ' => '\ '
    ));

    return $string;
  }

  /**
   * Returns true if zlib output compression is enabled otherwise false
   *
   * @return boolean
   */
  static public function is_zlib_enabled() {
    return PhotuHelper::to_boolean(ini_get('zlib.output_compression'));
  }

  /**
   * Recursive strips slahes from the var
   *
   * @param mixed   $var
   * @return mixed
   */
  static public function stripslashes($var) {
    if (is_string($var)) {
      return stripslashes($var);
    }
    elseif (is_array($var)) {
      $var = array_map(array(
        '\W3TC\PhotuHelper',
        'stripslashes'
      ) , $var);
    }

    return $var;
  }

  /**
   * Checks if post should be flushed or not. Returns true if it should not be flushed
   *
   * @param unknown $post
   * @param string  $module which cache module to check against (pgcache, varnish, dbcache or objectcache)
   * @param Config  $config
   * @return bool
   */
  static public function is_flushable_post($post, $module, $config) {
    if (is_numeric($post)) $post = get_post($post);
    $post_status = array(
      'publish'
    );
    // dont flush when we have post "attachment"
    // its child of the post and is flushed always when post is published, while not changed in fact
    $post_type = array(
      'revision',
      'attachment'
    );
    switch ($module) {
      case 'pgcache':
      case 'varnish':
      case 'posts': // means html content of post pages
        if (!$config->get_boolean('pgcache.reject.logged')) $post_status[] = 'private';
        break;
      case 'dbcache':
        if (!$config->get_boolean('dbcache.reject.logged')) $post_status[] = 'private';
        break;
      }

      $flushable = is_object($post) && !in_array($post->post_type, $post_type) && in_array($post->post_status, $post_status);

      return apply_filters('w3tc_flushable_post', $flushable, $post, $module);
    }

    /**
     * Converts value to boolean
     *
     * @param mixed   $value
     * @return boolean
     */
    static public function to_boolean($value) {
      if (is_string($value)) {
        switch (strtolower($value)) {
          case '+':
          case '1':
          case 'y':
          case 'on':
          case 'yes':
          case 'true':
          case 'enabled':
            return true;

          case '-':
          case '0':
          case 'n':
          case 'no':
          case 'off':
          case 'false':
          case 'disabled':
            return false;
        }
      }

      return (boolean)$value;
    }

    /**
     * Filter handler for use_curl_transport.
     * Workaround to not use curl for extra http methods
     *
     * @param unknown $result boolean
     * @param unknown $args   array
     * @return boolean
     */
    static public function use_curl_transport($result, $args) {
      if (isset($args['method']) && $args['method'] != 'GET' && $args['method'] != 'POST') return false;

      return $result;
    }

    /**
     * Sends HTTP request
     *
     * @param unknown $url  string
     * @param unknown $args array
     * @return WP_Error|array
     */
    static public function request($url, $args = array()) {
      static $filter_set = false;
      if (!$filter_set) {
        add_filter('use_curl_transport', array(
          '\W3TC\Util_Http',
          'use_curl_transport'
        ) , 10, 2);
        $filter_set = true;
      }

      $args = array_merge(array(
        'user-agent' => W3TC_POWERED_BY
      ) , $args);

      return wp_remote_request($url, $args);
    }

    /**
     * Sends HTTP GET request
     *
     * @param string  $url
     * @param array   $args
     * @return array|WP_Error
     */
    static public function get($url, $args = array()) {
      $args = array_merge($args, array(
        'method' => 'GET'
      ));

      return self::request($url, $args);
    }

    /**
     * Downloads URL into a file
     *
     * @param string  $url
     * @param string  $file
     * @return boolean
     */
    static public function download($url, $file) {
      if (strpos($url, '//') === 0) {
        $url = (PhotuHelper::is_https() ? 'https:' : 'http:') . $url;
      }

      $response = self::get($url);

      if (!is_wp_error($response) && $response['response']['code'] == 200) {
        return @file_put_contents($file, $response['body']);
      }

      return false;
    }

    /**
     * Returns upload info
     *
     * @return array
     */
    static public function upload_info() {
      static $upload_info = null;

      if ($upload_info === null) {
        $upload_info = PhotuHelper::wp_upload_dir();

        if (empty($upload_info['error'])) {
          $parse_url = @parse_url($upload_info['baseurl']);

          if ($parse_url) {
            $baseurlpath = (!empty($parse_url['path']) ? trim($parse_url['path'], '/') : '');
          }
          else {
            $baseurlpath = 'wp-content/uploads';
          }

          $upload_info['baseurlpath'] = '/' . $baseurlpath . '/';
        }
        else {
          $upload_info = false;
        }
      }

      return $upload_info;
    }

    /**
     * Check whether $engine is correct CDN engine
     *
     * @param string  $engine
     * @return boolean
     */
    static public function is_engine($engine) {
      return in_array($engine, array(
        'akamai',
        'att',
        'azure',
        'cf',
        'cloudfront_fsd',
        'cf2',
        'cotendo',
        'edgecast',
        'maxcdn_fsd',
        'ftp',
        'google_drive',
        'highwinds',
        'maxcdn',
        'mirror',
        'netdna',
        'rscf',
        'rackspace_cdn',
        's3',
        's3_compatible',
      ));
    }

    /**
     * Returns true if CDN engine is mirror
     *
     * @param string  $engine
     * @return bool
     */
    static public function is_engine_mirror($engine) {
      return in_array($engine, array(
        'mirror',
        'netdna',
        'maxcdn',
        'cotendo',
        'cf2',
        'akamai',
        'edgecast',
        'att',
        'highwinds',
        'rackspace_cdn'
      ));
    }

    /**
     * Returns true if CDN engine is mirror
     *
     * @param string  $engine
     * @return bool
     */
    static public function is_engine_fsd($engine) {
      return in_array($engine, array(
        'cloudfront_fsd',
        'maxcdn_fsd'
      ));
    }

    static public function is_engine_push($engine) {
      return !self::is_engine_mirror($engine) && !self::is_engine_fsd($engine);
    }

    /**
     * Returns true if CDN has purge all support
     *
     * @param unknown $engine
     * @return bool
     */
    static public function can_purge_all($engine) {
      return in_array($engine, array(
        'att',
        'cotendo',
        'edgecast',
        'maxcdn_fsd',
        'highwinds',
        'maxcdn',
        'netdna',
      ));
    }

    /**
     * Returns true if CDN engine is supporting purge
     *
     * @param string  $engine
     * @return bool
     */
    static public function can_purge($engine) {
      return in_array($engine, array(
        'akamai',
        'att',
        'azure',
        'cf',
        'cf2',
        'cloudfront_fsd',
        'cotendo',
        'edgecast',
        'maxcdn_fsd',
        'ftp',
        'highwinds',
        'maxcdn',
        'netdna',
        'rscf',
        's3',
        's3_compatible',
      ));
    }

    /**
     * Returns true if CDN supports realtime purge. That is purging on post changes, comments etc.
     *
     * @param unknown $engine
     * @return bool
     */
    static public function supports_realtime_purge($engine) {
      return !in_array($engine, array(
        'cf2'
      ));
    }

    /**
     * Search files
     *
     * @param string  $search_dir
     * @param string  $base_dir
     * @param string  $mask
     * @param boolean $recursive
     * @return array
     */
    static function search_files($search_dir, $base_dir, $mask = '*.*', $recursive = true) {
      static $stack = array();
      $files = array();
      $ignore = array(
        '.svn',
        '.git',
        '.DS_Store',
        'CVS',
        'Thumbs.db',
        'desktop.ini'
      );

      $dir = @opendir($search_dir);

      if ($dir) {
        while (($entry = @readdir($dir)) !== false) {
          if ($entry != '.' && $entry != '..' && !in_array($entry, $ignore)) {
            $path = $search_dir . '/' . $entry;

            if (@is_dir($path) && $recursive) {
              array_push($stack, $entry);
              $files = array_merge($files, self::search_files($path, $base_dir, $mask, $recursive));
              array_pop($stack);
            }
            else {
              $regexp = '~^(' . self::get_regexp_by_mask($mask) . ')$~i';

              if (preg_match($regexp, $entry)) {
                $tmp = $base_dir != '' ? $base_dir . '/' : '';
                $tmp .= ($p = implode('/', $stack)) != '' ? $p . '/' : '';
                $files[] = $tmp . $entry;
              }
            }
          }
        }

        @closedir($dir);
      }

      return $files;
    }

    /**
     * Returns regexp by mask
     *
     * @param string  $mask
     * @return string
     */
    static function get_regexp_by_mask($mask) {
      $mask = trim($mask);
      $mask = PhotuHelper::preg_quote($mask);

      $mask = str_replace(array(
        '\*',
        '\?',
        ';'
      ) , array(
        '@ASTERISK@',
        '@QUESTION@',
        '|'
      ) , $mask);

      $regexp = str_replace(array(
        '@ASTERISK@',
        '@QUESTION@'
      ) , array(
        '[^\\?\\*:\\|\'"<>]*',
        '[^\\?\\*:\\|\'"<>]'
      ) , $mask);

      return $regexp;
    }

    static function replace_folder_placeholders($file) {
      static $content_dir, $plugin_dir, $upload_dir;
      if (empty($content_dir)) {
        $content_dir = str_replace(PhotuHelper::document_root() , '', WP_CONTENT_DIR);
        $content_dir = substr($content_dir, strlen(PhotuHelper::site_url_uri()));
        $content_dir = trim($content_dir, '/');
        if (defined('WP_PLUGIN_DIR')) {
          $plugin_dir = str_replace(PhotuHelper::document_root() , '', WP_PLUGIN_DIR);
          $plugin_dir = trim($plugin_dir, '/');
        }
        else {
          $plugin_dir = str_replace(PhotuHelper::document_root() , '', WP_CONTENT_DIR . '/plugins');
          $plugin_dir = trim($plugin_dir, '/');
        }
        $upload_dir = PhotuHelper::wp_upload_dir();
        $upload_dir = str_replace(PhotuHelper::document_root() , '', $upload_dir['basedir']);
        $upload_dir = trim($upload_dir, '/');
      }
      $file = str_replace('{wp_content_dir}', $content_dir, $file);
      $file = str_replace('{plugins_dir}', $plugin_dir, $file);
      $file = str_replace('{uploads_dir}', $upload_dir, $file);

      return $file;
    }

    static function replace_folder_placeholders_to_uri($file) {
      static $content_uri, $plugins_uri, $uploads_uri;
      if (empty($content_uri)) {
        $content_uri = PhotuHelper::url_to_uri(content_url());
        $plugins_uri = PhotuHelper::url_to_uri(plugins_url());

        $upload_dir = PhotuHelper::wp_upload_dir();
        if (isset($upload_dir['baseurl'])) $uploads_uri = PhotuHelper::url_to_uri($upload_dir['baseurl']);
        else $uploads_uri = '';
      }
      $file = str_replace('{wp_content_dir}', $content_uri, $file);
      $file = str_replace('{plugins_dir}', $plugins_uri, $file);
      $file = str_replace('{uploads_dir}', $uploads_uri, $file);

      return $file;
    }

    static function get_array($key, $default = array()) {
      return (array)PhotuHelper::get($key, $default);
    }

    static function log_debug($event, $content) {
      if (defined('IK_DEBUG') && IK_DEBUG == true) {

        if (!array_key_exists($event, PhotuHelper::$debug_logs)) {
          PhotuHelper::$debug_logs[$event] = array();
        }

        array_push(PhotuHelper::$debug_logs[$event], $content);
      }
    }

    static function print_debug_logs($buffer) {
      $buffer = $buffer . "<!--";
      $buffer = $buffer . print_r(PhotuHelper::$debug_logs, true);
      $buffer = $buffer . "-->";
      return $buffer;
    }
  }
  
