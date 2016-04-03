<?php
/**
 * @file
 * Contains \Drupal\forms\Form\UserSettingsForm.
 */

namespace Drupal\forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

class UserSettingsForm extends FormBase
{
    /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
      return 'user_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      $form['name'] = array(
         '#type' => 'textfield',
         '#title' => t('Name'),
         '#required' => true,
      );
      $form['email'] = array(
         '#type' => 'email',
         '#title' => t('Email'),
         '#required' => true,
      );
      $form['phone'] = array(
         '#type' => 'tel',
         '#title' => t('Phone'),
         '#required' => true,
      );
      $form['resume'] = array(
         '#type' => 'managed_file',
         '#title' => t('Resume'),
         '#required' => true,
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
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

  }
}
