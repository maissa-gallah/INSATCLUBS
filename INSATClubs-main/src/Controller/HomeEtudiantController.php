<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use App\Repository\ClubRepository;
use App\Services\FileUploader;
use App\Services\NotificationMaker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
/**
 * @Route("/etudiant", name="etudiant_")
 */
class HomeEtudiantController extends AbstractController
{
    /**
     * @Route("/show/{id}", name="show")
     * @param $id
     * @param EtudiantRepository $etudiantRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home($id,EtudiantRepository $etudiantRepository)
    {
        $etudiant =$etudiantRepository->find($id);
        $user=$etudiant->getUser();
        $nom=$etudiant->getNom();
        $prenom=$etudiant->getPrenom();
        $date=$etudiant->getDatenaissance();
        $email=$user->getEmail();
        $password=$user->getPassword();
        $num=$etudiant->getNumCarteEtudiant();
        $img=$etudiant->getImageEmp();
        return $this->render('home_etudiant/home.html.twig',
            ['nom' =>$nom,
                'prenom'=>$prenom,
                'email'=>$email,
                'password'=>$password,
                'num'=>$num,
                'date'=>$date->format('Y-m-d'),
                'img'=>$img
            ]);
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/edit",name="edit")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function EditEtudiant(Request $request,FileUploader $fileUploader,UserPasswordEncoderInterface $passwordEncoder)
    { 
        $user=$this->getUser();
        $etudiant=$user->getEtudiant();
        $aux['nom']=$etudiant->getNom();
        $aux['prenom']=$etudiant->getPrenom();
        $aux['date']=$etudiant->getDatenaissance();
        $aux['email']=$user->getEmail();
        $aux['password']=$user->getPassword();
        $aux['num']=$etudiant->getNumCarteEtudiant();
        $form = $this->createFormBuilder($aux)
            ->add('nom',TextType::class)
            ->add('prenom',TextType::class)
            ->add('email', EmailType::class)
            ->add('date',DateType::class)
            ->add('num',\Symfony\Component\Form\Extension\Core\Type\IntegerType::class)
            ->add('password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Vérifiez que vous avez bien  votre mot de passe.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options' => ['label' => 'ajouter votre (nouveau) mot de passe '],
                    'second_options' => ['label' => 'Retapez votre (nouveau) mot de passe']
                ])

            ->add('imageEmp', FileType::class, array(
                'required' => false,
                'mapped' => false
            ))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // $user = new User();
            $user->setPassword($passwordEncoder->encodePassword($user,$data['password'])
            );
            $user->setEmail($data['email']);
            //$club = new Club();
            $etudiant->setNom($data['nom']);
            $etudiant->setPrenom($data['prenom']);
            $etudiant->setDatenaissance($data['date']);
            $etudiant->setNumCarteEtudiant($data['num']);
            /** @var UploadedFile $file */
            $file = $form['imageEmp']->getData();
            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $etudiant->setImageEmp($filename);
            }
            $user->setEtudiant($etudiant);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->persist($etudiant);
            $manager->flush();
            $this->get('security.token_storage')->setToken(null);
            return $this->redirect($this->generateUrl('app_login'));
        }
        //}
        return $this->render('home_etudiant/modifier.html.twig',[
            'form'=>$form->createView()//,
        ]);
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/myevents", name="myevents")
     */
    public function showMyEvents(){
        $etudiant=$this->getUser()->getEtudiant();
        $events=$etudiant->getEvent();
        return $this->render('home_etudiant/showMyEvents.html.twig',[
            'events' => $events
        ]);
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/subscribe/club/{id}", name="subscribe")
     * @param $id
     * @param ClubRepository $clubRepository
     */
    public function subscribeClub($id, ClubRepository $clubRepository,NotifierInterface $notifier, NotificationMaker $maker){
        $user=$this->getUser();
        $etudiant=$user->getEtudiant();
        $club=$clubRepository->find($id);
        $en = $this->getDoctrine()->getManager();
        $club->addEtudiant($etudiant);
        $en->persist($etudiant);
        $en->persist($club);
        $en->flush();
        $etudiantName = $etudiant->getNom() ." " . $etudiant->getPrenom();
        $maker->sendNotif2($club, $en, $notifier, "Un nouvel abonné ", "Vous avez un nouvel abonné " . $etudiantName . "." , 0);
//        return $this->redirect($this->generateUrl('club_show', [
//            'id' => $id
//        ]));
        return new Response();
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/unsubscribe/club/{id}", name="unsubscribe")
     * @param $id
     * @param ClubRepository $clubRepository
     * @return RedirectResponse
     */
    public function unsubscribeclub($id,ClubRepository $clubRepository)
    {
        $club =$clubRepository->find($id);
        $etudiant=$this->getUser()->getEtudiant();
        $en = $this->getDoctrine()->getManager();
        $club->removeEtudiant($etudiant);
        $en->flush();
        $this->addFlash('danger', 'Club effacé!');
        return $this->redirect($this->generateUrl('etudiant_myclubs', [
            'id' => $id
        ]));
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/unsubscribeme/club/{id}", name="unsubscribeme")
     * @param $id
     * @param ClubRepository $clubRepository
     */
    public function unsubscribemeclub($id,ClubRepository $clubRepository)
    {
        $club =$clubRepository->find($id);
        $etudiant=$this->getUser()->getEtudiant();
        $en = $this->getDoctrine()->getManager();
        $club->removeEtudiant($etudiant);
        $en->flush();
        $this->addFlash('danger', 'Club effacé!');
//        return $this->redirect($this->generateUrl('club_show', [
//            'id' => $id
//        ]));
        return new Response();
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/myclubs", name="myclubs")
     */
    public function showMyClubs(){
        $etudiant=$this->getUser()->getEtudiant();
        $clubs=$etudiant->getClub();
        return $this->render('home_etudiant/showMyClubs.html.twig',[
            'clubs' =>$clubs
        ]);
    }

}