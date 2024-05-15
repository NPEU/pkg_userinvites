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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
#use Joomla\CMS\Versioning\VersionableControllerTrait;

use NPEU\Component\Userinvites\Administrator\Helper\UserinvitesHelper;


class SendinvitesController extends FormController
{
    #use VersionableControllerTrait;

    public function getModel($name = 'Userinvite', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to save and send invites.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app     = Factory::getApplication();
        $lang    = Factory::getLanguage();
        $model   = $this->getModel();
        $table   = $model->getTable();
        $data    = Factory::getApplication()->input->get('jform', [], 'post', 'array');
        $context = "$this->option.edit.$this->context";
        $task    = $this->getTask();

        $form = new Form('sendinvites', ['control'=>'jform']);
        Form::addFormPath(JPATH_COMPONENT . '/forms');
        $form->loadFile('sendinvites', false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);
        #echo '<pre>'; var_dump($data); echo '</pre>'; exit;
        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item, false
                )
            );

            return false;
        }

        // Split the emails into an array. Should probably use a filter for this, but can't see
        // how to add custom filters to Form.
        $emails = explode("\n", $validData['emails']);
        foreach ($emails as $id => $email) {
            $emails[$id] = trim($email);
        }
        $validData['emails'] = $emails;

        // Attempt to save the data.
        if (!$new_ids = $model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            #$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            #$this->setMessage($this->getError(), 'error');
            $app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
            #$app->enqueueMessage($app->getError(), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item, false
                )
            );

            return false;
        }

        // SEND EMAILS HERE
        $failed = false;
        foreach ($new_ids as $id) {
            $item = $model->getItem($id);
            $r = UserinvitesHelper::sendInvite($item);
            if ($r instanceof JException) {
                #JError::raiseWarning(100, Text::_('COM_USERINVITES_ERROR_FAILED_EMAIL'));
                throw new GenericDataException(Text::_('COM_USERINVITES_ERROR_FAILED_EMAIL', 100));
                $failed = true;
            }
        }
        if (!$failed) {
            $this->setMessage(
                Text::_(
                    ($lang->hasKey($this->text_prefix . '_SAVE_SUCCESS')
                        ? $this->text_prefix
                        : 'JLIB_APPLICATION') . '_SAVE_SUCCESS'
                )
            );
        }

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item, false)
        );
        return true;
    }


    /**
    * Implement to allowAdd or not
    *
    * Not used at this time (but you can look at how other components use it....)
    * Overwrites: JControllerForm::allowAdd
    *
    * @param array $data
    * @return bool
    */
    /*protected function allowAdd($data = array())
    {
        return parent::allowAdd($data);
    }*/
    /**
    * Implement to allow edit or not
    * Overwrites: JControllerForm::allowEdit
    *
    * @param array $data
    * @param string $key
    * @return bool
    */
    /*protected function allowEdit($data = array(), $key = 'id')
    {
        $id = isset( $data[ $key ] ) ? $data[ $key ] : 0;
        if( !empty( $id ) )
        {
            return Factory::getApplication()->getIdentity()->authorise( "core.edit", "com_userinvites.userinvite." . $id );
        }
    }*/

    /*public function batch($model = null)
    {
        $model = $this->getModel('userinvite');
        $this->setRedirect((string)Uri::getInstance());
        return parent::batch($model);
    }*/
}
