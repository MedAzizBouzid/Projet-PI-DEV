<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class, [
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder' => 'Enter Email',  
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),

                ]
                ])
            ->add('nom',TextType::class, [
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder' => 'Enter First Name',  
                ],
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern'=>'/^[a-zA-Z]+$/',
                        'message'=>'the name must not have numbers'
                    
                ]),

                ]
                ])
            ->add('prenom',TextType::class, [
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder' => 'Enter Last Name',  
                ],
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern'=>'/^[a-zA-Z]+$/',
                        'message'=>'the lastname must not have numbers'
                    
                ]),

                ]
                ])
            ->add('userName',TextType::class, [
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder' => 'Enter UserName',  
                ],
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern'=>'/^[a-zA-Z]+$/',
                        'message'=>'the username must not have numbers'
                    
                ]),

                ]
                ])
            ->add('numTel',TelType::class,[
                'attr'=>[
                    'class'=>'form-control' ,
                    'placeholder' => 'Enter Phone number', 
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a phone number',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your phone number should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 8,
                    ]),
                    new Regex([
                        'pattern'=>'/^[0-9]+$/',
                        'message'=>'Your password should be at least 8 characters:upper lower and number'
                    
                ]),
            ]])
            
            ->add('image',FileType::class, [
                'attr'=>[
                    'class'=>'form-control'  
                ],
                'label' => 'Image du Produit (Des fichiers images seulement)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image ',
                    ])
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password',
                
                    'class'=>'form-control',
                    'placeholder' => 'Enter Password',  
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 8,
                    ]),
                    
                    new Regex([
                        'pattern'=>'/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
                        'message'=>'Your password should be at least 6 characters:upper lower and number'
                    
                ]),
                ],
            ])
            // ->add('captcha', Recaptcha3Type::class, [
            //     'constraints' => new Recaptcha3(),
            //     'action_name' => 'homepage',
                
            // ])
            // ->add('captcha', TextType::class,[
            //     'mapped' => false,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
