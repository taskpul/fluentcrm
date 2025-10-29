<?php

!defined('WPINC') && die;

if (!defined('FLUENTCAMPAIGN_DIR_FILE')) {
    define('FLUENTCAMPAIGN_DIR_FILE', __FILE__);
}

define('FLUENTCAMPAIGN', 'fluentcampaign');
define('FLUENTCAMPAIGN_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FLUENTCAMPAIGN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FLUENTCAMPAIGN_PLUGIN_VERSION', '2.9.84');
define('FLUENTCAMPAIGN_CORE_MIN_VERSION', '2.9.84');
define('FLUENTCAMPAIGN_FRAMEWORK_VERSION', 3);

spl_autoload_register(function ($class) {
    $match = 'FluentCampaign';
    if (!preg_match("/\b{$match}\b/", $class)) {
        return;
    }

    $path = FLUENTCAMPAIGN_PLUGIN_PATH;
    $file = str_replace(
        ['FluentCampaign', '\\', '/App/'],
        ['', DIRECTORY_SEPARATOR, 'app/'],
        $class
    );
    $filePath = trailingslashit($path) . trim($file, '/') . '.php';

    if (file_exists($filePath)) {
        require $filePath;
    }
});

add_action('init', function () {
    load_plugin_textdomain('fluentcampaign-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('plugins_loaded', function () {
    if (!class_exists('FluentCampaign\\App\\Services\\PluginManager\\LicenseManager')) {
        return;
    }

    $licenseManager = new \FluentCampaign\App\Services\PluginManager\LicenseManager();
    $licenseManager->initUpdater();

    $licenseMessage = $licenseManager->getLicenseMessages();

    if ($licenseMessage) {
        add_action('admin_notices', function () use ($licenseMessage) {
            if (defined('FLUENTCRM')) {
                $class = 'notice notice-error fc_message';
                $message = $licenseMessage['message'];
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
            }
        });
    }
}, 0);

add_action('fluentcrm_loaded', function ($app) {
    if (!defined('FLUENTCRM_FRAMEWORK_VERSION') || FLUENTCRM_FRAMEWORK_VERSION < 3) {
        add_action('admin_notices', function () {
            echo '<div class="fc_notice notice notice-error fc_notice_error"><h3>Update FluentCRM Plugin</h3><p>FluentCRM Pro requires the latest version of the FluentCRM Core Plugin. <a href="' . admin_url('plugins.php?s=fluent-crm&plugin_status=all') . '">' . __('Please update FluentCRM to latest version', 'fluentcampaign-pro') . '</a>.</p></div>';
        });
        return;
    }

    (new \FluentCampaign\App\Application($app));
    do_action('fluentcampaign_loaded', $app);
});
