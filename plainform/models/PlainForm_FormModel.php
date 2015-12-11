<?php
namespace Craft;

class PlainForm_FormModel extends BaseModel
{
	function __toString()
	{
		return Craft::t($this->name);
	}

	protected function defineAttributes()
	{
		return array(
			'id'                       => AttributeType::Number,
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
}
