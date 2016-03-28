<?php
/**
 * @file
 * Contains \Drupal\user_settings\Form\UserSettingsForm.
 */

namespace Drupal\user_settings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

class UserSettingsForm extends FormBase
{

    public function __construct()
    {
        $this->user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $this->username = $this->user->get('name')->value;
        $this->userId = $this->user->get('uid')->value;
    }

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
      $userExist = $this->getUserSettings($this->userId);
      $hideProfileFromUser = 0;
      $hideProfileFromCompany = 0;

      if ($userExist) {
          $hideProfileFromUser = $userExist['hide_people'];
          $hideProfileFromCompany = $userExist['hide_company'];
      }

      $form['hide_company'] = array(
        '#type' => 'checkbox',
        '#title' => t('Hide Profile to Any Companies'),
        '#default_value' => $hideProfileFromCompany
      );

      $form['hide_people'] = array(
        '#type' => 'checkbox',
        '#title' => t('Hide Profile to Any User'),
        '#default_value' => $hideProfileFromUser
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
      $hideProfileFromCompany = $form_state->getValue('hide_company');
      $hideProfileFromUser =  $form_state->getValue('hide_people');

      $userExist = $this->getUserSettings($this->userId);

      if ($userExist) {
          db_update('user_profile_settings')
              ->fields(array(
                  'user_id' => $this->userId,
                  'hide_company' => $hideProfileFromCompany,
                  'hide_people' => $hideProfileFromUser,
                  'date'    => date('Y-m-d H:i:s')
              ))
              ->condition('user_id', $this->userId, '=')
              ->execute();
      } else {
          db_insert('user_profile_settings')
              ->fields(array(
                  'user_id' => $this->userId,
                  'hide_company' => $hideProfileFromCompany,
                  'hide_people' => $hideProfileFromUser,
                  'date'    => date('Y-m-d H:i:s')
              ))
              ->execute();
      }
  }

    public function getUserSettings($userId)
    {
        $userExist = db_select('user_profile_settings', 'ups')
         ->condition('ups.user_id', $userId, '=')
         ->fields('ups', array('user_id', 'hide_people', 'hide_company'))
         ->execute()->fetchAssoc();

        return $userExist;
    }
}
