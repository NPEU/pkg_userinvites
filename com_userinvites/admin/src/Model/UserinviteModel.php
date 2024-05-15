<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Userinvites\Administrator\Model;

defined('_JEXEC') or die;


#use Joomla\CMS\Form\Form;
#use Joomla\CMS\Helper\TagsHelper;
#use Joomla\CMS\Language\Associations;
#use Joomla\CMS\Language\LanguageHelper;
#use Joomla\CMS\UCM\UCMType;
#use Joomla\CMS\Versioning\VersionableModelTrait;
#use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
#use Joomla\Registry\Registry;
#use Joomla\String\StringHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;

use NPEU\Component\Userinvites\Administrator\Helper\UserinvitesHelper;

/**
 * Userinvite Model
 */
class UserinviteModel extends AdminModel
{
    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        return false;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState(
            'com_userinvites.edit.userinvite.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to prepare the saved data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  mixed  array on success, False on error..
     */
    public function save($data)
    {
        $is_new = true;
        $app    = Factory::getApplication();
        $input  = $app->input;


        // Get parameters:
        #$params = \Joomla\CMS\Component\ComponentHelper::getParams(JRequest::getVar('option'));
        $params = ComponentHelper::getParams($input->get('option'));


        $user        = Factory::getUser();
        $user_id     = $user->get('id');
        $date_format = 'Y-m-d H:i:s';
        $lifespan    = $params->get('lifespan');

        $data['sent_by'] = $user_id;
        $data['sent_on'] = date($date_format);
        $data['expires'] = date($date_format, strtotime('+' . $lifespan));


        if (isset($data['groups'])) {

            $groups = new Registry;
            $groups->loadArray($data['groups']);
            $data['groups'] = (string) $groups;
        } else {
            $is_new = false;
        }

        $t_data = $data;
        unset($t_data['emails']);
        $state = $this->state;
        $new_ids = [];
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

        return $new_ids;
    }

}
