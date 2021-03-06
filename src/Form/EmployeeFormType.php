<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Employee;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method createFormBuilder()
 */
class EmployeeFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class, ['required' => true])
            ->add('last_name', TextType::class)
            ->add('career', TextType::class)
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d');
                },
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Employee::class,
            'csrf_protection' => false,
        ));
    }
}