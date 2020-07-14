<?php declare(strict_types=1);

namespace App\Form\TypeExtension;

use Symfony\Component\Form\{FormBuilderInterface, FormInterface, FormTypeExtensionInterface, FormView};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{EmailType, PasswordType, RepeatedType, TextType};

class IconWigdetExtension implements FormTypeExtensionInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO: Implement buildForm() method.
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['icon'] = $options['icon'];
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // TODO: Implement finishView() method.
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'icon' => null
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        yield EmailType::class;
        yield PasswordType::class;
        yield RepeatedType::class;
        yield TextType::class;
    } 
}