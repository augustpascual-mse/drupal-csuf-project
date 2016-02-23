<?php
/**
 * Provides a Block
 *
 * @Block(
 *   id = "apply_button_block",
 *   admin_label = @Translation("Apply Button Block"),
 * )
 */

namespace Drupal\blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

class ApplyButtonBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build()
  {
      return array(
         '#markup' => '<a class="mdl-button mdl-js-button" id="apply-job" ng-click="applyJob()" ng-show="jobContent">Apply</a>',
         '#cache' => array(
             'max-age' => 0,
         ),
       );
  }

}
