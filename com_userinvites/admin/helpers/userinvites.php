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
 * Userinvites component helper.
 */
abstract class UserinvitesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_USERINVITES_SUBMENU_SEND'),
			'index.php?option=com_userinvites',
			$vName == 'menus'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_USERINVITES_SUBMENU_MANAGE'),
			'index.php?option=com_userinvites&view=userinvites',
			$vName == 'items'
		);
	}

	/**
	 * Add style.
	 */
	public static function addStyle()
	{
		// Set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-userinvites ' .
		                               '{background-image: url(../media/com_userinvites/images/userinvites-48x48.png);}');
		$document->addStyleDeclaration('.icon-32-messaging ' .
		                               '{background-image: url(/administrator/templates/bluestork/images/toolbar/icon-32-messaging.png);}');
	}


	/**
	 * Get the actions
	 */
	public static function getActions($userinvitesId = 0)
	{
		jimport('joomla.access.access');
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($userinvitesId)) {
			$assetName = 'com_userinvites';
		}
		else {
			$assetName = 'com_userinvites.userinvites.'.(int) $userinvitesId;
		}

		$actions = JAccess::getActions('com_userinvites', 'component');

		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Send invitation emails.
	 */
	public static function sendInvite($item)
	{
		$uri        = JUri::getInstance();
		$admin_path = str_replace('index.php', '', $uri->getPath());

		$base       = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		$option     = JRequest::getCmd('option');
		$params     = JComponentHelper::getParams($option);
		$email      = $item->email;

		$app_admin  = JFactory::getApplication();
		$app_site   = JApplication::getInstance('site');

		$menu       = $app_site->getMenu();
		$menu_items = $menu->getItems('link', 'index.php?option=com_users&view=registration');
		if (!empty($menu_items)) {
			$menu_item  = $menu_items[0];
			$id         = 'Itemid=' . $menu_item->id . '&';
		} else {
		    $id = '';
		}

		// Core routers call JFactory::getApplication() which returns admin app,
		// so routes aren't processed, so temporarily replace $application
		// with site app:
		JFactory::$application = $app_site;

		$router     = $app_site->getRouter();

		$uri        = $router->build('index.php?' . $id . 'option=com_users&view=registration&code=' . $item->code);

		// Restore admin application:
		JFactory::$application = $app_admin;

		$parsed_url = $uri->toString();

		$register_link = $base . str_replace($admin_path, '/', $parsed_url);

		$lifespan = $params->get('lifespan');
		$subject  = $params->get('subject');
		$body     = sprintf(
			$params->get('template'),
			$register_link,
			$lifespan
			);

		// New mailer:
		$mailer =& JFactory::getMailer();
		// Set sender from config:
		$config =& JFactory::getConfig();
		$sender = array(
    		$config->get('config.mailfrom'),
    		$config->get('config.fromname')
    		);
		$mailer->setSender($sender);
		// Add recipient:
		$recipient = $email;
		$mailer->addRecipient($recipient);
		// Set subject and body:
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		// And send it:
		return $mailer->Send();
	}

	/**
	 * Creates a registration code.
	 */
	public static function createCode($email)
	{
		return sha1($email . uniqid(rand(), true));
	}
}