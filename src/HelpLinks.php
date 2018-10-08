<?php
/**
 * Help Links plugin for Craft CMS 3.x
 *
 * Define useful links to be added to the dashboard for clients.
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\helplinks;

use adigital\helplinks\models\Settings;
use adigital\helplinks\widgets\HelpLinksWidget as HelpLinksWidgetWidget;
use adigital\helplinks\services\HelpLinksService as HelpLinksServiceService;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\services\Dashboard;
use craft\services\UserPermissions;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    A Digital
 * @package   HelpLinks
 * @since     1.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class HelpLinks extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * HelpLinks::$plugin
     *
     * @var HelpLinks
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * HelpLinks::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        
        // Install our event listeners
        $this->installEventListeners();

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'help-links',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }
    
    /**
     * Install our event listeners.
     */
    protected function installEventListeners()
    {
        $request = Craft::$app->getRequest();
        // Add in our event listeners that are needed for every request
        $this->installGlobalEventListeners();
        // Install only for non-console site requests
        if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
            $this->installSiteEventListeners();
        }
        // Install only for non-console AdminCP requests
        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            $this->installCpEventListeners();
        }
    }
    
    /**
     * Install global event listeners for all request types
     */
    protected function installGlobalEventListeners()
    {
	    // Register our widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = HelpLinksWidgetWidget::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );
    }
    
    /**
     * Install site event listeners for site requests only
     */
    protected function installSiteEventListeners()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                Craft::debug(
                    'UrlManager::EVENT_REGISTER_SITE_URL_RULES',
                    __METHOD__
                );
                $event->rules['siteActionTrigger1'] = 'help-links/default';
            }
        );
    }
    
    /**
     * Install site event listeners for AdminCP requests only
     */
    protected function installCpEventListeners()
    {
        // Handler: UrlManager::EVENT_REGISTER_CP_URL_RULES
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                Craft::debug(
                    'UrlManager::EVENT_REGISTER_CP_URL_RULES',
                    __METHOD__
                );
                // Register our AdminCP routes
                $event->rules = array_merge(
                    $event->rules,
                    $this->customAdminCpRoutes()
                );
            }
        );
        // Handler: UserPermissions::EVENT_REGISTER_PERMISSIONS
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                Craft::debug(
                    'UserPermissions::EVENT_REGISTER_PERMISSIONS',
                    __METHOD__
                );
                // Register our custom permissions
                $event->permissions[Craft::t('help-links', 'Help Links')] = $this->customAdminCpPermissions();
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'help-links/settings/plugin',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $subNavs = [];
        $show = false;
        $navItem = parent::getCpNavItem();
        /** @var User $currentUser */
        $currentUser = Craft::$app->getUser()->getIdentity();
        // Only show sub-navs the user has permission to view
        if ($currentUser->can('helpLinks:sections')) {
	        $subNavs['sections'] = [
	            'label' => 'Sections',
	            'url' => 'help-links',
	        ];
	        $show = true;
	    }
	    if ($currentUser->can('helpLinks:settings')) {
	        $subNavs['plugin'] = [
	            'label' => 'Plugin Settings',
	            'url' => 'help-links/plugin',
	        ];
	        $show = true;
        }
        if ($currentUser->can('helpLinks:importExport')) {
	        $subNavs['import'] = [
	            'label' => 'Import / Export',
	            'url' => 'help-links/import',
	        ];
	        $show = true;
        }
        if ($show === true) {
	        $navItem = array_merge($navItem, [
	            'subnav' => $subNavs,
	        ]);
        }

        return $navItem;
    }
    
    /**
     * Return the custom AdminCP routes
     *
     * @return array
     */
    protected function customAdminCpRoutes(): array
    {
        return [
            'help-links' => 'help-links/cp',
            
            'help-links/sections' => [
                'route' => 'help-links/cp',
                'defaults' => ['subSection' => 'overview']
            ],
            'help-links/sections/<subSection:{handle}>' => 'help-links/cp',

            'help-links/plugin' => 'help-links/cp/plugin',
            
            'help-links/export' => 'help-links/cp/export',
            
            'help-links/import' => 'help-links/cp/import',
            
            'help-links/import/process' => 'help-links/cp/processImport'
        ];
    }
    
    /**
     * Returns the custom AdminCP user permissions.
     *
     * @return array
     */
    protected function customAdminCpPermissions(): array
    {
        // The script meta containers for the global meta bundle
        try {
            $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id ?? 1;
        } catch (SiteNotFoundException $e) {
            $currentSiteId = 1;
        }

        return [
            'helpLinks:sections' => [
                'label' => Craft::t('help-links', 'Manage the widgets links under each heading'),
            ],
            'helpLinks:settings' => [
                'label' => Craft::t('help-links', 'Manage the headings available to the widget'),
            ],
            'helpLinks:importExport' => [
                'label' => Craft::t('help-links', 'Import / Export the settings and links'),
            ],
        ];
    }
}
