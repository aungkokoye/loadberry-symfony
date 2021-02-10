<?php

namespace App\Form;

use App\Entity\FileUpload;
use App\Service\UtilityService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class FileUploadType extends AbstractType
{
    /**
     * @var UtilityService
     */
    private $utility;

    public function __construct(UtilityService $utility)
    {
        $this->utility = $utility;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     * @throws InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $maxFileUploadSize = $this->utility->getMaxFileUploadSize();
        $builder
            ->add('description')
            ->add('file', FileType::class, [
                'label' => 'File Upload ( required )',

                'help'  => 'Max File size '. strtoupper($maxFileUploadSize) .
                    '. Allowed file formats (all images, all videos, PDF, Word, Excel, MS office, text)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => true,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                                 'maxSize' => $maxFileUploadSize,
                                 'mimeTypes' => [
                                     'image/*',
                                     'video/*',
                                     'application/pdf',
                                     'application/x-pdf',
                                     'application/msword',
                                     'application/vnd.ms-excel',
                                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                     'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                     'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                     'text/plain'
                                 ],
                                 'mimeTypesMessage' => 'Please upload a valid type of document',
                             ]),
                    new NotBlank()
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FileUpload::class,
        ]);
    }
}
