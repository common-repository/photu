=== Photu - URL based image manipulation and optimization ===
Contributors: Photu
Donate link: 
Tags: images,image management, image manipulation, image optimization, image optimisation,photu, wepb,photo, photos, picture, pictures, thumbnail, thumbnails, upload, batch, cdn, content delivery network
Requires at least: 3.3
Tested up to: 5.8
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Faster & lighter experience for your users. Deliver optimized images on all platforms instantly using Photu.

== Description ==

Images make up a critical part of all websites and mobile applications these days. They are the centerpieces of a great product and user experience. Managing your images and delivering the perfect image, tailored and optimized for your user’s device is, therefore, more critical than it has ever been. However, this takes up a lot of development and maintenance time that could have otherwise been used in building your core product. This is where Photu can excel.

This plugin will **automatically update all the image URLs in your post** so that images are fetched from Photu for optimization and faster delivery instead of your web server.

= Gets the best out of all your images in less than 10 minutes =

* Your existing images get all the benefits instantly.
* Size, quality & format optimizations work automatically.
* URL-based image transformations like resize, crop, rotate etc.
* Responsive images for a tailored experience across devices.
* Up to 50% load time reduction with quality and format settings.
* CDN-powered delivery of images across the globe.
* Simple dashboard to monitor usage and manage your images.
* Easy to integrate SDKs for uploads and other features.

= Requirements =

You just need to [Create an account](https://admin.mogiio.com/) on Photu to use this plugin and get optimization benefits on your WordPress website instantly.

= About Photu =

* [Main website](https://mogiio.com)
* [Website analyzer](https://aim.stag-z.mogiio.com/)
* [Help center](Write to us. At support@mogiio.com)
* [Developer documentation](https://docs.google.com/document/d/1XqyJLm6F4qL10ew9NjNVuNo2f1yBWFcooYYiyFMdRuc)

= Support =

* Support Email: [support@mogiio.com](support@mogiio.com)


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Photu setting screen to configure the plugin. Check out [WordPress integration guide](https://docs.google.com/document/d/1P8sulREpvvAc00Mgc0Z25FGjEdkhQKZi-kcSlNRmAQU/edit)

== Frequently asked questions ==

= Do I have to register on Photu to use this module? =

Yes, you need to [create an account](https://admin.mogiio.com/#/auth/register) on https://admin.mogiio.com/#/auth/register first to use this plugin.

= How does this plugin works? =

This plugin changes the HTML content of the post to replace base URL with Photu endpoint so that images are loaded via Photu.

= I installed the plugin but Google pagespeed insights is still showing image related warnings =

This plugin automatically optimize the images and serve them in next-gen format including WebP. However, this plugin does not automatically resize the images as per the layout. WordPress 4.4 has [added native support for responsive image](https://make.wordpress.org/core/2015/11/10/responsive-images-in-wordpress-4-4/). [Learn more](https://viastudio.com/optimizing-your-theme-for-wordpress-4-4s-responsive-images/) to make your themes image responsive.

= Do I have to manually change the old posts to optimize their images? =

No, this plugin automatically takes care of that.

= Does this plugin support custom CNAME? =

Yes, you can email tech@mogiio.com to configure custom CNAME for your account and then specify that in the plugin setting page.

= Can I configure this plugin to use Photu for custom upload directories? =

Yes, you can specify any number of custom directory locations on plugin settings page

= Does Photu support all image formats? =

Photu supports all popular image formats that cover 99.99% of the use case. On the settings page, you can further configure if you want to allow or disallow a particular file type to be loaded via Photu.

= I installed the plugin, but the Photu website analyzer is suggesting more optimization. =

This is because image dimensions are not as per the layout. We could have done it using Javascript in the frontend like other plugins, but we do not recommend it. The browser triggers the image load as soon as it sees an image URL in HTML and intentionally delaying this while Javascript calculates the ideal width will ultimately slow down the image load for your users. WordPress 4.4 has [added native support for a responsive image](https://make.wordpress.org/core/2015/11/10/responsive-images-in-wordpress-4-4/). [Learn more](https://viastudio.com/optimizing-your-theme-for-wordpress-4-4s-responsive-images/) to make your themes image responsive.


== Screenshots ==

1

== Changelog ==

1.2
Plugin release - UI updated. New Photu plugin for Photu image delivery Powered by Mogi I/O

1.1
Plugin release. New Photu plugin for Photu image delivery Powered by Mogi I/O

1.0
Plugin initial release. New Photu plugin for Photu image delivery Powered by Mogi I/O

== Upgrade notice ==

Updated integration steps and documentation links.

== Additional Info ==
The url photu/includes/setting.php:80: <img src="https://mogiio.com/assets/img/mogiio-logo-dark.png" height="32"> used here is our own url and website
We have exclusive right to this logo and just are using the link from it's assets folder. 
