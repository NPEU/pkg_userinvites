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
 * UserInvites Component Controller
 */
class UserInvitesController extends JControllerLegacy
{
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'sendinvites';

    /**
     * display task
     *
     * @return void
     */
    function display($cachable = false, $urlparams = false)
    {
        // Set default view if not set
        JFactory::getApplication()->input->set('view', JFactory::getApplication()->input->get('view', 'sendinvites'));


        $session = JFactory::getSession();
        $registry = $session->get('registry');


        JLoader::import('groups', JPATH_ADMINISTRATOR . '/components/com_users/models');
        $groups_model = JModelLegacy::getInstance('groups', 'UsersModel' );

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JFactory::getApplication()->input->get('view', $this->default_view);
        $viewLayout = JFactory::getApplication()->input->get('layout', 'default');

        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

        $view->setModel($groups_model);

        // call parent behavior
        parent::display($cachable, $urlparams);

        // Add style
        UserinvitesHelper::addStyle();

        // Set the submenu
        UserinvitesHelper::addSubmenu(JFactory::getApplication()->input->get('view'));
    }
}
