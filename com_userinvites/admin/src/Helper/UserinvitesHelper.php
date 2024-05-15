<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_designrequests
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Userinvites\Administrator\Helper;
/*
use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
*/
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Access\Access;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * DesignrequestssHelper Component Model
 */
#class DesignrequestsHelper extends ContentHelper
class UserinvitesHelper
{

    /**
     * Get the actions
     */
    public static function getActions($userinvitesId = 0)
    {
        jimport('joomla.access.access');
        $user   = Factory::getUser();
        $result = new CMSObject;

        if (empty($userinvitesId)) {
            $assetName = 'com_userinvites';
        } else {
            $assetName = 'com_userinvites.userinvites.' . (int) $userinvitesId;
        }

        $actions = Access::getActionsFromFile(dirname(dirname(__DIR__)) . '/access.xml', '/access/section[@name="component"]/');

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
        $app    = Factory::getApplication();
        $input  = $app->input;

        $uri        = Uri::getInstance();
        $admin_path = str_replace('index.php', '', $uri->getPath());

        $base       = $uri->toString(['scheme', 'user', 'pass', 'host', 'port']);

        $params = ComponentHelper::getParams($input->get('option'));
        $email      = $item->email;


        $site = Factory::getContainer()->get(\Joomla\CMS\Application\SiteApplication::class);
        $menu = $site->getMenu();
        $menu_items = $menu->getItems('link', 'index.php?option=com_users&view=registration');

        if (!empty($menu_items)) {
            $menu_item  = $menu_items[0];
            $id         = 'Itemid=' . $menu_item->id . '&';
        } else {
            $id = '';
        }

        $router     = $app->getRouter();

        $uri        = $router->build('index.php?option=com_users&view=registration&code=' . $item->code);

        $parsed_url = $uri->toString();
        $register_link = $base . str_replace($admin_path, '/', $parsed_url);

        $lifespan = $params->get('lifespan');
        $subject  = $params->get('subject');
        $body     = sprintf(
            $params->get('template'),
            $register_link,
            $lifespan
        );

        $app        = Factory::getApplication();
        $mailfrom   = $app->getCfg('mailfrom');
        $fromname   = $app->getCfg('fromname');
        $sitename   = $app->getCfg('sitename');
        $email      = PunycodeHelper::emailToPunycode($email);

        $mail = Factory::getMailer();
        $mail->addRecipient($email);
        $mail->addReplyTo($mailfrom);
        $mail->setSender([$mailfrom, $fromname]);
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