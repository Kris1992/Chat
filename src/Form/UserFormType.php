<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\{EmailType, FileType, PasswordType, RepeatedType, TextType, 
HiddenType, CheckboxType, ChoiceType};
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use App\Model\User\UserModel;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['data'] ?? null;
        $isEdit = $user && $user->getId();
        $isAdmin = $user && $user->isAdmin();

        $imageConstraints = [
            new Image([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ]
            ])
        ];

        $builder
            ->add('email', EmailType::class, [
                'icon' => 'fas fa-envelope'
            ])
            ->add('login', TextType::class, [
                'icon' => 'fas fa-user',
                'disabled' => $isEdit
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
                ->add('imageFile', FileType::class, [
                    'mapped' => false,
                    'required' => false,
                    'constraints' => $imageConstraints
                ])
                ;
        }

        if($options['is_admin'] && !$isAdmin) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'multiple' => true,
                    'choices'  => [
                        'User' => 'ROLE_USER',
                        'Moderator' => 'ROLE_MODERATOR',
                        'Admin' => 'ROLE_ADMIN'
                    ],
                    'help' => 'Remember user role is required, so even if you take it off it will be applied to accout'
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
