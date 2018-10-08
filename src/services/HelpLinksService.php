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
use craft\mail\Message;
use craft\web\View;

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
     * @return mixed
     */
    public function returnSection($section)
    {
        $model = SectionsRecord::findOne(["heading" => $section]);
        if ($model === null) {
        	return false;
        }
        $attributes = $model->getAttributes();
        $attributes["links"] = json_decode($attributes["links"]);
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
     * @return mixed
     */
    public function createSection($section)
    {
        $modelSection = [
	        "links" => "[['', '', '']]"
        ];
        
        $model = SectionsRecord::findOne(['heading' => $section]);
        if ($model !== null) {
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
     * @return mixed
     */
    public function saveSection($request)
    {
        $modelSection = [
	        "links" => $request->getParam("links")
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
     * @return mixed
     */
    public function importSection($title, $links = [], $count)
    {
        $modelSection = [
	        "links" => $links
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
     * @return mixed
     */
    public function generateSection($request)
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
     * @return mixed
     */
    public function importSettings($attachments)
    {
	    $file = $attachments["jsonSettings"]["tmp_name"];
	    
	    $filedata = file_get_contents($file);
	    $jsonSettings = json_decode($filedata);
	    
	    $pluginSettings = (array)$jsonSettings->plugin;
	    $sectionSettings = $jsonSettings->sections;
	    
	    $plugin = Craft::$app->getPlugins()->getPlugin("help-links");
	    Craft::$app->getPlugins()->savePluginSettings($plugin, $pluginSettings);
	    $sections = [];
	    $count = 1;
	    foreach($sectionSettings as $key => $section) {
	        HelpLinks::$plugin->helpLinksService->importSection($key, $section, $count);
	        $sections[] = $key;
	        $count++;
        }
        
        HelpLinks::$plugin->helpLinksService->removeSections($sections);
	    
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
     * @return mixed
     */
    public function removeSections($sectionTitles)
    {
	    $models = SectionsRecord::find()->where([
	    	'not in',
	    	'heading',
	    	$sectionTitles
	    ])->all();
	    if (isset($models) && (is_array($models) || is_object($models)) && ($models instanceof \Countable || count($models) > 0)) {
		    foreach($models as $model) {
			    $model->delete();
		    }
	    }
	    return true;
	}
}
