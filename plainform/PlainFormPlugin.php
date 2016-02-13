<?php
namespace Craft;

class PlainFormPlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('PlainForm');
	}

	function getVersion()
	{
		return '1.2.0';
	}

	function getDeveloper()
	{
		return 'Plain Language';
	}

	function getDeveloperUrl()
	{
		return 'http://plainlanguage.co';
	}

	function getSourceLanguage()
	{
		return 'en_us';
	}

	protected function defineSettings()
	{
		return array();
	}

	public function getSettingsHtml()
	{
		return '';
	}

	public function prepSettings($settings)
	{
		// Modify settings from POST here

		return $settings;
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'plainform'                          => array('action' => 'plainForm/index'),
			'plainform/forms/new'                => array('action' => 'plainForm/newForm'),
			'plainform/forms/(?P<formId>\d+)'    => array('action' => 'plainForm/editForm'),
			'plainform/entries'                  => array('action' => 'plainForm/entriesIndex'),
			'plainform/entries/(?P<entryId>\d+)' => array('action' => 'plainForm/viewEntry'),
		);
	}
}
