<?php

namespace craft\helplinks\migrations;

use adigital\helplinks\records\Preferences;
use craft\db\Migration;
use Craft;

class m241120_144302_addPreferencesTable extends Migration
{
    public function safeUp(): bool
    {
        if ($this->createTables()) {
            Craft::$app->db->schema->refresh();
        }

        $this->moveSettings();

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists(Preferences::tableName());
        return true;
    }

    protected function createTables(): bool
    {
        $tablesCreated = false;

        // helplinks_preferences table
        $tableSchema = Craft::$app->db->schema->getTableSchema(Preferences::tableName());
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                Preferences::tableName(),
                [
                    'id' => $this->primaryKey(),
                    // Custom columns in the table
                    'widgetTitle' => $this->string(255)->notNull()->defaultValue(''),
                    'sections' => $this->text(),
                    // Default columns
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function moveSettings(): bool
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('helplinks');
        $settings = $plugin->getSettings();

        $model = new Preferences();
        $model->setAttribute('widgetTitle', $settings->widgetTitle);
        $model->setAttribute('sections', $settings->sections);
        $model->save();

        Craft::$app->plugins->savePluginSettings($plugin, []);

        return true;
    }
}