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
 * UserInvites Record View
 */
class UserinvitesViewSendinvites extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    /**
     * Display the UserInvites view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the Data
        $form = new JForm('sendinvites', array('control'=>'jform'));
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        $form->loadFile('sendinvites', false);
        $this->form = $form;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the toolbar
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

        $canDo = UserinvitesHelper::getActions();
        JToolBarHelper::title(JText::_('COM_USERINVITES_MANAGER_SEND'), 'userinvites');
        if ($canDo->get('core.create')) {
            JToolBarHelper::custom('sendinvites.save', 'mail', '', 'COM_USERINVITES_TOOLBAR_SEND', false);
            JToolBarHelper::divider();
            JToolBarHelper::preferences('com_userinvites');
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
        $document->addScript(JURI::root() . "administrator/components/com_userinvites"
                                          . "/views/sendinvites/submitbutton.js");
        JText::script('COM_USERINVITES_RECORD_ERROR_UNACCEPTABLE');
    }
}
