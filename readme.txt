=== Pythia for Woocommerce ===
Contributors: PythiaBot
Tags: woocommerce, ecommerce, e-commerce, stats, tracking, analytics, woo, woo commerce
Requires at least: 4.9
Tested up to: 5.6
Requires PHP: 7.1
Stable tag: 1.1.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Pythia for Woocommerce is a Tracking Tool solution built on WooCommerce.

== Description ==

Pythia is a mobile-first business analytics platform built to help you to understand your business and online performance in an easy-to-digest format from a variety of sources. Current systems are often disjointed and cumbersome to navigate so pulling them together into a system that is easier to understand will give you all the information you need to take your business to the next level.

Pythia aims to provide a very simple and predefined interface to understand more about your business.

The Pythia Plugin for WooCommerce helps you quickly connect to the Pytyhia platform, so you can easily start understanding your business performance right away on your phone. 

No coding or extra steps are required! 

= Web Support =

If you want to get a free account you can access to [PythiaBot](https://www.pythiabot.com/).
If you need support you can write to us using our [Web Support](https://www.pythiabot.com/contact/).


== Installation ==

= Minimum Requirements =

* PHP 7.2 or greater is recommended
* MySQL 5.6 or greater is recommended

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of Pythia for Woocommerce, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”
 
In the search field type “Pythia for Woocommerce” then click “Search Plugins.” Once you’ve found the plugin you can install it by Clicking “Install Now,”.

= Manual installation =

Manual installation method requires downloading the Pythia for Woocommerce plugin and uploading it via your FTP application.  [You can find instructions in WordPress codex](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

= Updating =

Automatic updates should work but a backup of your website before doing it is recommended.

== Screenshots ==

1. The Pythia Registration screen used to setup your project and the account you will use to access to it.
2. Offer an option to connect your Pythia account with Google Analytics.
3. Screen to start existing data synchronization.
4. Synchronization status.
5. Screen displayed when existing data was synchronized successfully.

== Changelog ==
= 1.1.6 - 2021-01-18 =
* Fix – Fix error in front end alerts.

= 1.1.5 - 2021-01-17 =
* Dev – Allow people creating account without WooCommerce to use Analytics connector.

= 1.1.4 - 2020-08-20 =
* Enhancement – Restyling
* Fix – Uninstall action.
* Tweak – Hide advanced debug settings.
* Dev – Sync single order run separately.

= 1.1.3 - 2020-06-15 =
* Fix – Do not expire API token.
* Fix – Display correct error messages when synchronization fails.
* Dev – Disconnect account without resetting settings.

= 1.1.2 - 2020-05-08 =
* Enhancement – Add the option to change the number of orders to be synchronized per process.
* Enhancement – Display message and Google UA ID when an account was already authorized.
* Enhancement – After Login, select Project and Source automatically when only 1 is available.
* Enhancement – Display a notice when synchronization is disabled.
* Tweak – Improve Google Analytics Views list and sort by name.
* Tweak – Settings page display a link to Analytics page when any account is associated. 
* Tweak – Display success message after saving selected project in login page.
* Tweak – Standardize html notice file names.
* Tweak – Do not display project sources different than WooCommerce.
* Dev – Display the option to select Google Analytics View if the browser was closed before saving the ID.
* Dev – Add admin notices.
* Dev – Single order synchronization using WC Single Schedule.
* Dev – Stop synchronization if the domain is different than the domain used when the account was setup or when the user logged in.

= 1.1.1 - 2020-04-30 =
* Add sync log events.
* [BUGFIX] Remove setup message when token exists.
* [BUGFIX] Remove sync schedule when plugin is deactivated or settings reset.

= 1.1.0 - 2020-04-10 =
* Show last time Synchronization process ran
* Manual Synchronization run pending schedules
* Synchronization page run pending schedules automatically
* Display notice when synchronization process has pending schedules
* Improve synchronization progress UI
* API Debug with constant WC_PYTHIA_API_DEBUG
* Batch Synchronization

= 1.0.1 - 2019-10-22 =
* Order history synchronization
* Reset Settings Button
* Add pending setup notice
* Environment variable added to set API url. Define WC_PYTHIA_API_URL in wp-config.php to overwrite API url used to connect to Pythia

= 1.0.0 - 2019-10-15 =
* We are alive!
