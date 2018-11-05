<?php

namespace PlannerBundle\Repository;

use Doctrine\ORM\EntityRepository;
use PlannerBundle\Entity\User;
use PlannerBundle\Entity\Project;
use PlannerBundle\Entity\Task;

/**
 * UserRepository
 */

class UserRepository extends EntityRepository
{
    public function getExpenses($user){

        $projects = $user->getProjects();

        $userCosts = 0;

        foreach ($projects as $project) {
            $object_task = $project->getTasks();
            foreach ($object_task as $task){
                if($task->isDone() == 0) {
                    $cost = $task->getCosts();
                    $userCosts += $cost;
                }
            }
        }

        return $userCosts;

    }
}
