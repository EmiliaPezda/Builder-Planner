<?php

namespace PlannerBundle\Form\Type;

use AppBundle\Entity\Task;
use PlannerBundle\Entity\Material;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('description', 'text')
                ->add('name', 'text')
                ->add('costs', 'integer');

        $builder->add('materials', CollectionType::class, array(
            'entry_type' => MaterialType::class,
            'entry_options' => array('label' => 'Add task'),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Material::class
        ));
    }
}