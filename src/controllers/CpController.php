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
use craft\errors\MissingComponentException;
use craft\web\Controller;
use craft\helpers\UrlHelper;
use JsonException;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
    protected int|bool|array $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/help-links/cp
     *
     * @param string $subSection
     * @return Response
     * @throws JsonException
     */
    public function actionIndex(string $subSection = 'overview'): Response
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
        $variables['currentSubSection'] = $subSection;

        // Enabled sites
        $variables['controllerHandle'] = 'sections'.'/'.$subSection;

        // Render the template
        $template = 'help-links/sections/index';
        if ($subSection != "overview") {
	        $variables['fullPageForm'] = true;
	        $sectionSettings = HelpLinks::$plugin->helpLinksService->returnSection($variables['selectedItem']);
	        if ($sectionSettings !== false) {
		        $variables['sectionSettings'] = $sectionSettings;
	        }
	        $template = 'help-links/sections/detail';
        }
        return $this->renderTemplate($template, $variables);
    }

    /**
     * @return Response
     * @throws MissingComponentException
     * @throws BadRequestHttpException
     */
    public function actionSaveSections(): Response
    {
        $request = Craft::$app->getRequest();
        HelpLinks::$plugin->helpLinksService->saveSection($request);
        Craft::$app->getSession()->setNotice(Craft::t('help-links', 'Help Links section saved.'));
        return $this->redirectToPostedUrl();
    }

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/help-links/cp/plugin
     *
     * @return Response
     */
    public function actionRename(): Response
    {
	    $variables = [];
	    $variables['fullPageForm'] = true;
	    $variables['selectedSubnavItem'] = 'rename';
	    $variables['crumbs'] = [
            [
                'label' => "Help Links",
                'url' => UrlHelper::cpUrl('help-links')
            ]
        ];
        $variables['settings'] = HelpLinks::$plugin->getSettings();
	    
	    return $this->renderTemplate('help-links/rename', $variables);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws NotFoundHttpException
     */
    public function actionSaveRename(): Response
    {
        $request = Craft::$app->getRequest();
        HelpLinks::$plugin->helpLinksService->saveRename($request);
        Craft::$app->getSession()->setNotice(Craft::t('help-links', 'Help Links headings renamed.'));
        return $this->redirectToPostedUrl();
    }

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/help-links/cp/plugin
     *
     * @return Response
     */
    public function actionPlugin(): Response
    {
	    $variables = [];
	    $variables['selectedSubnavItem'] = 'plugin';
	    $variables['crumbs'] = [
            [
                'label' => "Help Links",
                'url' => UrlHelper::cpUrl('help-links')
            ]
        ];
        $variables['settings'] = HelpLinks::$plugin->getSettings();
	    
	    return $this->renderTemplate('help-links/settings', $variables);
    }

    /**
     * Saves a plugin’s settings.
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionSavePluginSettings(): ?Response
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
        
        $sections = [];
        $settings = HelpLinks::$plugin->getSettings();
        $count = 1;
        foreach($settings["sections"] as $section) {
	        HelpLinks::$plugin->helpLinksService->createSection($section[0], $count);
	        $sections[] = $section[0];
	        $count++;
        }
        HelpLinks::$plugin->helpLinksService->removeSections($sections);

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
        return $this->redirectToPostedUrl();
    }

    /**
     * Exports a plugin’s settings.
     *
     * @return false|string
     * @throws JsonException
     */
    public function actionExport(): bool|string
    {
        $pluginSettings = HelpLinks::$plugin->getSettings();
        $settings = [];
        $settings["plugin"]["widgetTitle"] = $pluginSettings["widgetTitle"];
        foreach($pluginSettings["sections"] as $section) {
	        $sectionSettings = HelpLinks::$plugin->helpLinksService->returnSection($section[0]);
	        $settings["plugin"]["sections"][] = [$sectionSettings["heading"]];
	        $settings["sections"][$sectionSettings["heading"]] = $sectionSettings["links"];
        }
        $json = json_encode($settings, JSON_THROW_ON_ERROR);
        header('Content-disposition: attachment; filename=helplinks_settings.json');
		header('Content-type: application/json');
        return $json;
    }

    /**
     * Imports a plugin’s settings.
     *
     * @return Response
     */
    public function actionImport(): Response
    {
        $variables = [];
	    $variables['selectedSubnavItem'] = 'import';
	    $variables['crumbs'] = [
            [
                'label' => "Help Links",
                'url' => UrlHelper::cpUrl('help-links')
            ]
        ];
        $variables['settings'] = HelpLinks::$plugin->getSettings();
	    
	    return $this->renderTemplate('help-links/import', $variables);
    }

    /**
     * Processes a JSON file to import a plugin’s settings.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws JsonException
     * @throws MissingComponentException
     * @throws StaleObjectException
     */
    public function actionProcessImport(): Response
    {
	    $this->requirePostRequest();
	    if (count($_FILES)) {
		    HelpLinks::$plugin->helpLinksService->importSettings($_FILES);
	    }
	    Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings imported.'));
	    return $this->redirectToPostedUrl();
    }
}
