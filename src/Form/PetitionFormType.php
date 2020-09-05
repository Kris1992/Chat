<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\{TextType, ChoiceType, TextareaType, HiddenType, CollectionType};
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\Petition\{PetitionModel, PetitionConstants};

class PetitionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class)
            ->add('type', ChoiceType::class, [
                'multiple' => false,
                'choices'  => PetitionConstants::TYPES_DESC,
            ])
            ->add('description', TextareaType::class, [
                'help' => 'Add description of problem'
            ])
            ->add('attachementsFilenames', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'attr' => [
                    'class' => 'js-attachments-filenames'
                ],
                'label' => false,
                'allow_add' => true,
                'by_reference' => false,
                'required' => false
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PetitionModel::class,
        ]);
    }
    
}
