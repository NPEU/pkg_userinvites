<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->filter_order);
$listDirn  = $this->escape($this->filter_order_dir);

function getGroups($groupsJSON, $usergroups) {
    $usersgroups = array();
    $groups = json_decode($groupsJSON);
    foreach ($groups as $group) {
        $usersgroups[] = $usergroups[$group];
    }
    return implode(', ', $usersgroups);
}

?>
<form action="index.php?option=com_userinvites&view=userinvites" method="post" id="adminForm" name="adminForm">
    <?php if (!empty($this->items)): ?>
    <div class="row-fluid">
        <div class="span6">
            <?php echo JText::_('COM_USERINVITES_INVITES_FILTER'); ?>
            <?php
                echo JLayoutHelper::render(
                    'joomla.searchtools.default',
                    array('view' => $this)
                );
            ?>
        </div>
    </div>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th width="2%"><?php echo JText::_('COM_USERINVITES_HEADING_NUM'); ?></th>
                <th width="4%">
                    <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                <th width="20%">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_SENT_TO', 'email', $listDirn, $listOrder); ?>
                </th>
                <th width="10%" class="center">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_SENT_BY', 'sent_by', $listDirn, $listOrder); ?>
                </th>
                <th width="20%" class="center">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_SENT_DATE', 'sent_date', $listDirn, $listOrder); ?>
                </th>
                <th width="20%" class="center">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_EXPIRED_DATE', 'expired_date', $listDirn, $listOrder); ?>
                </th>
                <th width="10%" class="center">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_STATUS', 'status', $listDirn, $listOrder); ?>
                </th>
                <th width="10%" class="center">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_GROUPS', 'groups', $listDirn, $listOrder); ?>
                </th>
                <th width="4%" class="center">
                    <?php echo JHtml::_('grid.sort', 'COM_USERINVITES_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="9">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $link = JRoute::_('index.php?option=com_userinvites&task=record.edit&id=' . $item->id);
            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
        ?>
            <tr>
                <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td>
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td>
                    <?php echo $this->escape($item->email); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->sender_name); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('date', $item->sent_on, JText::_('DATE_FORMAT_LC2')); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('date', $item->expires, JText::_('DATE_FORMAT_LC2')); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->status); ?>
                </td>
                <td class="center">
                    <?php echo getGroups($item->groups, $this->usergroups); ?>
                </td>
                <td class="center">
                    <?php echo $item->id; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <?php echo JText::_('COM_USERINVITES_NO_RECORDS'); ?>
    <?php endif; ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
