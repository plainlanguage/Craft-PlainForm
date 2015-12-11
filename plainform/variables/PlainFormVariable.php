<?php
namespace Craft;

class PlainFormVariable
{
    /**
     * Check if value is an email address.
     * @param $value
     * @return bool
     */
    public function isEmail($value)
    {
        return craft()->plainForm->isEmail($value);
    }

}
