<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\{TextType, HiddenType, FileType};
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use App\Model\Chat\ChatModel;

class ChatFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $chat = $options['data'] ?? null;
        $isEdit = $chat && $chat->getId();

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
            ->add('title', TextType::class)
            ->add('description', TextType::class, [
                'help' => 'Add short description about this chat room'
            ])
            ->add('imageFile', FileType::class, [
                    'mapped' => false,
                    'required' => false,
                    'constraints' => $imageConstraints
                ])
            ;

        if ($isEdit) {
            $builder
                ->add('id', HiddenType::class)
                ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChatModel::class,
            'is_admin' => false
        ]);
    }
    
}
