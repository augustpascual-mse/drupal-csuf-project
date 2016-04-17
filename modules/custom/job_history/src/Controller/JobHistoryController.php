<?php
/**
 * @file
 * Contains \Drupal\job_history\Controller\JobHistoryController.
 */

namespace Drupal\job_history\Controller;

use Drupal\Core\Controller\ControllerBase;

class JobHistoryController extends ControllerBase
{
    public function content()
    {
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $userId = $user->get('uid')->value;
        $data = array(
                    array('job_title' => '',
                          'job_path' => '',
                          'company_name' => '',
                          'application_date' => '')
                 );
        $appliedJobs = db_select('user_job_application', 'uja')
           ->condition('uja.user_id', $userId, '=')
           ->fields('uja', array('job_id', 'date'))
           ->orderBy('uja.date', 'DESC')
           ->execute()->fetchAll();
        if($appliedJobs){
            $x = 0;
            foreach($appliedJobs as $appliedJob){
                $jobNode =  \Drupal\node\Entity\Node::load($appliedJob->job_id);
                $jobTitle = $jobNode->getTitle();
                $jobPathAlias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$appliedJob->job_id);

                $companyNodeEntity = $jobNode->get('field_company');
                $companyNode =  \Drupal\node\Entity\Node::load($companyNodeEntity->entity->id());
                $companyName = $companyNode->getTitle();

                $data[$x]['job_title'] = $jobTitle;
                $data[$x]['job_path'] = $jobPathAlias;
                $data[$x]['company_name'] = $companyName;
                $data[$x]['application_date'] = $appliedJob->date;
                $x++;
            }
      }
      $markUp = $this->createMarkUp($data);
      return array(
          '#type' => 'markup',
          '#markup' => $markUp,
        );

    }

    public function createMarkUp($variables)
    {
        $markUp = "<div class='history'><table class='mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp'>
                      <thead>
                        <tr>
                          <th class='mdl-data-table__cell--non-numeric'>Job Title</th>
                          <th class='mdl-data-table__cell--non-numeric'>Company</th>
                          <th class='mdl-data-table__cell--non-numeric'>Application Date</th>
                        </tr>
                      </thead>
                      <tbody>";
        foreach($variables as $data){

            $markUp .= "<tr>";
            $markUp .= "<td class='mdl-data-table__cell--non-numeric'>"."<a href='".$data['job_path']."'>".$data['job_title']."</a></td>";
            $markUp .= "<td class='mdl-data-table__cell--non-numeric'>".$data['company_name']."</td>";
            $markUp .= "<td class='mdl-data-table__cell--non-numeric'>".$data['application_date']."</td>";
            $markUp .= "</tr>";

        }
        $markUp .= "</tbody></table></div>";
        return $markUp;
    }
}
