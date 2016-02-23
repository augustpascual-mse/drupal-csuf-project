<?php
/**
 * Provides a 'Form' Block
 *
 * @Block(
 *   id = "apply_job_block",
 *   admin_label = @Translation("Apply Job Block"),
 * )
 */

namespace Drupal\blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

class ApplyJobBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build()
  {
      $form = \Drupal::formBuilder()->getForm('Drupal\forms\Form\ApplyForm');
      return $form;
  }

}
