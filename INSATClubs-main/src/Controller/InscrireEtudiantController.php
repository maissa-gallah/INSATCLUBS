<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\User;
use App\Security\UserAuthentificatorAuthenticator;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use  Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;


/**
 * Class InscrireEtudiantController
 * @package App\Controller
 */

/**
/**
 * @Route("/etudiant", name="etudiant")
 */

class InscrireEtudiantController extends AbstractController
{
    /**
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY') and not is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/inscrire", name="inscrire")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param FileUploader $fileUploader
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function inscrire(Request $request,UserPasswordEncoderInterface $passwordEncoder,FileUploader $fileUploader)
    {
        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])]
                ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])]
            ])
            ->add('dataDeNaissance', DateType::class,
                [ 'widget' => 'choice',
                    'input'  => 'datetime_immutable',
                    'years'=>range(1980,2015)]

            )
            ->add('NumeroDeLaCarteEtudiant', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Vérifiez que vous avez bien retappé le mot de passe.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 7])]
            ])
            ->add('imageEmp',FileType::class, array(
                'required' => false,
                'mapped'=>false
            ))
            ->add('Annuler', ResetType::class, [
                'attr' => ['class' => 'btn btn-outline-dark float-right']])

            ->add('captcha',\MonCaptcha\RecaptchaBundle\Type\RecaptchaSubmitType::class,
                ['label'=>'Enregistrer[link][/link]'])
            ->add('recaptcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'homepage',
            ])
            ->getForm();


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $data=$form->getData();
            $etudiant= new Etudiant();
            $etudiant->setNom($data['nom']);
            $user= new User();
            $user->setPassword($passwordEncoder->encodePassword($user,$data['password'])
            );
            $user->setEmail($data['email']);
            $user->setRoles(array('ROLE_ETUDIANT'));
            $etudiant->setDatenaissance($data['dataDeNaissance']);
            $etudiant->setPrenom($data['prenom']);
            $etudiant->setNumCarteEtudiant($data['NumeroDeLaCarteEtudiant']);
            /** @var UploadedFile $file */
            $file=$form['imageEmp']->getData();
            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $etudiant->setImageEmp($filename);
            }
            $user->setEtudiant($etudiant);
            $etudiant->getUser()->setIsDeleted(false);
            $em=$this->getDoctrine()->getManager();
            $em->persist($etudiant);
            $em->persist($user);
            $em->flush();
            return $this->redirect($this->generateUrl('app_login'));
        }

        return $this->render('inscrire_etudiant/index.html.twig',
            ['form' =>  $form->createView()]);
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/delete", name="delete")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserAuthentificatorAuthenticator $authenticator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function desinscrire(Request $request, EntityManagerInterface $em, UserAuthentificatorAuthenticator $authenticator)
    {
        $user = $this->getUser();
        $deleteUserForm = $this->createFormBuilder()
            ->add('password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Vérifiez que vous avez bien  votre mot de passe.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Retapez votre mot de passe']
                ])
            ->getForm();
        $deleteUserForm->handleRequest($request);

        if ($deleteUserForm->isSubmitted() && $deleteUserForm->isValid()) {
            if ($authenticator->checkCredentials($deleteUserForm->getData(),$user))
            {
                $this->get('security.token_storage')->setToken(null);
                $comments=$user->getEtudiant()->getComment();
                foreach ($comments as $cm) {
                     $em->remove($cm);
               }
                $notif=$user->getNotif();
                foreach ($notif as $not) {
                    $em->remove($not);
                }

                $em->remove($user->getEtudiant());
                $em->remove($user);
                $em->persist($user);
                $em->flush();
                return $this->redirect($this->generateUrl('home'));
            }
        }

        return $this->render('inscrire_etudiant/delete.html.twig',
            ['form' =>  $deleteUserForm->createView()]);
    }

}
