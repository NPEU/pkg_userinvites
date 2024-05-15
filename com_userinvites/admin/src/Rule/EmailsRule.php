<?php
namespace NPEU\Component\Userinvites\Administrator\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Form\Rule\EmailRule;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;


class EmailsRule extends EmailRule
{
    /**
     * Method to test the email address and optionally check for uniqueness.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   Form               $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid and points to an existing folder below the Joomla root, false otherwise.
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $emails = explode("\n", $value);
        foreach ($emails as $email) {
            $email = trim($email);
            if (!parent::test($element, $email, $group, $input, $form)) {
                return false;
            }
        }
        return true;
    }
}