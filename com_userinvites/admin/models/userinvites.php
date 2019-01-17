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
 * UserInvites Records List Model
 */
class UserinvitesModelUserinvites extends JModelList
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
        if (empty($config['filter_fields']))
        {
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
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('u.id, u.email, u.groups, u.email_body, u.sent_by, u.sent_on, u.expires')
              ->from($db->quoteName('#__userinvites') . ' AS u');

        // Join the users for the sender:
        $query->select('us.name AS sender_name');
        $query->join('LEFT', '#__users AS us ON us.id = u.sent_by');

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where('u.email LIKE ' . $like);
            $query->where('u.sent_by LIKE ' . $like);
        }

        // Filter by published state
        /*$published = $this->getState('filter.published');

        if (is_numeric($published))
        {
            $query->where('a.published = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(a.published IN (0, 1))');
        }*/

        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering', 'u.sent_on');
        $orderDirn  = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
