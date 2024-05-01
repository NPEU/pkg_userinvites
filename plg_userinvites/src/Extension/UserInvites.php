<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.UserInvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Plugin\System\UserInvites\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Adds checks for invitation process; overrides default registration behaviour.
 */
class UserInvites extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    /**
     * An internal flag whether plugin should listen any event.
     *
     * @var bool
     *
     * @since   4.3.0
     */
    protected static $enabled = false;

    /**
     * Constructor
     *
     */
    public function __construct($subject, array $config = [], bool $enabled = true)
    {
        // The above enabled parameter was taken from the Guided Tour plugin but it always seems
        // to be false so I'm not sure where this param is passed from. Overriding it for now.
        $enabled = true;


        #$this->loadLanguage();
        $this->autoloadLanguage = $enabled;
        self::$enabled          = $enabled;

        parent::__construct($subject, $config);
    }

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return self::$enabled ? [
            'onAfterInitialise' => 'onAfterInitialise',
            'onAfterRoute' => 'onAfterRoute',
            'onUserAfterSave' => 'onUserAfterSave'
        ] : [];
    }

    /**
     * @param   Event  $event
     *
     * @return  void
     */
    public function onAfterInitialise(Event $event): void
    {
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            return; // Don't run in admin
        }

        $router = $app->getRouter();

        // Attach the callback to the router
        //$router->attachBuildRule($buildRulesCallback);
        #$router->attachParseRule([$this, 'parseRules'], $router::PROCESS_BEFORE);
        $router->attachBuildRule([$this, 'codeRoute'], $router::PROCESS_BEFORE);
        #$router->attachBuildRule([$this, 'buildRules2'], $router::PROCESS_DURING);
    }


    /**
     * AfterRoute event.
     * Checks for a valid code during registration.
     *
     * @return   void
     */
    public function onAfterRoute(Event $event): void
    {
        $app = Factory::getApplication();
        $input = $app->input;
        // Only run during registration process...
        if ($input->get('view') != 'registration') {
            return;
        }
        // ... but not at completion or activation stage
        if ($input->get('layout') == 'complete' || $input->get('task') == 'registration.activate') {
            return;
        }

        // We must have a code on all parts of the registration process except the last
        $code = $app->input->get('code');
        if (empty($code)) {
            ###JLog::add('No registration code', JLog::NOTICE, 'plgSystemUserinvites');
            throw new Exception(JText::_('PLG_SYSTEM_USERINVITES_ERROR_NO_CODE'), 404);
            return;
        }

        // Check code is valid:
        $db    = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
          ->from($db->quoteName('#__userinvites'))
          ->where('code = "' . $code . '"');
        $db->setQuery($query);
        $result = $db->loadAssoc();
        if (is_null($result)) {
            ###JLog::add('Invlaid registration code', JLog::NOTICE, 'plgSystemUserinvites');
            throw new Exception(JText::_('PLG_SYSTEM_USERINVITES_ERROR_INVALID_CODE'), 404);
            return;
        }


        // Check if we're at the registration.register stage:
        if ($app->input->get('task') != 'registration.register') {
            return;
        }

        // Form errrors:
        $form_errors = false;

        // Check if the code has expired:
        if (time() > strtotime($result['expires'])) {
            // User did not enter the email they were invited with:
            #throw new Exception(JText::_('PLG_SYSTEM_USERINVITES_ERROR_EXPIRED_CODE'), 100);
            $app->enqueueMessage(Text::_('PLG_SYSTEM_USERINVITES_ERROR_EXPIRED_CODE'), 'error');
            $form_errors = true;
        }


        // We have a valid code and invite data, now check for submission
        // and that the submitted email matches stored email:
        $form_data = $app->input->get('jform', array(), 'array');
        if (!empty($form_data) && $form_data['email1'] != $result['email']) {
            // User did not enter the email they were invited with:
            $app->enqueueMessage(Text::_('PLG_SYSTEM_USERINVITES_ERROR_UNREGISTERED_EMAIL'), 'error');
            $form_errors = true;
        }

        if ($form_errors) {
            $app->redirect(Route::_('index.php?option=com_users&view=registration&code=' . $code, false));
        }

        return;
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * @param   Event  $event
     *
     * @return  boolean
     */
    public function onUserAfterSave(Event $event): void
    {
        [$user, $isnew, $success, $msg] = array_values($event->getArguments());
        $user_id = ArrayHelper::getValue($user, 'id', 0, 'int');

        $input = Factory::getApplication()->input;
        // Only run during registration process...
        if ($input->get('view') != 'registration') {
            return;
        }
        // ... but not at completion or activation stage
        if ($input->get('layout') == 'complete' || $input->get('task') == 'registration.activate') {
            return;
        }

        if ($user_id && $success) {
            $db   = Factory::getDBO();
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

        if (!empty($uri->getVar('code'))) {
            Log::add('Already has code', Log::NOTICE, 'plgSystemUserinvites');
            return;
        }

        $code = Factory::getApplication()->input->get('code');
        ###JLog::add((string) $uri, JLog::NOTICE, 'plgSystemUserinvites');
        #Log::add($code, Log::NOTICE, 'plgSystemUserinvites');

        if ($code
            && $uri->getVar('option') == 'com_users'
            && ($uri->getVar('view') == 'registration')
                || $uri->getVar('task') == 'registration.register') {
            $uri->setVar('code', $code);
        }
    }
}