<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Userinvites\Administrator\View\Userinvites;

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView {
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
    * Is this view an Empty State
    *
    * @var  boolean
    * @since 4.0.0
    */
    private $isEmptyState = false;

    /**
     * Display the main "Userinvites" view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void
     */
    function display($tpl = null) {
        // Get application
        $app = Factory::getApplication();

        // Get data from the model
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // What Access Permissions does this user have? What can (s)he do?
        $this->canDo = ContentHelper::getActions('com_userinvites');

        // Add user groups
        $groups_model  = $app->bootComponent('com_users')->getMVCFactory()->createModel('Groups', 'Administrator');
        $usergroupObjs = $groups_model->getItems();
        $usergroups = [];
        foreach ($usergroupObjs as $usergroup) {
            $usergroups[$usergroup->id] = $usergroup->title;
        }

        $this->usergroups = $usergroups;

        $errors = $this->get('Errors');
        if (is_array($errors) && count($errors)) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }
        #echo '<pre>'; var_dump($this->getLayout()); echo '</pre>'; exit;
        if ($this->getLayout() !== 'modal') {
            $this->addToolBar();
        } /*else
        {
            // If it's being displayed to select a record as an association, then forcedLanguage is set
            if ($forcedLanguage = $app->input->get('forcedLanguage', '', 'CMD')) {
                // Transform the language selector filter into an hidden field, so it can't be set
                $languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);
            }
        }*/

        // Prepare a mapping from parent id to the ids of its children
        /*$this->ordering = [];
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                $this->ordering[$item->parent_id][] = $item->id;
            }
        }*/

        // Display the layout
        parent::display($tpl);
    }

    protected function addToolBar()
    {
        $title = Text::_('COM_USERINVITES_MANAGER_INVITES');

        $bar = Toolbar::getInstance('toolbar');

        /*if ($this->pagination->total)
        {
            $title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
        }*/
        ToolBarHelper::title($title);

        if ($this->canDo->get('core.create')) {
            ToolBarHelper::addNew('userinvite.add', 'JTOOLBAR_NEW');
        }
        if ($this->canDo->get('core.edit')) {
            ToolBarHelper::editList('userinvite.edit', 'JTOOLBAR_EDIT');
        }


        if (!$this->isEmptyState && ($this->canDo->get('core.edit.state'))) {
            if ($this->canDo->get('core.delete')) {
                ToolBarHelper::deleteList('', 'userinvites.delete', 'JTOOLBAR_DELETE');
                /*if ($this->state->get('filter.published') != -2) {
                    ToolBarHelper::deleteList('', 'userinvites.trash', 'JTOOLBAR_DELETE');
                }*/
            }

            if ($this->canDo->get('core.edit') || Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_checkin')) {
                ToolBarHelper::checkin('userinvites.checkin');
            }

            // Add a batch button
            /*if ($this->canDo->get('core.create') && $this->canDo->get('core.edit')
                    && $this->canDo->get('core.edit.state'))
            {
                // we use a standard Joomla layout to get the html for the batch button
                $layout = new FileLayout('joomla.toolbar.batch');
                $batchButtonHtml = $layout->render(array('title' => Text::_('JTOOLBAR_BATCH')));
                $bar->appendButton('Custom', $batchButtonHtml, 'batch');
            }*/
        }

        if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            ToolBarHelper::deleteList('', 'userinvites.delete', 'JTOOLBAR_EMPTY_TRASH');
            /*$toolbar->delete('userinvites.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);*/
        }

        if ($this->canDo->get('core.admin')) {
            ToolBarHelper::divider();
            ToolBarHelper::preferences('com_userinvites');
        }
    }
}