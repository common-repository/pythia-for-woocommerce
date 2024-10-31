# Pythia for Woocommerce
Contributors: PythiaBot
Tags: ecommerce, e-commerce, stats, tracking, analytics, woocommerce, woo, woo commerce
Requires at least: 4.9
Tested up to: 5.5
Requires PHP: 7.2
Stable tag: 1.1.0

Pythia for Woocommerce is a Tracking Tool solution built on WooCommerce.

## Description

Pythia for Woocommerce is a Tracking Tool solution built on WooCommerce.

Activate the free Pythia for Woocommerce plugin on a new or existing WordPress site and start tracking your sales to improve your goals.

## Installation

### Minimum Requirements

* PHP 7.2 or greater is recommended
* MySQL 5.6 or greater is recommended

### Automatic installation

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of Pythia for Woocommerce, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”
 
In the search field type “Pythia for Woocommerce” then click “Search Plugins.” Once you’ve found the plugin you can install it by Clicking “Install Now,”.

### Manual installation

Manual installation method requires downloading the Pythia for Woocommerce plugin and uploading it via your FTP application.  [You can find instructions in WordPress codex](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

### Updating

Automatic updates should work but a backup of your website before doing it is recommended.

### Screenshots

1. The Pythia Registration screen used to setup your project and the account you will use to access to it.
2. Offer an option to connect your Pythia account with Google Analytics.
3. Screen to start existing data synchronization.
4. Synchronization status.
5. Screen displayed when existing data was synchronized successfully.

### Changelog
#### 1.1.3 - 2020-05-08
* Fix – Do not expire API token.
* Fix – Display correct error messages when synchronization fails.
* Dev – Disconnect account without resetting settings.

#### 1.1.2 - 2020-05-08
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

#### 1.1.1 - 2020-04-30
* Add sync log events.
* Fix - Remove setup message when token exists.
* Fix - Remove sync schedule when plugin is deactivated or settings reset.

#### 1.1.0 - 2020-04-10
* Show last time Synchronization process ran
* Manual Synchronization run pending schedules
* Synchronization page run pending schedules automatically
* Display notice when synchronization process has pending schedules
* Improve synchronization progress UI
* API Debug with constant WC_PYTHIA_API_DEBUG
* Batch Synchronization

#### 1.0.1 - 2019-10-22
* Order history synchronization
* Reset Settings Button
* Add pending setup notice
* Environment variable added to set API url. Define WC_PYTHIA_API_URL in wp-config.php to overwrite API url used to connect to Pythia

#### 1.0.0 - 2019-10-15
* We are alive!
