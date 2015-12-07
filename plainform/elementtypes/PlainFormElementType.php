<?php
namespace Craft;

class PlainFormElementType extends BaseElementType
{
	public function getName()
	{
		return Craft::t('PlainForm');
	}

	public function getSources($context = null)
	{
		$sources = array(
			'*' => array(
				'label' => Craft::t('All Submissions.'),
			),
		);

		foreach (craft()->plainForm->getAllForms() as $form)
		{
			$key = 'formId:' . $form->id;

			$sources[$key] = array(
				'label'    => $form->name,
				'criteria' => array('formId' => $form->id)
			);
		}

		return $sources;
	}

	public function defineSearchableAttributes()
	{
		return array('id', 'data');
	}

	public function defineTableAttributes($source = null)
	{
		return array(
			'id'          => Craft::t('ID'),
			// 'formId'   => Craft::t('Form ID'),
			//'title'       => Craft::t('Title'),
			'dateCreated' => Craft::t('Date'),
			'data'        => Craft::t('Submission Data'),
		);
	}

	/**
	 * Returns the table view HTML for a given attribute.
	 *
	 * @param BaseElementModel $element
	 * @param string $attribute
	 * @return string
	 */
	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		switch ($attribute)
		{
			case 'data':
				$data = $element->_normalizeDataForElementsTable();
				return $element->data;
				break;
			default:
				return parent::getTableAttributeHtml($element, $attribute);
				break;
		}
	}

	public function defineCriteriaAttributes()
	{
		return array(
			'formId' => AttributeType::Mixed,
			'order'  => array(AttributeType::String, 'default' => 'plainform_entries.dateCreated desc'),
		);
	}

	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand $query
	 * @param ElementCriteriaModel $criteria
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query
			->addSelect('plainform_entries.formId, plainform_entries.data')
			->join('plainform_entries plainform_entries', 'plainform_entries.id = elements.id')
		;

		if ($criteria->formId) {
			$query->andWhere(DbHelper::parseParam('plainform_entries.formId', $criteria->formId, $query->params));
		}
	}

	/**
	 * Populates an element model based on a query result.
	 *
	 * @param array $row
	 * @return array
	 */
	public function populateElementModel($row, $normalize = false)
	{
		$entry = PlainForm_EntryModel::populateModel($row);

		if ($normalize)
		{
			$entry = $entry->_normalizeDataForElementsTable();
		}

		return $entry;
	}

}
