<?php
/**
 * Help Links plugin for Craft CMS 3.x
 *
 * Define useful links to be added to the dashboard for clients.
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\helplinks\services;

use adigital\helplinks\HelpLinks;
use adigital\helplinks\records\Sections as SectionsRecord;

use Craft;
use craft\base\Component;
use craft\errors\MissingComponentException;
use JsonException;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;

/**
 * HelpLinksService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    A Digital
 * @package   HelpLinks
 * @since     1.0.0
 */
class HelpLinksService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->returnSection()
     *
     * @param $section
     * @return array|false
     * @throws JsonException
     */
    public function returnSection($section): bool|array
    {
        $model = SectionsRecord::findOne(["heading" => $section]);
        if ($model === null) {
        	return false;
        }
        $attributes = $model->getAttributes();
        $attributes["links"] = json_decode($attributes["links"], true, 512, JSON_THROW_ON_ERROR);
        return $attributes;
    }

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->createSection()
     *
     * @param $section
     * @param $count
     * @return SectionsRecord
     */
    public function createSection($section, $count): SectionsRecord
    {
        $modelSection = [
	        "links" => "[['', '', '']]",
	        "position" => $count
        ];
        
        $model = SectionsRecord::findOne(['heading' => $section]);
        if ($model !== null) {
	        $model->setAttribute("position", $count);
	        $model->save();
	        return $model;
	    }
	    
    	$model = new SectionsRecord();
    	$modelSection["heading"] = $section;
    	$model->setAttributes($modelSection, false);
        $model->save();
        return $model;
    }

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->saveSection()
     *
     * @param $request
     * @return SectionsRecord|null
     */
    public function saveSection($request): ?SectionsRecord
    {
	    $modelSection = [
	        "links" => array_merge($request->getParam("links"))
        ];
        
        $model = SectionsRecord::findOne(['heading' => $request->getParam("heading")]);
        if ($model === null) {
	    	$model = new SectionsRecord();
	    	$modelSection["heading"] = $request->getParam("heading");
	    }
        $model->setAttributes($modelSection, false);
        $model->save();
        return $model;
    }

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->importSection()
     *
     * @param string $title
     * @param array $links
     * @param int $count
     * @return SectionsRecord|null
     */
    public function importSection(string $title, int $count, array $links = []): ?SectionsRecord
    {
        $modelSection = [
	        "links" => $links,
	        "position" => $count
        ];
        
        $model = SectionsRecord::findOne(['heading' => $title]);
        if ($model === null) {
	    	$model = new SectionsRecord();
	    	$modelSection["heading"] = $title;
	    }
        $model->setAttributes($modelSection, false);
        $model->save();
        return $model;
    }

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->generateSection()
     *
     * @param $request
     * @return SectionsRecord|null
     */
    public function generateSection($request): ?SectionsRecord
    {
        $modelSection = [
	        "links" => $request["links"]
        ];
        
        $model = SectionsRecord::findOne(['heading' => $request["heading"]]);
        if ($model === null) {
	    	$model = new SectionsRecord();
	    	$modelSection["heading"] = $request["heading"];
	    }
        $model->setAttributes($modelSection, false);
        $model->save();
        return $model;
    }

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->importSettings()
     *
     * @param $attachments
     * @return bool
     * @throws JsonException
     * @throws StaleObjectException
     */
    public function importSettings($attachments): bool
    {
	    $file = $attachments["jsonSettings"]["tmp_name"];
	    
	    $fileData = file_get_contents($file);
	    $jsonSettings = json_decode($fileData, false, 512, JSON_THROW_ON_ERROR);
	    
	    $pluginSettings = (array)$jsonSettings->plugin;
	    $sectionSettings = $jsonSettings->sections;
	    
	    $plugin = Craft::$app->getPlugins()->getPlugin("help-links");
        if (!$plugin) {
            return false;
        }
	    Craft::$app->getPlugins()->savePluginSettings($plugin, $pluginSettings);
	    $sections = [];
	    $count = 1;
	    foreach($sectionSettings as $key => $section) {
	        $this->importSection($key, $count, $section);
	        $sections[] = $key;
	        $count++;
        }
        
        $this->removeSections($sections);
	    
		return true;
    }

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->removeSections()
     *
     * @param $sectionTitles
     * @return bool
     * @throws StaleObjectException
     */
    public function removeSections($sectionTitles): bool
    {
	    $models = SectionsRecord::find()->where([
	    	'not in',
	    	'heading',
	    	$sectionTitles
	    ])->all();
        foreach($models as $model) {
            $model->delete();
        }
	    return true;
	}

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     HelpLinks::$plugin->helpLinksService->saveRename()
     *
     * @param $request
     * @return bool
     * @throws MissingComponentException
     * @throws NotFoundHttpException
     */
    public function saveRename($request): bool
    {
	    $sections = $request->getParam("section");
	    $pluginSections = [];
	    foreach($sections as $old => $new) {
            $pluginSections[] = [$new];
		    if ($old !== $new) {
			    $model = SectionsRecord::findOne(['heading' => $old]);
                if (!$model) {
                    continue;
                }
			    $model->setAttribute("heading", $new);
			    $model->save();
		    }
	    }
	    
        $pluginModel = Craft::$app->getPlugins()->getPlugin("help-links");

        if ($pluginModel === null) {
            throw new NotFoundHttpException('Plugin not found');
        }
        
        $settings = HelpLinks::$plugin->getSettings();
        $settings["sections"] = $pluginSections;

        if (!Craft::$app->getPlugins()->savePluginSettings($pluginModel, (array)$settings)) {
            Craft::$app->getSession()->setError(Craft::t('app', "Couldn't save plugin settings."));
            return false;
        }
        
        return true;
    }
}
