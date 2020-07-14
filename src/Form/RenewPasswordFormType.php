<?php declare(strict_types=1);

namespace App\Form;

use App\Model\User\RenewPasswordModel;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{PasswordType, RepeatedType};

class RenewPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RenewPasswordModel::class
        ]);
    }
}
