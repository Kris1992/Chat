<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\{EmailType, PasswordType, RepeatedType, TextType, 
HiddenType, CheckboxType, ChoiceType};
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\User\UserModel;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['data'] ?? null;
        $isEdit = $user && $user->getId();

        $builder
            ->add('email', EmailType::class, [
                'icon' => 'fas fa-envelope'
            ])
            ->add('login', TextType::class, [
                'icon' => 'fas fa-user'
            ])
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ;

        if (!$isEdit) {
            $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => [ 
                    'help' => 'Password should contain at least 2 numbers and 3 letters (lower and uppercase)',
                    'icon' => 'fas fa-key', 
                ],
                'second_options' => [
                    'icon' => 'fas fa-key', 
                ]
            ])
            ->add('agreeTerms', CheckboxType::class);
        } else {
            $builder
                ->add('id', HiddenType::class)
                ;
        }

        if($options['is_admin']) {
            $builder
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN'
                ],
            ]);
        } 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserModel::class,
            'is_admin' => false
        ]);
    }
    
}
