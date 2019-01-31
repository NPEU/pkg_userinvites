<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JFormHelper::loadRuleClass('email');

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleEmails extends JFormRuleEmail
{
    /**
     * Method to test the email address and optionally check for uniqueness.
     *
     * @param   SimpleXMLElement  &$element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value     The form field value to validate.
     * @param   string            $group     The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   JRegistry         &$input    An optional JRegistry object with the entire data set to validate against the entire form.
     * @param   object            &$form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   11.1
     * @throws  JException on invalid rule.
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Joomla\Registry\Registry $input = null, Joomla\CMS\Form\Form $form = null)
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
