<?php
require_once (ABSPATH . 'wp-admin/includes/plugin-install.php');

function get_photu_plugin_file($plugin_slug) {
  require_once (ABSPATH . '/wp-admin/includes/plugin.php');
  $plugins = get_plugins();

  foreach ($plugins as $plugin_file => $plugin_info) {
    $slug = dirname(plugin_basename($plugin_file));
    if ($slug) {
      if ($slug == $plugin_slug) {
        return $plugin_file;
      }
    }
  }
  return null;
}

function check_photu_file_extension($filename) {
  if (substr(strrchr($filename, '.') , 1) === 'php') {
    return true;
  }
  else {
    return false;
  }
}

function photu_render_photu_setting_page() {
  global $photu_options;

  $plugin = "wp-lazy-loading";

  $api = plugins_api('plugin_information', array(
    'slug' => $plugin,
    'fields' => array(
      'short_description' => true,
      'sections' => false,
      'requires' => false,
      'downloaded' => true,
      'last_updated' => false,
      'added' => false,
      'tags' => false,
      'compatibility' => false,
      'homepage' => false,
      'donate_link' => false,
      'icons' => true,
      'banners' => true,
    ) ,
  ));

  $photu_options["cname"] = !empty($photu_options["cname"]) ? $photu_options["cname"] : "";
  $photu_options["file_type"] = !empty($photu_options["file_type"]) ? $photu_options["file_type"] : "*.gif;*.png;*.jpg;*.jpeg;*.bmp;*.ico;*.webp";
  $photu_options["custom_files"] = !empty($photu_options["custom_files"]) ? $photu_options["custom_files"] : "favicon.ico\ncustom-directory";
  $photu_options["reject_files"] = !empty($photu_options["reject_files"]) ? $photu_options["reject_files"] : "wp-content/uploads/wpcf7_captcha/*\nwp-content/uploads/imagerotator.swf\ncustom-directory*.mp4";

  if (empty($photu_options["photu_url_endpoint"])) {
    if (empty($photu_options['photu_id']) && empty($photu_options['cname'])) {
      $photu_options["photu_url_endpoint"] = "";
    }
    else if (!empty($photu_options['cname'])) {
      $photu_options["photu_url_endpoint"] = $photu_options['cname'];
    }
    else if (!empty($photu_options['photu_id'])) {
      $photu_options["photu_url_endpoint"] = "https://apis-z.mogiio.com/mogi-enhance/" . $photu_options['photu_id'] . "/fwebp,q80,ptrue";
    }
  }

  ob_start();

  wp_enqueue_style('xyz', plugins_url('photu_wordpress') . '/includes/main.css');
?>
<div>
   <div id="ik-plugin-container">
      <div>
         <div>
            <div class="ik-masthead">
               <div class="ik-masthead__inside-container">
                  <div class="ik-masthead__logo-container">
                     <a class="ik-masthead__logo-link" href="#">
                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>mogiio-logo-dark.png" class="photu-logo__masthead" height="32">
                     </a>
                  </div>
               </div>
            </div>
            <div class="ik-lower">
                <div class="ik-settings-container">
                    <div>
                        <div class="dops-card ik-settings-description">
                           <h2 class="dops-card-title">Steps to configure Photu</h2>
                           <h4>If you haven't created an account with photu yet, then the first step is to 
            <a href="https://admin.mogiio.com" target="_blank">register</a>.
            
            After sign-up, check out <a href="https://docs.google.com/document/d/1blXyGHgBetkecFp1az3NQmILSOd6n1Y6T8oW-WZUUU8" target="_blank">WordPress integration guide</a>.</h4>
                        </div>
                    </div>
                    <form method="post" action="options.php">
                        <?php settings_fields('photu_settings_group'); ?>
                        <div class="ik-form-settings-group">
                          <div class="dops-card ik-form-has-child">
                            <fieldset class="ik-form-fieldset">
                                <label class="ik-form-label"><span class="ik-form-label-wide"><?php _e('Photu URL endpoint (or CNAME)', 'photu_domain'); ?></span>
                                  <input id="photu_settings[photu_url_endpoint]" 
										 type="text" 
										 class="dops-text-input" 
                                         name="photu_settings[photu_url_endpoint]" 
                                         value="<?php echo isset($photu_options['photu_url_endpoint']) ? esc_url($photu_options['photu_url_endpoint']) : ''; ?>" />
                                </label>
                                <span class="ik-form-setting-explanation">
									Copy paste the Photu URL endpoint (or CNAME) <a href="https://admin.mogiio.com/#/photu/dashboard" target="_blank">dashboard</a>. 
									<a href="https://docs.google.com/document/d/1P8sulREpvvAc00Mgc0Z25FGjEdkhQKZi-kcSlNRmAQU" target="_blank">Learn more</a>
									
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="ik-form-settings-group">
                          <div class="dops-card ik-form-has-child">
                            <fieldset class="ik-form-fieldset">
								<label class="ik-form-label"><span class="ik-form-label-wide"><?php _e('File types', 'photu_domain'); ?></span>
									<input id="photu_settings[file_type]" 
										   type="text"
										   name="photu_settings[file_type]" 
										   value="<?php echo isset($photu_options['file_type']) ? esc_url($photu_options['file_type']) : '' ?>" 
										   class="dops-text-input" />
                                </label>
                                <span class="ik-form-setting-explanation">
									Specify the file types that you want to be loaded via Photu
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="ik-form-settings-group">
                          <div class="dops-card ik-form-has-child">
                            <fieldset class="ik-form-fieldset">
								<label class="ik-form-label"><span class="ik-form-label-wide"><?php _e('Custom files', 'photu_domain');; ?></span>
									<textarea id="photu_settings[custom_files]" 
											  name="photu_settings[custom_files]"
											  class="dops-text-input"
											  cols="40" 
											  rows="5"><?php echo isset($photu_options['custom_files']) ? esc_url($photu_options['custom_files']) : '' ?></textarea>
                                </label>
                                <span class="ik-form-setting-explanation">
									Specify any files or directories outside of theme or other common directories to be loaded via Photu
								</span>
							</fieldset>
						  </div>
						</div>
						<div class="ik-form-settings-group">
                          <div class="dops-card ik-form-has-child">
                            <fieldset class="ik-form-fieldset">
								<label class="ik-form-label"><span class="ik-form-label-wide"><?php _e('Rejected files', 'photu_domain');; ?></span>
									<textarea id="photu_settings[reject_files]" 
											  name="photu_settings[reject_files]"
											  class="dops-text-input"
											  cols="40" 
											  rows="5"><?php echo isset($photu_options['reject_files']) ? esc_url($photu_options['reject_files']) : ''; ?></textarea>
                                </label>
                                <span class="ik-form-setting-explanation">
									Specify any files or directories that you do not want to load via Photu
								</span>
                            </fieldset>
						  </div>
						</div>
						<div class="ik-form-settings-group">
                          <div class="dops-card ik-form-has-child">

							<fieldset class="ik-form-fieldset">
								<label class="ik-form-label"><span class="ik-form-label-wide"><?php _e('Lazy Load Images', 'photu_domain');; ?></span></label>
								<p>Lazy loading images will improve your site’s speed and create a smoother viewing experience. Images will load as visitors scroll down the screen, instead of all at once.</p>
								<?php
  $wp_version = (float)get_bloginfo('version');
  if (5.5 <= $wp_version):
?>
									<p>With the release of Version 5.5 of Wordpress Core, <a href="https://wordpress.org/support/wordpress-version/version-5-5/#speed" target="_blank">Lazy-Loading of images</a> has been introduced as a core feature and is enabled by default.</p>

									<?php if (!is_wp_error($api)):
      $main_plugin_file = get_photu_plugin_file($plugin); ?>
										<?php if (check_photu_file_extension($main_plugin_file)): ?>
											<p>We have detected that you are using the <a href="https://wordpress.org/plugins/wp-lazy-loading" target="_blank">Lazy Loading Feature Plugin</a>, you can proceed uninstall it, since it is no longer required.</p>
										   <?php
      endif; ?>
										<?php
    endif; ?>
								<?php
  else: ?>
									<p>For lazy loading, we recommend the <a href="https://wordpress.org/plugins/wp-lazy-loading" target="_blank">Lazy Loading Feature Plugin</a> developed by the WordPress Core Team. This feature has been built into WordPress core since version 5.5 (<a href="https://wordpress.org/support/wordpress-version/version-5-5/#speed" target="_blank">Read More</a>). </p>

									<?php if (!is_wp_error($api)):
      $main_plugin_file = get_photu_plugin_file($plugin); ?>
									<div class="plugin">
									  <div class="plugin-wrap">
										  <img src="<?php echo esc_url($api->icons['default']); ?>" alt="">
									   <h2><?php echo esc_url($api->name); ?></h2>
									   <p><?php echo esc_url($api->short_description); ?></p>

									   <p class="plugin-author"><?php _e('By', 'photu_domain'); ?> <?php echo esc_url($api->author); ?></p>
									   </div>
									   <ul class="activation-row">
									   <?php if (check_photu_file_extension($main_plugin_file)): ?>
											<?php if (is_plugin_active($main_plugin_file)): ?>
											   <li>
												   <a class="button disabled">Activated</a>
											   </li>
										   <?php
        else: ?>
												<li>
												   <a class="activate button button-primary" href="plugins.php?action=activate&amp;plugin=<?php echo $main_plugin_file ?>&amp;_wpnonce=<?php echo wp_create_nonce('activate-plugin_' . $main_plugin_file) ?>" target="_parent">Activate Plugin</a>
											   </li>
										   <?php
        endif; ?>
									   <?php
      else: ?>
									   <li>
										  <a class="install button"
											href="<?php echo esc_url(get_admin_url()); ?>/update.php?action=install-plugin&amp;plugin=<?php echo esc_url($api->slug); ?>&amp;_wpnonce=<?php echo wp_create_nonce('install-plugin_' . $api->slug) ?>">
											Install Now
										  </a>
									   </li>
									   <?php
      endif; ?>
									   <li>
										  <a href="https://wordpress.org/plugins/<?php echo esc_url($api->slug); ?>/" target="_blank">
											 <?php _e('More Details', 'photu_domain'); ?>
										  </a>
									   </li>
									</ul>
								   </div>
									<?php
    endif; ?>
								<?php
  endif; ?>
							 </fieldset>
						  </div>
						</div>
						<div class="ik-form-settings-group">
                          <div class="dops-card ik-form-has-child">
							<fieldset class="ik-form-fieldset">
								<label class="ik-form-label">
									<input type="submit" class="button-primary" value="<?php _e('Save changes', 'photu_domain'); ?>" />
                                </label>
                                <span class="ik-form-setting-explanation">
									Once you save settings, this plugin will load all post images via Photu. If you face any problem, reach out to us at <a href="mailto:mehtab.akhtar@mogiio.com" target="_blank">support@mogiio.com</a> or <a href="https://docs.google.com/document/d/1blXyGHgBetkecFp1az3NQmILSOd6n1Y6T8oW-WZUUU8" target="_blank">read docs</a>.
								</span>
                            </fieldset>
                          </div>
                      </div>
                    </form>
                </div>
                
            </div>
			<div class="ik-footer">
				<?php $plugin_data = get_plugin_data(IK_PLUGIN_ENTRYPOINT); ?>
			    <ul class="ik-footer__links">
				    <li class="ik-footer__link-item"><a href="https://mogiio.com/photu/" target="_blank" rel="noopener noreferrer" class="ik-footer__link"><?php echo esc_url($plugin_data['Name']) ?> version <?php echo esc_url($plugin_data['Version']) ?></a></li>
				</ul>
			</div>
         </div>
      </div>
   </div>
</div>
<?php
  echo ob_get_clean();
}

function photu_add_setting_link() {
  add_options_page('photu settings', 'photu settings', 'manage_options', 'photu-setting', 'photu_render_photu_setting_page');
}
add_action('admin_menu', 'photu_add_setting_link');

function photu_register_settings() {
  add_filter('admin_body_class', function ($classes) {
    $classes .= ' ' . 'photu-pagestyles ';
    return $classes;
  });
  register_setting('photu_settings_group', 'photu_settings');
}

add_action('admin_init', 'photu_register_settings');

?>
