<?php
/**
 * Help Links plugin for Craft CMS 5.x
 *
 * Define useful links to be added to the dashboard for clients.
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\helplinks\models;

use craft\base\Model;

/**
 * Sections Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    A Digital
 * @package   HelpLinks
 * @since     1.0.0
 */
class Sections extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some model attribute
     *
     * @var array
     */
    public array $links = [['', '', '']];

    /**
     * Some model attribute
     *
     * @var string
     */
    public string $position = '0';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            ['links', 'default', 'value' => [['', '', '']]],
            
            ['position', 'string'],
            ['position', 'default', 'value' => '0'],
        ];
    }
}
