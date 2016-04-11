<?php
/**
 * @file
 * Contains \Drupal\user_settings\Controller\UserSettingsController.
 */

namespace Drupal\user_settings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;

class UserSettingsController extends ControllerBase
{
    public function index()
    {
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $userId = $user->get('uid')->value;
        return new TrustedRedirectResponse('/user/'.$userId.'/main');
    }
}
