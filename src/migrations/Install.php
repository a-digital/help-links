<?php
/**
 * Help Links plugin for Craft CMS 3.x
 *
 * Define useful links to be added to the dashboard for clients.
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\helplinks\migrations;

use adigital\helplinks\records\Preferences;
use Craft;
use craft\db\Migration;

/**
 * Help Links Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    A Digital
 * @package   HelpLinks
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public string $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables(): bool
    {
        $tablesCreated = false;

    // helplinks_sections table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%helplinks_sections}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%helplinks_sections}}',
                [
                    'id' => $this->primaryKey(),
                // Custom columns in the table
                    'heading' => $this->string(255)->notNull()->defaultValue(''),
                    'links' => $this->text(),
                    'position' => $this->string(255)->notNull()->defaultValue(''),
                // Default columns
                	'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

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

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes(): void
    {
    // helplinks_sections table
        $this->createIndex(
            $this->db->getIndexName(),
            '{{%helplinks_sections}}',
            'heading',
            true
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData(): void
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables(): void
    {
    // helplinks_sections table
        $this->dropTableIfExists('{{%helplinks_sections}}');
        $this->dropTableIfExists(Preferences::tableName());
    }
}
