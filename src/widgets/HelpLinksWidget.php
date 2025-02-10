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

use adigital\helplinks\records\Preferences;
use Craft;
use craft\base\Widget;
use JsonException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use function Arrayy\array_first;

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
 *
 * @property-read string|false $bodyHtml
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
        $model = Preferences::find()->one();
        if (!$model) {
            return '';
        }
	    $settings = $model;
	    $widgetName = "Help Links";
	    if ($settings->getAttribute("widgetTitle")) {
		    $widgetName = $settings->getAttribute("widgetTitle");
		}
        return Craft::t('help-links', $widgetName);
    }
    
    /**
     * Returns the path to the widget’s SVG icon.
     *
     * @return string|null The path to the widget’s SVG icon
     */
    public static function iconPath(): ?string
    {
        return Craft::getAlias("@adigital/helplinks/assetbundles/helplinkswidgetwidget/dist/img/HelpLinksWidget-icon.svg");
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the widget's body HTML.
     *
     * @return null|string
     * @throws JsonException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getBodyHtml(): ?string
    {
        $model = Preferences::find()->one();
        if (!$model) {
            return null;
        }
        $settings = $model;
        $sections = [];
        $sectionsData = json_decode($settings->getAttribute("sections"), true);
        if ($sectionsData) {
	        foreach($sectionsData as $section) {
		        $sectionSettings = HelpLinks::$plugin->helpLinksService->returnSection(array_first($section));
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
