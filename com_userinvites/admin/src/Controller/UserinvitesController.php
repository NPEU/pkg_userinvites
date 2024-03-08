<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Userinvites\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

class UserinvitesController extends AdminController
{

    public function getModel($name = 'Userinvite', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to resend invites.
     */
    public function resend($key = null)
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Get items to remove from the request.
        $cid = Factory::getApplication()->input->get('cid', [], '', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            #JError::raiseWarning(500, Text::_($this->text_prefix . '_NO_ITEM_SELECTED'));
            throw new GenericDataException(Text::_($this->text_prefix . '_NO_ITEM_SELECTED', 500));
        } else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            jimport('joomla.utilities.arrayhelper');
            ArrayHelper::toInteger($cid);

            $save_data = array('emails' => array());

            // Get the invite data from ids, then resave them to update sent time and code:
            foreach ($cid as $id) {
                $item = $model->getItem($id);
                $save_data['emails'][$id] = $item->email;
            }
            if ($model->save($save_data)) {

                // Then reloop to to actaully send the emails:
                foreach ($cid as $id) {
                    $item = $model->getItem($id);
                    if (!UserinvitesHelper::sendInvite($item)) {
                        #JError::raiseWarning(100, Text::_('COM_USERINVITES_ERROR_FAILED_EMAIL'));
                        throw new GenericDataException(Text::_('COM_USERINVITES_ERROR_FAILED_EMAIL'), 100);
                        return;
                    }
                }
            }

            $m =  count($cid) == 1 ? 'COM_USERINVITES_N_ITEMS_RESENT_1' : 'COM_USERINVITES_N_ITEMS_RESENT_MORE';
            $this->setMessage(Text::plural($m, count($cid)));
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }
}
