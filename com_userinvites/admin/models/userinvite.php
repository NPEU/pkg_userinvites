<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * UserInvites Record Model
 */
class UserinvitesModelUserinvite extends JModelAdmin
{
    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data   An array of input data.
     * @param   string  $key    The name of the key for the primary key.
     *
     * @return  boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Check specific edit permission then general edit permission.
        return JFactory::getUser()->authorise('core.edit', 'com_userinvite.userinvite.' .
            ((int) isset($data[$key]) ? $data[$key] : 0))
            or parent::allowEdit($data, $key);
    }


    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($type = 'userinvites', $prefix = 'UserInvitesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        return false;
    }

    /**
     * Method to get the script that have to be included on the form
     *
     * @return string   Script files
     */
    public function getScript()
    {
        return '';
    }

    /**
     * Method to prepare the saved data.
     *
     * @param   array  $data  The form data.
     *
     * @return  array  Array of saved ID's
     */
    public function save($data)
    {
        $is_new = true;

        // Get parameters:
        $params = JComponentHelper::getParams(JRequest::getVar('option'));


        $user        = JFactory::getUser();
        $user_id     = $user->get('id');
        $date_format = 'Y-m-d H:i:s A';
        $lifespan    = $params->get('lifespan');

        $data['sent_by'] = $user_id;
        $data['sent_on'] = date($date_format);
        $data['expires'] = date($date_format, strtotime('+' . $lifespan));


        if (isset($data['groups'])) {
            $groups = new JRegistry;
            $groups->loadArray($data['groups']);
            $data['groups'] = (string) $groups;
        } else {
            $is_new = false;
        }

        $t_data = $data;
        unset($t_data['emails']);
        $state = $this->state;
        $new_ids = array();
        foreach ($data['emails'] as $id => $email) {
            $t_data['email'] = $email;
            if (!$is_new) {
                $t_data['id'] = $id;
            }
            $t_data['code']  = UserinvitesHelper::createCode($email);
            if (!parent::save($t_data)) {
                return false;
            }
            $new_ids[] = $this->state->get('userinvite.id');

            // Reset the state. Probably a better way of doing this.
            $this->state       = $state;
            $this->__state_set = null;
        }

        return $new_ids;;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_userinvites.edit.userinvites.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
}
