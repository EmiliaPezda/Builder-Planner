<?php

namespace PlannerBundle\Controller;

use Faker\Provider\DateTime;
use function in_array;
use function print_r;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use PlannerBundle\Entity\Project;
use PlannerBundle\Entity\Task;
use PlannerBundle\Entity\Comment;
use PlannerBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    /**
     * @Route("/", name="startPage")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        if(!empty($user)){
            $projects = $user->getProjects();
            return $this->render('PlannerBundle:Default:startPage.html.twig', ['projects' => $projects]);
        }
        else{
            return $this->redirect('./register');
        }

    }

    /**
     * @Route("/new-project", name="new_project")
     */
    public function createProjectAction(Request $request)
    {
        $user = $this->getUser();

        $project = new Project();

        $form = $this->createFormBuilder($project)
        ->setMethod('POST')
        ->add('name', 'text')
        ->add('description', 'text')
        ->add('save', 'submit', ['label' => 'Create project'])
        ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $project = $form->getData();
            $this->getUser()->addProject($project);
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();


            return $this->redirectToRoute('show_project', array('name' => $project->getName()));
        }

        return $this->render('PlannerBundle:Project:new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/project/{name}", name="show_project")
     */
    public function showProjectAction(Request $request, $name)
    {

        $user = $this->getUser();
        $user_id = $user->getId();

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('PlannerBundle:Project')->findOneBy(['name' => $name]);
        $tasks = $project->getTasks();

        $task = new Task();
        $project_id = $project->getId();
        $task->setProject($project_id);

        $comments = [];

        foreach ($tasks as $oneTask) {
            $id = $oneTask->getId();

            //get all comment objects from specified task
            $taskComments = $em->getRepository('PlannerBundle:Comment')->findBy(['taskId' => $id]);

            foreach ($taskComments as $comment) {
                $author_id = $comment->getUserId();
                $author = $em->getRepository('PlannerBundle:User')->findOneBy(['id' => $author_id]);
                $authorName = $author->getUsername();
                $comments[] = array(
                    'comment' => $comment,
                    'author' =>$authorName,
                );
            }
        }


        //form to create task
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('name', 'text')
            ->add('description', 'text')
            ->add('costs', 'integer')
            ->add('save', 'submit', ['label' => 'Add task'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()  && $form->isValid()) {

            $taskName = $form["name"]->getData();
            $taskDescription = $form["description"]->getData();
            $taskCosts = $form["costs"]->getData();

            //adding task data
            $task = new Task();
            $task->setName($taskName);
            $task->setDescription($taskDescription);
            $task->setCosts($taskCosts);
            $task->setProject($project);
            $task->setDone(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $task_id = $task->getId();

            //adding materials to the table
//            foreach ( $materialsNew as $material){
//                $newMaterial = new Material();
//                $newMaterial->setTaskId($task_id);
//                $newMaterial->setName($material);
//                $em = $this->getDoctrine()->getManager();
//                $em->persist($newMaterial);
//                $em->flush();
//                $task->addMaterial($newMaterial);
//            }


            return $this->redirectToRoute('show_project', ['form' => $form->createView(), 'name' => $name, 'tasks' => $tasks, 'comments' => $comments]);
        }

        //if comment was added
        if(!empty($request->get('comment'))){
            $newComment = new Comment();
            $task1 = $em->getRepository('PlannerBundle:Task')->findOneBy(['id' => 1]);
            $newComment->setTaskId($task1);
            $newComment->setText($request->request->get('comment'));
            $newComment->setUserId($user);
            $newComment->setDate(new \DateTime('now'));

            $this->get('request')->query->remove('comment');

            $em = $this->getDoctrine()->getManager();
            $em->persist($newComment);
            $em->flush();

        }


        return $this->render('PlannerBundle:Project:show.html.twig', ['form' => $form->createView(), 'name' => $name, 'tasks' => $tasks, 'comments' => $comments]);
    }


    /**
     * @Route("/project/{name}/addUser", name="add_user")
     */
    public function addUserAction(Request $request, $name)
    {
        //form to add user

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('PlannerBundle:Project')->findOneBy(['name' => $name]);

        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('username', 'text')
            ->add('save', 'submit', ['label' => 'Add'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $myUser = $this->getUser();
            $myUsername = $myUser->getUsername();

            $username = $form->getData();
            $userAdded = $em->getRepository('PlannerBundle:User')->findOneBy(['username' => $username]);

            //user does not exists
            if(!$userAdded){
                $error =  "User does not exists";
                return $this->render('PlannerBundle:Project:addUser.html.twig', ['form' => $form->createView(),'name' => $name, 'error'=>$error]);
            } else{
                $em = $this->getDoctrine()->getManager();
                $projectOwners = $project->getUsers();

                $ownersUsername = [];

                //array with owner usernames
                foreach ($projectOwners as $owner){
                    $ownersUsername[]= $owner->getUsername();
                }

                //if user is added, see communicate
                if($userAdded->getUsername() === $myUsername){
                    $error = "You cannot add your own user";
                    return $this->render('PlannerBundle:Project:addUser.html.twig', ['form' => $form->createView(),'name' => $name, 'error'=>$error]);
                } elseif(in_array($userAdded->getUsername(), $ownersUsername)){
                    $error = "User already added";
                    return $this->render('PlannerBundle:Project:addUser.html.twig', ['form' => $form->createView(),'name' => $name, 'error'=>$error]);
                } else {
                    $userAdded->addProject($project);
                    $em->persist($userAdded);
                    $em->flush();
                    return $this->redirectToRoute('show_project', ['form' => $form->createView(), 'name' => $name]);
                }
            }

        }
        return $this->render('PlannerBundle:Project:addUser.html.twig', ['form' => $form->createView(),'name' => $name]);
    }


}
