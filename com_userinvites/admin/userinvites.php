<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

// Force-load the Admin language file to avoid repeating form language strings:
// (this model is used in the front-end too, and the Admin lang isn't auto-loaded there.)
$lang = JFactory::getLanguage();
$extension = 'com_userinvites';
$base_dir = JPATH_COMPONENT_ADMINISTRATOR;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_userinvites')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require helper file
JLoader::register('UserinvitesHelper', JPATH_COMPONENT . '/helpers/userinvites.php');

// Get an instance of the controller prefixed by UserInvites
$controller = JControllerLegacy::getInstance('UserInvites');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
