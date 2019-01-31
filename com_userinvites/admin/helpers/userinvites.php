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
     * @param   string  The name of the active view.
     */
    public static function addSubmenu($vName)
    {
        JSubMenuHelper::addEntry(
            JText::_('COM_USERINVITES_MANAGER_SUBMENU_SEND'),
            'index.php?option=com_userinvites&view=sendinvites',
            $vName == 'sendinvites'
        );
        JSubMenuHelper::addEntry(
            JText::_('COM_USERINVITES_MANAGER_SUBMENU_INVITES'),
            'index.php?option=com_userinvites&view=userinvites',
            $vName == 'userinvites'
        );
    }

    /**
     * Add style.
     */
    public static function addStyle()
    {
        // Set some global property
        $document = JFactory::getDocument();

        $document->addStyleDeclaration('.icon-userinvites:before {content: "\004D";}');
    }


    /**
     * Get the actions
     */
    public static function getActions($userinvitesId = 0)
    {
        jimport('joomla.access.access');
        $user   = JFactory::getUser();
        $result = new JObject;

        if (empty($userinvitesId)) {
            $assetName = 'com_userinvites';
        } else {
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
        echo '<pre>'; var_dump($item); echo '</pre>'; exit;
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

        $app        = JFactory::getApplication();
        $mailfrom   = $app->getCfg('mailfrom');
        $fromname   = $app->getCfg('fromname');
        $sitename   = $app->getCfg('sitename');
        $email      = JStringPunycode::emailToPunycode($email);

        $mail = JFactory::getMailer();
        $mail->addRecipient($email);
        $mail->addReplyTo($mailfrom);
        $mail->setSender(array($mailfrom, $fromname));
        $mail->setSubject($subject);
        $mail->setBody($body);
        $sent = $mail->Send();

        return $sent;
    }

    /**
     * Creates a registration code.
     */
    public static function createCode($email)
    {
        return sha1($email . uniqid(rand(), true));
    }
}