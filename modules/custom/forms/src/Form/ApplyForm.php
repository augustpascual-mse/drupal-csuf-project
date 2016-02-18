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
    // TODO Validate file resume if less than 1MB

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
      // TODO Email company representative
      // TODO Update Database Candidate Applied to Job
      //$resume = $form_state->getUserInput('resume');
      //$resumeFilename = $resume['files']['resume'];
      $resumeFileId = $form_state->getValue('resume');
      $mailManager = \Drupal::service('plugin.manager.mail');

     $module = 'job_mailer';
     $key = 'apply_job';
     $to = 'augustpascual23@gmail.com';
     $params['message'] = 'test';
     $langcode = \Drupal::currentUser()->getPreferredLangcode();
     $send = true;

     $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
     dpm($result);

  }

}
