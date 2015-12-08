<?php
namespace Craft;

class PlainFormController extends BaseController
{
    protected $allowAnonymous = true;
    protected $defaultEmailTemplate = 'plainform/email/default';

    /**
     * View all entries
     * @return (object) Array of entries.
     */
    public function actionIndex()
    {
        $variables['tabs']  = $this->_getTabs();
        $variables['forms'] = craft()->plainForm->getAllForms();

        return $this->renderTemplate('plainform/index', $variables);
    }

    public function actionNewForm()
    {
        $variables['tabs'] = $this->_getTabs();
        $variables['form'] = new PlainForm_FormModel;

        return $this->renderTemplate('plainform/forms/_edit', $variables);
    }

    public function actionEditForm(array $variables = array())
    {
        $variables['form'] = PlainForm_FormRecord::model()->findById($variables['formId']);
        $variables['tabs'] = $this->_getTabs();

        return $this->renderTemplate('plainform/forms/_edit', $variables);
    }

    public function actionSaveForm()
    {
        $this->requirePostRequest();

        $form = new PlainForm_FormModel;

        $form->id                       = craft()->request->getPost('formId');
        $form->name                     = craft()->request->getPost('name');
        $form->handle                   = craft()->request->getPost('handle');
        $form->description              = craft()->request->getPost('description');
        $form->successMessage           = craft()->request->getPost('successMessage');
        $form->emailSubject             = craft()->request->getPost('emailSubject');
        $form->fromEmail                = craft()->request->getPost('fromEmail');
        $form->fromName                 = craft()->request->getPost('fromName');
        $form->replyToEmail             = craft()->request->getPost('replyToEmail');
        $form->toEmail                  = craft()->request->getPost('toEmail');
        $form->notificationTemplatePath = craft()->request->getPost('notificationTemplatePath');

        if (craft()->plainForm->saveForm($form)) {
            craft()->userSession->setNotice(Craft::t('Form saved.'));
            $this->redirectToPostedUrl($form);
        } else {
            craft()->userSession->setNotice(Craft::t("Couldn't save the form."));
        }

        // Send the saved form back to the template
        craft()->urlManager->setRouteVariables(array(
            'form' => $form
        ));
    }

    public function actionDeleteForm()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $formId = craft()->request->getRequiredPost('id');

        craft()->plainForm->deleteFormById($formId);
        $this->returnJson(array('success' => true));
    }

    public function actionEntriesIndex()
    {
        // Get the data
        $variables         = craft()->plainForm->getAllEntries();
        $variables['tabs'] = $this->_getTabs();

        // Render the template!
        $this->renderTemplate('plainform/entries/index', $variables);
    }

    public function actionViewEntry(array $variables = array())
    {
        $entry              = craft()->plainForm->getFormEntryById($variables['entryId']);
        $variables['entry'] = $entry;

        if (empty($entry)) {
            throw new HttpException(404);
        }

        $variables['form']        = craft()->plainForm->getFormById($entry->formId);
        $variables['tabs']        = $this->_getTabs();
        $variables['selectedTab'] = 'entries';
        $variables['data']        = $this->_filterPostKeys(unserialize($entry->data));

        $this->renderTemplate('plainform/entries/_view', $variables);
    }

    public function actionSaveFormEntry()
    {
        // Require a post request
        $this->requirePostRequest();

        // Honeypot validation
        $honeypot = craft()->request->getPost('plainFormHoneypot');

        if ($honeypot) {
            $honeypotValue = craft()->request->getPost($honeypot);

            if (!empty($honeypotValue)) {
                craft()->userSession->setFlash('honeypot', 'yup');
                $this->redirect(craft()->request->getUrl());
            }
        }

        // Set the required errors array
        $errors['required'] = array();

        // Get the form
        $plainFormHandle = craft()->request->getPost('plainFormHandle');

        if (!$plainFormHandle) {
            throw new HttpException(404);
        }

        // Required attributes
        $required = craft()->request->getPost('required');

        if ($required) {
            foreach ($required as $key => $message) {
                $value = craft()->request->getPost($key);

                if (empty($value)) {
                    $errors['required'][$key] = $message;
                }
            }
        }

        if (!empty($errors['required'])) {
            craft()->userSession->setError($errors);
            craft()->userSession->setFlash('post', craft()->request->getPost());

            $this->redirect(craft()->request->getUrl());
        }

        if (!$plainFormHandle) {
            throw new HttpException(404);
        }

        // Get the form model, need this to save the entry
        $form = craft()->plainForm->getFormByHandle($plainFormHandle);

        if (!$form) {
            throw new HttpException(404);
        }

        // @todo Need to exclude certain keys
        $excludedPostKeys = array();

        // Form data
        $data = serialize(craft()->request->getPost());

        // New form entry model
        $plainFormEntry = new PlainForm_EntryModel();

        // Set entry attributes
        $plainFormEntry->formId = $form->id;
        $plainFormEntry->data   = $data;
        $plainFormEntry->ip     = $_SERVER['REMOTE_ADDR'];

        // Save it
        if (craft()->plainForm->saveFormEntry($plainFormEntry)) {
            // Time to make the notifications
            if ($this->_sendEmailNotification($plainFormEntry, $form)) {
                // Set the message
                if (!empty($form->successMessage)) {
                    $message = $form->successMessage;
                } else {
                    $message = Craft::t('Thank you, we have received your submission and we\'ll be in touch shortly.');
                }

                craft()->userSession->setFlash('success', $message);
                $this->redirectToPostedUrl();
            } else {
                craft()->userSession->setError(Craft::t('We\'re sorry, but something has gone wrong.'));
            }

            craft()->userSession->setNotice(Craft::t('Entry saved.'));
            $this->redirectToPostedUrl($plainformEntry);
        } else {
            craft()->userSession->setNotice(Craft::t("Couldn't save the form."));
        }

        // Send the saved form back to the template
        craft()->urlManager->setRouteVariables(array(
            'entry' => $plainFormEntry
        ));
    }

    public function actionDeleteEntry()
    {
        $this->requirePostRequest();

        $entryId = craft()->request->getRequiredPost('entryId');

        if (craft()->elements->deleteElementById($entryId)) {
            craft()->userSession->setNotice(Craft::t('Entry deleted.'));
            $this->redirectToPostedUrl();
        } else {
            craft()->userSession->setError(Craft::t("Couldn't delete entry."));
        }

    }

    protected function _sendEmailNotification($record, $form)
    {
        // Put in work setting up data for the email template.
        $data = new \stdClass($data);

        $data->entryId = $record->id;

        $postData = unserialize($record->data);
        $postData = $this->_filterPostKeys($postData);

        foreach ($postData as $key => $value) {
            $data->$key = $value;
        }

        // Email template
        if (craft()->templates->findTemplate($form->notificationTemplatePath)) {
            $template = $form->notificationTemplatePath;
        }

        if (!$template) {
            $template = $this->defaultEmailTemplate;
        }

        $variables = array(
            'data'  => $postData,
            'form'  => $form,
            'entry' => $record,
        );

        $message = craft()->templates->render($template, $variables);

        // Send the message
        if (craft()->plainForm->sendEmailNotification($form, $message, $postData, true, null)) {
            return true;
        } else {
            return false;
        }
    }

    protected function _filterPostKeys($post)
    {
        $filterKeys = array(
            'action',
            'required',
            'redirect',
            'plainformhandle',
            'plainformHoneypot',
        );

        if (isset($post['plainFormHoneypot'])) {
            $honeypot = $post['plainFormHoneypot'];
            array_push($filterKeys, $honeypot);
        }

        if (is_array($post)) {
            foreach ($post as $k => $v) {
                if (in_array(strtolower($k), $filterKeys)) {
                    unset($post[$k]);
                }
            }
        }

        return $post;
    }

    protected function _getTabs()
    {
        return array(
            'forms'   => array(
                'label' => "Forms",
                'url'   => UrlHelper::getUrl('plainform/'),
            ),
            'entries' => array(
                'label' => "Entries",
                'url'   => UrlHelper::getUrl('plainform/entries'),
            ),
        );
    }

    public function renderTest()
    {
        $this->requirePostRequest();

        $post = craft()->request->getPost();

        Craft::dd($post);
        //die(var_dump($post));

        $string = craft()->request->getPost('plainFormTitle');

        $renderedString = $this->renderString($string, $post);

        //die(var_dump($renderedString));
    }
}
