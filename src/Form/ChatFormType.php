<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\{TextType, HiddenType, CheckboxType, ChoiceType};
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\Chat\ChatModel;

class ChatFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $chat = $options['data'] ?? null;
        $isEdit = $chat && $chat->getId();

        $builder
            ->add('title', TextType::class)
            ->add('description', TextType::class, [
                'help' => 'Add short description about this chat room'
            ])
            ;

        /* From admin area you can create just public rooms */
        //if ($options['is_admin']) {
            //$chat->setIsPublic(true);
        //}

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChatModel::class,
            'is_admin' => false
        ]);
    }
    
}
