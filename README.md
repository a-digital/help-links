# Help Links plugin for Craft CMS 3.x

Define useful links to be added to the dashboard for clients.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require adigital/help-links

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Help Links.

## Help Links Overview

This widget can be used to display useful links on the dashboard for clients. You can create as many headings as you need in the plugin settings to separate out the content clearly. Each link can have a title, url, and additional comment. This plugin was inspired by the talk [Katie Fritz](https://twitter.com/KatieMaeFritz) gave at dot all 2018 in Berlin about [Prioritising Author Experience](http://dotall.com/sessions/prioritizing-author-experience).

## Configuring Help Links

Create your section headings in the plugin settings page. Then click Help Links in the sidebar and configure the links for each of these headings you have created.

## Pre Populating Help Links

If like us you are going to be using this for multiple clients but most of the links will remain the same, then you can pre-populate your settings and links for this plugin. There are 2 ways of doing this.

### Import / Export using the plugin

We've created an Import / Export page within the plugin which you can use. Once you have manually populated the links on one site, you can export these to a JSON formatted file. This file can then be imported into a clean install and it will generate all of your links for you. From here you can then edit them as needed to make them site specific. This is our recommended method for pre populating your links.

### Use a migration

You can also create a content migration and run it within the CMS once the plugin is installed. You can then edit the settings in the usual way as needed.

Further reading on content migrations in Craft 3 can be found here: [Using Content Migrations in Craft 3](https://adigital.agency/blog/using-content-migrations-in-craft-3).

A code example can be found below for setting up a migration to run with this plugin, just make sure you add `use adigital\helplinks\HelpLinks;` to the top of the file.

```
$plugin = Craft::$app->plugins->getPlugin("help-links");
$settings = [
	"widgetTitle" => "Help Links",
	"sections" => [
		["Documentation"],
		["Support"]
	]
];
Craft::$app->plugins->savePluginSettings($plugin, $settings);

$request = [];
$request["heading"] = "Documentation";
$request["position"] = "1";
$request["links"][] = [
	"Test",
	"http://www.test.com",
	"This is a test"
];
$request["links"][] = [
	"Another",
	"http://www.google.co.uk",
	"(Google)"
];
HelpLinks::$plugin->helpLinksService->generateSection($request);

$request = [];
$request["heading"] = "Support";
$request["position"] = "2";
$request["links"][] = [
	"Zendesk",
	"https://adigital.zendesk.com/agent/filters",
	"(tickets)"
];
HelpLinks::$plugin->helpLinksService->generateSection($request);
```

## Using Help Links

Once configured, add the widget to your dashboard.

## Screenshots

![Dashboard](resources/img/dashboard.png)
![Settings](resources/img/settings.png)
![Section Links](resources/img/section-links.png)
![Rename Headings](resources/img/rename.png)
![Import / Export](resources/img/import-export.png)
![Permissions](resources/img/permissions.png)

Brought to you by [A Digital](https://adigital.agency)
