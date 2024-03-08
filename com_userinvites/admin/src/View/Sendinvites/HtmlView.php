<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Userinvites\Administrator\View\Sendinvites;

defined('_JEXEC') or die;


use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use NPEU\Component\Userinvites\Administrator\Helper\UserinvitesHelper;

class HtmlView extends BaseHtmlView {

    protected $form;
    protected $item;
    protected $canDo;

    /**
     * Display the "Hello World" edit view
     */
    function display($tpl = null) {
        $app = Factory::getApplication();

        /*$form = new JForm('sendinvites', array('control'=>'jform'));
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        $form->loadFile('sendinvites', false);

        $this->form  = $this->get('Form');*/
        $form = new Form('sendinvites', ['control'=>'jform']);
        Form::addFormPath(JPATH_COMPONENT . '/forms');
        $form->loadFile('sendinvites', false);
        if (!$form) {
            $app->enqueueMessage($form->getError(), 'error');

            return false;
        }
        $this->form = $form;

        #echo '<pre>'; var_dump($this->form); echo '</pre>'; exit;

        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolBar();

        parent::display($tpl);
    }

    protected function addToolBar() {

        $input = Factory::getApplication()->input;

        #$input->set('hidemainmenu', true);

        $canDo = UserinvitesHelper::getActions();
        ToolBarHelper::title(Text::_('COM_USERINVITES_MANAGER_SEND'), 'userinvites');
        if ($canDo->get('core.create')) {
            ToolBarHelper::custom('sendinvites.save', 'mail', '', 'COM_USERINVITES_TOOLBAR_SEND', false);
            ToolBarHelper::divider();
            ToolBarHelper::preferences('com_userinvites');
        }

        return;







        // Hide Joomla Administrator Main menu
        /*$input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        ToolBarHelper::title($isNew ? Text::_('COM_USERINVITES_MANAGER_RECORD_ADD')
                                    : Text::_('COM_USERINVITES_MANAGER_RECORD_EDIT'), 'smiley');
        // Build the actions for new and existing records.
        if ($isNew) {
            // For new records, check the create permission.
            if ($this->canDo->get('core.create')) {
                ToolbarHelper::apply('userinvite.apply', 'JTOOLBAR_APPLY');
                ToolbarHelper::save('userinvite.save', 'JTOOLBAR_SAVE');
                ToolbarHelper::custom('userinvite.save2new', 'save-new.png', 'save-new_f2.png',
                                       'JTOOLBAR_SAVE_AND_NEW', false);
            }
            ToolbarHelper::cancel('userinvite.cancel', 'JTOOLBAR_CANCEL');
        } else {
            if ($this->canDo->get('core.edit')) {
                // We can save the new record
                ToolbarHelper::apply('userinvite.apply', 'JTOOLBAR_APPLY');
                ToolbarHelper::save('userinvite.save', 'JTOOLBAR_SAVE');

                // We can save this record, but check the create permission to see
                // if we can return to make a new one.
                if ($this->canDo->get('core.create')) {
                    ToolbarHelper::custom('userinvite.save2new', 'save-new.png', 'save-new_f2.png',
                                           'JTOOLBAR_SAVE_AND_NEW', false);
                }
                /*$save_history = Factory::getApplication()->get('save_history', true);
                if ($save_history) {
                    ToolbarHelper::versions('com_userinvite.userinvite', $this->item->id);
                }*   /
            }

            if ($this->canDo->get('core.create')) {
                ToolbarHelper::custom('userinvite.save2copy', 'save-copy.png', 'save-copy_f2.png',
                                       'JTOOLBAR_SAVE_AS_COPY', false);
            }
            ToolbarHelper::cancel('userinvite.cancel', 'JTOOLBAR_CLOSE');
        }*/
    }
}