<?php

namespace PlannerBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PlannerBundle\Entity\User;
use const true;

class BudgetController extends Controller
{

    /**
     * @Route("/budget", name="see_budget")
     */
    public function seeBudgetAction(Request $request)
    {
        $user = $this->getUser();

        $currentBalance = $user->getBudget();

        $repo = $this->getDoctrine()->getManager()->getRepository('PlannerBundle:User');
        $expenses = $repo->getExpenses($user);

        $form1 = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('budget', 'number', array(
                'label' => 'Add resources to your budget',
                'allow_extra_fields' => true,
            ))
            ->add('save', 'submit', ['label' => 'Submit'])
            ->getForm();

        $form1->handleRequest($request);

        $form2 = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('substractbudget', 'number', array(
                'label' => 'Substract from your budget',
                'allow_extra_fields' => true,
            ))
            ->add('save', 'submit', ['label' => 'Submit'])
            ->getForm();

        $form2->handleRequest($request);


            if ($form1->isSubmitted() && $form1->isValid()) {
                $addedMoney = $form1->getData();
                $budget = $addedMoney['budget'];
                $user->addBudget($budget);

                //budget with added amount
                $currentBalance = $user->getBudget();

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->render('PlannerBundle:Budget:budget.html.twig', ['form1' => $form1->createView(), 'form2' => $form2->createView(), 'balance' => $currentBalance, 'expenses'=> $expenses]);

            } elseif ($form2->isSubmitted() && $form2->isValid()) {
                $substractedMoney = $form2->getData();
                $budget = $substractedMoney['substractbudget'];
                $user->substractBudget($budget);

                //budget with substracted amount
                $currentBalance = $user->getBudget();

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->render('PlannerBundle:Budget:budget.html.twig', ['form1' => $form1->createView(), 'form2' => $form2->createView(), 'balance' => $currentBalance, 'expenses'=> $expenses]);
            }

        return $this->render('PlannerBundle:Budget:budget.html.twig', ['form1' => $form1->createView(), 'form2' => $form2->createView(), 'balance'=> $currentBalance, 'expenses'=> $expenses]);
    }
}