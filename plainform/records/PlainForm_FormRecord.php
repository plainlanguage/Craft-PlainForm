<?php
namespace Craft;

class PlainForm_FormRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'plainform_forms';
	}

	public function defineAttributes()
	{
		return array(
			'name'                     => AttributeType::String,
			'handle'                   => AttributeType::Handle,
			'description'              => AttributeType::String,
			'successMessage'           => AttributeType::Mixed,
			'emailSubject'             => AttributeType::String,
			'fromEmail'                => AttributeType::String,
			'fromName'                 => AttributeType::String,
			'replyToEmail'             => AttributeType::String,
			'toEmail'                  => AttributeType::String,
			'notificationTemplatePath' => AttributeType::String,
		);
	}

	public function defineRelations()
	{
		return array(
			'entry' => array(static::HAS_MANY, 'PlainForm_EntryRecord', 'id'),
		);
	}
}
