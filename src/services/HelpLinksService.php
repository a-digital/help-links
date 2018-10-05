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
    public function createSection($section, $settings)
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
}
