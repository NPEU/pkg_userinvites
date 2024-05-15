<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Userinvites\Administrator\Model;

defined('_JEXEC') or die;


use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Userinvites List Model
 */
class UserinvitesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'sent_to',
                'sent_by',
                'sent_on',
                'expires',
                'status',
                'id'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @note    Calling getState in this method will result in recursion.
     */
    /*protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        // Load the filter state.
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.published', $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string'));

        // Load the parameters.
        $params = ComponentHelper::getParams('com_userinvites');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }*/

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Gets an array of objects from the results of database query.
     *
     * @param   string   $query       The query.
     * @param   integer  $limitstart  Offset.
     * @param   integer  $limit       The number of records.
     *
     * @return  array  An array of results.
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $items = parent::_getList($query, $limitstart, $limit);

        foreach ($items as $item) {
            $item->status = time() < strtotime($item->expires)
                          ? 'Pending'
                          : 'Expired';
        }
        return $items;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Initialize variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('u.id, u.email, u.groups, u.email_body, u.sent_by, u.sent_on, u.expires')
              ->from($db->quoteName('#__userinvites') . ' AS u');

        // Join the users for the sender:
        $query->select('us.name AS sender_name');
        $query->join('LEFT', '#__users AS us ON us.id = u.sent_by');

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $like = $db->quote('%' . $search . '%');
            $query->where('u.email LIKE ' . $like);
            $query->where('u.sent_by LIKE ' . $like);
        }

        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering', 'u.sent_on');
        $orderDirn  = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
