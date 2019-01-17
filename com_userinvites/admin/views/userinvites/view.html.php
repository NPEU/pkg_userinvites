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
 * UserInvites Records View
 */
class UserInvitesViewUserinvites extends JViewLegacy
{
    /**
     * Display the UserInvites view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null)
    {

        // Get application
        $app = JFactory::getApplication();
        $context = "userinvites.list.admin.record";
        // Get data from the model
        $this->items            = $this->get('Items');
        $this->pagination       = $this->get('Pagination');
        $this->state            = $this->get('State');
        $this->filter_order     = $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'sent_date', 'cmd');
        $this->filter_order_dir = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
        $this->filterForm       = $this->get('FilterForm');
        $this->activeFilters    = $this->get('ActiveFilters');

        $usergroupObjs = $this->get('Items', 'Groups');
        $usergroups = array();
        foreach ($usergroupObjs as $usergroup) {
            $usergroups[$usergroup->id] = $usergroup->title;
        }

        $this->usergroups = $usergroups;

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the toolbar and number of found items
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolBar()
    {
        $input = JFactory::getApplication()->input;

        // Hide Joomla Administrator Main menu
        #$input->set('hidemainmenu', true);

        $title = JText::_('COM_USERINVITES_MANAGER_INVITES');

        if ($this->pagination->total)
        {
            $title .= "<span style='font-size: 0.5em; vertical-align: middle;'> (" . $this->pagination->total . ")</span>";
        }

        $canDo = UserinvitesHelper::getActions();

        JToolBarHelper::title($title, 'userinvites');

        if ($canDo->get('core.admin')) {
            if (!empty($this->items)) {
                JToolBarHelper::custom('userinvites.resend', 'refresh.png', 'refresh_f2.png', 'COM_USERINVITES_TOOLBAR_RESEND', false);
                JToolBarHelper::deleteList('', 'userinvites.delete', 'JTOOLBAR_DELETE');
                JToolBarHelper::divider();
                JToolBarHelper::preferences('com_userinvites');
            }
        }
    }
    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_USERINVITES_ADMINISTRATION'));
    }
}