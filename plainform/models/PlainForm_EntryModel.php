<?php
namespace Craft;

class PlainForm_EntryModel extends BaseElementModel
{
    protected $elementType = 'PlainForm';

    function __toString()
    {
        return $this->id;
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'     => AttributeType::Number,
            'ip'     => AttributeType::String,
            'formId' => AttributeType::Number,
            'title'  => AttributeType::String,
            'data'   => AttributeType::Mixed,
        ));
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        return UrlHelper::getCpUrl('plainform/entries/' . $this->id);
    }

    public function _normalizeDataForElementsTable()
    {
        $data = unserialize($this->data);

        $data = $this->_filterPostKeys($data);

        // Pop off the first (4) items from the data array
        $data = array_slice($data, 0, 5);

        $newData = '<ul>';
        foreach ($data as $key => $value) {
            if (craft()->plainForm->isEmail($value)) {
                $newData .= '<li class="left icon text" style="margin-right:10px;"><strong>' . ucfirst($key) . "</strong>: <a href='mailto:{$value}'>{$value}</a></li>";
            } else {
                $newData .= '<li class="left icon text" style="margin-right:10px;"><strong>' . ucfirst($key) . "</strong>: {$value}</li>";
            }
        }
        $newData .= "</ul>";

        $this->__set('data', $newData);

        return $this;
    }

    private function _filterPostKeys($post)
    {
        $filterKeys = array(
            'action',
            'honeypot',
            'redirect',
            'required',
            'plainformhandle',
            'plainformhoneypot',
            'simpleformhandle',
            'simpleformhoneypot',
            'g-recaptcha-response',
        );

        if (is_array($post)) {
            foreach ($post as $k => $v) {
                if (in_array(strtolower($k), $filterKeys) || empty($v)) {
                    unset($post[$k]);
                }
            }
        }

        return $post;
    }

}
