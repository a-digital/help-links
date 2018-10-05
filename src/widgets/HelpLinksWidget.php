<?php
/**
 * Help Links plugin for Craft CMS 3.x
 *
 * Define useful links to be added to the dashboard for clients.
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\helplinks\widgets;

use adigital\helplinks\HelpLinks;
use adigital\helplinks\assetbundles\helplinkswidgetwidget\HelpLinksWidgetWidgetAsset;

use Craft;
use craft\base\Widget;

/**
 * Help Links Widget
 *
 * Dashboard widgets allow you to display information in the Admin CP Dashboard.
 * Adding new types of widgets to the dashboard couldn’t be easier in Craft
 *
 * https://craftcms.com/docs/plugins/widgets
 *
 * @author    A Digital
 * @package   HelpLinks
 * @since     1.0.0
 */
class HelpLinksWidget extends Widget
{

    // Public Properties
    // =========================================================================

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
	    $settings = HelpLinks::$plugin->getSettings();
	    $widgetName = "Help Links";
	    if ($settings["widgetTitle"]) {
		    $widgetName = $settings["widgetTitle"];
		}
        return Craft::t('help-links', $widgetName);
    }
    
    /**
     * Returns the path to the widget’s SVG icon.
     *
     * @return string|null The path to the widget’s SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@adigital/helplinks/assetbundles/helplinkswidgetwidget/dist/img/HelpLinksWidget-icon.svg");
    }

    /**
     * Returns the widget’s maximum colspan.
     *
     * @return int|null The widget’s maximum colspan, if it has one
     */
    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the widget's body HTML.
     *
     * @return string|false The widget’s body HTML, or `false` if the widget
     *                      should not be visible. (If you don’t want the widget
     *                      to be selectable in the first place, use {@link isSelectable()}.)
     */
    public function getBodyHtml()
    {
        $settings = HelpLinks::$plugin->getSettings();
        $sections = [];
        if ($settings["sections"]) {
	        foreach($settings["sections"] as $section) {
		        $sectionSettings = HelpLinks::$plugin->helpLinksService->returnSection($section[0]);
		        $sections[] = $sectionSettings;
	        }
        }
        // settings
        // sections

        return Craft::$app->getView()->renderTemplate(
            'help-links/_components/widgets/HelpLinksWidget_body',
            [
                'message' => "",
                "sections" => $sections
            ]
        );
    }
}
