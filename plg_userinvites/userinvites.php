<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.UserInvites
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * Adds checks for invitation process; overrides default registration behaviour.
 */
class plgSystemUserInvites extends JPlugin
{
    protected $autoloadLanguage = true;
    
    
    /**
     * AfterInitialise event.
     * Add the codeRoute callack if not in admin.
     *
     * @return  void
     */
    public function onAfterInitialise()
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return; // Don't run in admin
        }
        $router   = $app->getRouter();
        $callback = array($this, 'codeRoute');
        $router->attachBuildRule($callback);
    }
    
    /**
	 * AfterRoute event.
	 * Checks for a valid code during registration.
	 *
	 * @return   void
	 */
	public function onAfterRoute()
	{
		$input = JFactory::getApplication()->input;
	    // Only run during registration process...
		if ($input->get('view') != 'registration') {
            return;
		}
        // ... but not at completion or activation stage
		if ($input->get('layout') == 'complete' || $input->get('task') == 'registration.activate') {
            return;
		}

	    // We must have a code on all parts of the registration process except the last
		$code = JFactory::getApplication()->input->get('code');
		if (empty($code)) {
		    JLog::add('No registration code', JLog::NOTICE, 'plgSystemUserinvites');
		    JError::raiseError(404);
		    return;
		}

        // Check code is valid:
		$db	   = JFactory::getDBO();
		$query = $db->getQuery(true);
        $query->select('*')
          ->from($db->quoteName('#__userinvites'))
          ->where('code = "' . $code . '"');
        $db->setQuery($query);
        $result = $db->loadAssoc();
        if (is_null($result)) {
        	JLog::add('Invlaid registration code', JLog::NOTICE, 'plgSystemUserinvites');
        	JError::raiseError(404);
        	return;
        }

		// Check if the code has expired:
		if (time() > strtotime($result['expires'])) {
			// User did not enter the email they were invited with:
			JError::raiseWarning(100, JText::_('PLG_SYSTEM_USERINVITES_ERROR_EXPIRED_CODE'));
			return;
		}

		// Check if we're at the registration.register stage:
		if (JFactory::getApplication()->input->get('task') != 'registration.register') {
			return;
		}


		// We have a valid code and invite data, now check for submission
		// and that the submitted email matches stored email:
       	$form_data = JFactory::getApplication()->input->get('jform', array(), 'array');
       	if (!empty($form_data) && $form_data['email1'] != $result['email']) {
       		// User did not enter the email they were invited with:
       		JError::raiseWarning(100, JText::_('PLG_SYSTEM_USERINVITES_ERROR_UNREGISTERED_EMAIL'));
       	}

		return;
	}

	/**
	 * Add a User to stored groups if the user is completing registration.
     * Delete the invitation as it's now fulfilled.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was successfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$input = JFactory::getApplication()->input;
	    // Only run during registration process...
		if ($input->get('view') != 'registration') {
            return;
		}
        // ... but not at completion or activation stage
		if ($input->get('layout') == 'complete' || $input->get('task') == 'registration.activate') {
            return;
		}
	
		if (!$error) {
			$db	  = JFactory::getDBO();
			// Add the groups for the user:
			$query = $db->getQuery(true);
			$query->select('groups')
			  ->from($db->quoteName('#__userinvites'))
		      ->where('email = "' . $data['email'] . '"');
			$db->setQuery($query);
			$groups = json_decode($db->loadResult(), true);
			foreach ($groups as $group) {
				$query = $db->getQuery(true);
				$query->insert('#__user_usergroup_map');
				$query->columns('user_id, group_id');
				$query->values($data['id'] . ',' . (int) $group);
				$db->setQuery($query);
				$db->execute();
			}
			// Delete the invitation:
			$query = $db->getQuery(true);
			$query->delete('#__userinvites');
			$query->where('email = "' . $data['email'] . '"');
			$db->setQuery($query);
			$db->execute();
		}
		return;
	}

    /**
	 * @param   JRouterSite  $router  The Joomla site Router
	 * @param   JURI         $uri     The URI to parse
	 *
	 * @return  array  The array of processed URI variables
	 */
    public function codeRoute($router, $uri)
    {
        if ($uri->getVar('layout', false) == 'complete') {
            $uri->delVar('code');
            return;
        }
        $code = JFactory::getApplication()->input->get('code');
        JLog::add((string) $uri, JLog::NOTICE, 'plgSystemUserinvites');
        JLog::add($code, JLog::NOTICE, 'plgSystemUserinvites');

        if ($code
            && $uri->getVar('option') == 'com_users'
            && ($uri->getVar('view') == 'registration')
                || $uri->getVar('task') == 'registration.register') {
            $uri->setVar('code', $code);
        }
    }
}