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
 * Userinvites Controller
 */
class UserinvitesControllerUserinvites extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     */
    public function getModel($name = 'Userinvite', $prefix = 'UserinvitesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Method to resend invites.
     */
    public function resend($key = null)
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to remove from the request.
        $cid = JFactory::getApplication()->input->get('cid', array(), '', 'array');

        if (!is_array($cid) || count($cid) < 1)
        {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else
        {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            jimport('joomla.utilities.arrayhelper');
            JArrayHelper::toInteger($cid);

            $save_data = array('emails' => array());
            // Get the invite data from ids, then resave them to update sent
            // time and code:
            foreach ($cid as $id) {
                $item = $model->getItem($id);
                $save_data['emails'][$id] = $item->email;
            }
            if ($model->save($save_data)) {
                #$items = array();
                // Then reloop to to actaully send the emails:
                foreach ($cid as $id) {
                    $item = $model->getItem($id);
                    if (!UserinvitesHelper::sendInvite($item)) {
                        JError::raiseWarning(100, JText::_('COM_USERINVITES_ERROR_FAILED_EMAIL'));
                        return;
                    }
                }
            }

            $m =  count($cid) == 1 ? 'COM_USERINVITES_N_ITEMS_RESENT_1' : 'COM_USERINVITES_N_ITEMS_RESENT_MORE';
            #$this->setMessage(JText::_($m, count($cid)));
            $this->setMessage(JText::plural($m, count($cid)));
            #$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_RESENT', count($cid)));
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }
}