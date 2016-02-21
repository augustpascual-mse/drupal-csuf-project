<?php
/**
 * @file
 * Contains \Drupal\forms\Form\ApplyForm.
 */

namespace Drupal\forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

class ApplyForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
       return 'forms_apply_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
      $node = \Drupal::routeMatch()->getParameter('node');
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      $form['name'] = array(
         '#type' => 'textfield',
         '#title' => t('Name'),
         '#required' => TRUE,
      );
      $form['email'] = array(
         '#type' => 'email',
         '#title' => t('Email'),
         '#required' => TRUE,
      );
      $form['phone'] = array(
         '#type' => 'tel',
         '#title' => t('Phone'),
         '#required' => TRUE,
      );
      $form['resume'] = array(
         '#type' => 'managed_file',
         '#title' => t('Resume'),
         '#required' => TRUE,
         '#upload_location' => 'private://resume/'.$node->id().'/'.$user->get('uid')->value,
      );
      $form['submit'] = array(
         '#type' => 'submit',
         '#value' => t('Submit'),
      );

      return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    // TODO Validate file resume if less than 1MB and limit filetypes

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
      //TODO v2 Send Email via Cron not on Submit

      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      $username = $user->get('name')->value;
      $userId = $user->get('uid')->value;

      $mailManager = \Drupal::service('plugin.manager.mail');

      $jobNode = \Drupal::routeMatch()->getParameter('node');
      $jobNodeTitle = $jobNode->getTitle();

      $companyNodeEntity = $jobNode->get('field_company');
      $companyNode =  \Drupal\node\Entity\Node::load( $companyNodeEntity->entity->id());
      $companyEmail = $companyNode->field_email->value;

      $resumeFileId = $form_state->getValue('resume');
      $resumeFile = db_select('file_managed', 'f')
         ->condition('f.fid', $resumeFileId, '=')
         ->fields('f', array('uri'))
         ->execute()->fetchField();
      $atttachment = array(
        'filepath' => $resumeFile
      );

      $module = 'job_mailer';
      $key = 'apply_job';
      $params['job_title'] = $jobNodeTitle;
      $params['message'] =
          "<html>
           <p>Please see attached resume for user: $username
           </html>";
      $params['attachment'] = $atttachment;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;
      $reply =  \Drupal::config('system.site')->get('mail');

      $result = $mailManager->mail($module, $key, $companyEmail, $langcode, $params, $reply, $send);

      db_insert('user_job_application')
          ->fields(array(
              'job_id' => $jobNode->id(),
              'user_id' => $userId,
          ))
          ->execute();

      drupal_set_message('Your application has been sent.');

    }

}
