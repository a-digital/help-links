<?php
/**
 * Help Links plugin for Craft CMS 3.x
 *
 * Define useful links to be added to the dashboard for clients.
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\helplinks\controllers;

use adigital\helplinks\HelpLinks;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;

/**
 * Cp Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    A Digital
 * @package   HelpLinks
 * @since     1.0.0
 */
class CpController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/help-links/cp
     *
     * @return mixed
     */
    public function actionIndex(string $subSection = 'overview', string $siteHandle = null)
    {
        $variables = [];

        $pluginName = "Help Links";
        $templateTitle = Craft::t('help-links', 'Sections');
        $subSectionTitle = Craft::t('help-links', ucfirst($subSection));
        // Basic variables
        $variables['pluginName'] = $pluginName;
        $variables['title'] = $templateTitle;
        $variables['selectedSubnavItem'] = 'sections';
        $variables['subSectionTitle'] = $subSectionTitle;
        $variables['settings'] = HelpLinks::$plugin->getSettings();
        $variables['sections'] = $variables['settings']['sections'];
        foreach ($variables['sections'] as $location) {
			$friendlyUrl = strtolower(str_replace([" ", "-"], ["", ""], $location[0]));
			if ($friendlyUrl === $subSection) {
				$variables['selectedItem'] = $location[0];
				$variables['subSectionTitle'] = $location[0];
			}
		}
        $variables['crumbs'] = [
            [
                'label' => $pluginName,
                'url' => UrlHelper::cpUrl('help-links'),
            ],
            [
                'label' => $templateTitle,
                'url' => UrlHelper::cpUrl('help-links/sections'),
            ]
        ];
        $variables['selectedSubnavItem'] = 'sections';
        $variables['currentSubSection'] = $subSection;

        // Enabled sites
        $variables['controllerHandle'] = 'sections'.'/'.$subSection;

        // Render the template
        $template = 'help-links/sections/index';
        if ($subSection == "overview") {
	        $variables['crumbs'][] = [
		        'label' => $subSectionTitle,
                'url' => UrlHelper::cpUrl('help-links/sections/').$subSection,
	        ];
	        $template = 'help-links/index';
        } else {
	        $variables['crumbs'][] = [
                'label' => $variables['selectedItem'],
                'url' => UrlHelper::cpUrl('help-links/sections/').$subSection,
            ];
	        $variables['fullPageForm'] = true;
	        $sectionSettings = HelpLinks::$plugin->helpLinksService->returnSection($variables['selectedItem']);
	        if ($sectionSettings !== false) {
		        $variables['sectionSettings'] = $sectionSettings;
	        }
        }
        return $this->renderTemplate($template, $variables);
    }

    /**
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \craft\errors\MissingComponentException
     */
    public function actionSaveSections()
    {
        $request = Craft::$app->getRequest();
        HelpLinks::$plugin->helpLinksService->saveSection($request);
        Craft::$app->getSession()->setNotice(Craft::t('help-links', 'Help Links authority saved.'));
        return $this->redirectToPostedUrl();
    }
    
    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/help-links/cp/plugin
     *
     * @return mixed
     */
    public function actionPlugin()
    {
	    $variables = [];
	    $variables['selectedSubnavItem'] = 'plugin';
	    $variables['crumbs'] = [
            [
                'label' => "Help Links",
                'url' => UrlHelper::cpUrl('help-links')
            ],
            [
                'label' => "Plugin Settings",
                'url' => UrlHelper::cpUrl('help-links/plugin')
            ]
        ];
        $variables['settings'] = HelpLinks::$plugin->getSettings();
	    
	    return $this->renderTemplate('help-links/settings', $variables);
    }
    
    /**
     * Saves a plugin’s settings.
     *
     * @return Response|null
     * @throws NotFoundHttpException if the requested plugin cannot be found
     * @throws \yii\web\BadRequestHttpException
     * @throws \craft\errors\MissingComponentException
     */
    public function actionSavePluginSettings()
    {
        $this->requirePostRequest();
        $pluginHandle = Craft::$app->getRequest()->getRequiredBodyParam('pluginHandle');
        $settings = Craft::$app->getRequest()->getBodyParam('settings', []);
        $plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);

        if ($plugin === null) {
            throw new NotFoundHttpException('Plugin not found');
        }

        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings)) {
            Craft::$app->getSession()->setError(Craft::t('app', "Couldn't save plugin settings."));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin,
            ]);

            return null;
        }
        
        $settings = HelpLinks::$plugin->getSettings();
        foreach($settings["sections"] as $section) {
	        HelpLinks::$plugin->helpLinksService->createSection($section[0], $settings);
        }

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
        return $this->redirectToPostedUrl();
    }
}
