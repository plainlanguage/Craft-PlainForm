<?php
namespace Craft;

class PlainForm_EntryRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'plainform_entries';
	}

	public function defineAttributes()
	{
		return array(
			'formId' => AttributeType::Number,
			'title'  => AttributeType::String,
			'data'   => AttributeType::Mixed,
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
			'form'    => array(static::BELONGS_TO, 'PlainForm_FormRecord', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}
}
