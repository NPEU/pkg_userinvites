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
class UserinvitesControllerSendinvites extends JControllerForm
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
     * Method to save and send invites.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app     = JFactory::getApplication();
        $lang    = JFactory::getLanguage();
        $model   = $this->getModel();
        $table   = $model->getTable();
        $data    = JFactory::getApplication()->input->get('jform', array(), 'post', 'array');
        $context = "$this->option.edit.$this->context";
        $task    = $this->getTask();

        $form = new JForm('sendinvites', array('control'=>'jform'));
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        $form->loadFile('sendinvites', false);
        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);
        #echo "<pre>\n"; var_dump($validData); echo "</pre>\n"; exit;
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
                JRoute::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend($recordId, $key), false
                )
            );

            return false;
        }

        // Split the emails into an array. Should probably use a filter for this,
        // but can't see how to add custom filters to jform.
        $emails = explode("\n", $validData['emails']);
        foreach ($emails as $id => $email) {
            $emails[$id] = trim($email);
        }
        $validData['emails'] = $emails;

        #echo '<pre>'; var_dump($emails); echo '</pre>'; exit;

        // Attempt to save the data.
        if (!$new_ids = $model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(
                JRoute::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend($recordId, $key), false
                )
            );

            return false;
        }

        // SEND EMAILS HERE
        $failed = false;
        foreach ($new_ids as $id) {
            $item = $model->getItem($id);
            #echo "<pre>\n"; var_dump($item); echo "</pre>\n";
            $r = UserinvitesHelper::sendInvite($item);
            #echo "<pre>\n"; var_dump($r); echo "</pre>\n"; exit;
            if ($r instanceof JException) {
                JError::raiseWarning(100, JText::_('COM_USERINVITES_ERROR_FAILED_EMAIL'));
                $failed = true;
            }

        }
        if (!$failed) {
            $this->setMessage(
                JText::_(
                    ($lang->hasKey($this->text_prefix . '_SAVE_SUCCESS')
                        ? $this->text_prefix
                        : 'JLIB_APPLICATION') . '_SAVE_SUCCESS'
                )
            );
        }



        #exit;
        // Redirect
        // Set the record data in the session.
        /*$recordId = $model->getState($this->context . '.id');
           $this->holdEditId($context, $recordId);
           $app->setUserState($context . '.data', null);
           $model->checkout($recordId);

           // Redirect back to the edit screen.
           $this->setRedirect(
           JRoute::_(
           'index.php?option=' . $this->option . '&view=' . $this->view_item
           . $this->getRedirectToItemAppend($recordId, $key), false
           )
           );*/

        #echo "<pre>\n"; var_dump(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item, false)); echo "</pre>\n"; exit;

        $this->setRedirect(
            JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item, false)
        );
        return true;
    }
}