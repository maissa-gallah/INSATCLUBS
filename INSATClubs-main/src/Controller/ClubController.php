<?php /** @noinspection ALL */

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\User;
use App\Security\UserAuthentificatorAuthenticator;
use App\Services\FileUploader;
use Doctrine\DBAL\Types\TextType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Club;
use App\Repository\ClubRepository;
use App\Repository\EtudiantRepository;
use App\Form\ClubType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ClubController extends AbstractController
{

    /**
     * @Route("/club",name="club")
     */
    public function home(Request $request){
        $formView = null;
        $form = $this->createFormBuilder()
            ->add("search")
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-dark '
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $search = $data['search'];
            $clubs = $this->getDoctrine()->getRepository(Club::class)->searchByKeyword($search);
            $formView = $form->createView();
            return $this->render('blog/domaine.html.twig', [
                'clubs' => $clubs,
                'form' => $formView
            ]);
        }
        $formView = $form->createView();
        return $this->render('blog/home.html.twig',[
            'title'=>"InsatClub",
            'form' => $formView
        ]);
    }
    /**
     * @Route("/club/domaine/{domaine}",name="club_domaine")
     */
    public function showDomaine($domaine){
        $repo=$this->getDoctrine()->getRepository(Club::class);
        $clubs= $repo->findBy(["domaine"=>$domaine]);
        return $this->render('blog/domaine.html.twig',['clubs'=>$clubs]);
    }
    /**
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY') and not is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/club/new",name="club_create")
     */
    public function form(Request $request,Club $club= null,FileUploader $fileUploader,UserPasswordEncoderInterface $passwordEncoder){
        if(!$club) {
            $club = new Club();
            $form = $this->createFormBuilder()
                ->add('nom')
                ->add('domaine', choiceType::class, ['choices' => ['artistique' => 'artistique', 'differentsDomaines' => 'differentDomaine',
                    'automatique' => 'automatique', 'robotique' => 'robotique', 'informatique' => 'informatique', 'volontaire' => 'volontaire', 'chimie' => 'chimie',
                    'press' => 'press', 'annimation' => 'annimation']])
                ->add('detail')
                ->add('description')
                ->add('email', EmailType::class)
                ->add('imageEmp', FileType::class, array(
                    'required' => false,
                    'mapped' => false
                ))
                ->add('password', RepeatedType::class,
                    [
                        'type' => PasswordType::class,
                        'invalid_message' => 'Vérifiez que vous avez bien  votre mot de passe.',
                        'options' => ['attr' => ['class' => 'password-field']],
                        'required' => true,
                        'first_options' => ['label' => 'Password'],
                        'second_options' => ['label' => 'Retapez votre mot de passe']
                    ])
                ->add('Annuler', ResetType::class, [
                    'attr' => ['class' => 'btn btn-outline-dark float-right']])

                ->add('captcha',\MonCaptcha\RecaptchaBundle\Type\RecaptchaSubmitType::class,
                    ['label'=>'Enregistrer[link][/link]'])
                ->add('recaptchaa', Recaptcha3Type::class, [
                    'constraints' => new Recaptcha3(),
                    'action_name' => 'homepage',
                ])
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $user = new User();
                $user->setPassword($passwordEncoder->encodePassword($user, $data['password'])
                );
                $user->setEmail($data['email']);
                $user->setRoles(['ROLE_CLUB']);
                $club = new Club();
                $club->setNom($data['nom']);
                $club->setDescription($data['description']);
                $club->setDetail($data['detail']);
                $club->setDomaine($data['domaine']);

                /** @var UploadedFile $file */
                $file = $form['imageEmp']->getData();
                if ($file) {
                    $filename = $fileUploader->uploadFile($file);
                    $club->setImageEmp($filename);
                }
                $user->setClub($club);
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->persist($club);
                $manager->flush();
                return $this->redirect($this->generateUrl('app_login'));
            }
        }
        return $this->render('blog/create.html.twig',['formClub'=>$form->createView(),'editMode'=>$club->getId()!==null]);
    }
    /**
     * @Route("/club/show/{id}",name="club_show")
     */
    public function show($id){
        $bool=false;
        $repo=$this->getDoctrine()->getRepository(Club::class);
        $club= $repo->find($id);
        $user = $club->getUser();
        if($this->isGranted('ROLE_ETUDIANT'))
        {$etudiant=$this->getUser()->getEtudiant();
            $clubs=$etudiant->getClub();
            foreach ($clubs as $clubb)
            { if($clubb ===$club)
            {
                $bool=true;
            }

          }

        }
        return $this->render('blog/show.html.twig',['club'=>$club,'user'=>$user,'bool'=>$bool
                        ]);
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/club/edit",name="club_edit")
     */
    public function formEdit(Request $request,FileUploader $fileUploader,UserPasswordEncoderInterface $passwordEncoder)
    {
        $user=$this->getUser();
        $club=$user->getClub();
        $d=null;
        $d["nom"]=$club->getNom();
        $d["detail"]=$club->getDetail();
        $d["description"]=$club->getDescription();
        $d["imageEmp"]=$club->getImageEmp();
        $d["domaine"]=$club->getDomaine();
        $d["email"]=$user->getEmail();
        $d["password"]=$user->getPassword();
        $form = $this->createFormBuilder($d)
            ->add('nom')
            ->add('domaine', choiceType::class, ['choices' => ['artistique' => 'artistique', 'differentsDomaines' => 'differentDomaine',
                'automatique' => 'automatique', 'robotique' => 'robotique', 'informatique' => 'informatique', 'volontaire' => 'volontaire', 'chimie' => 'chimie',
                'press' => 'press', 'annimation' => 'annimation']])
            ->add('detail')
            ->add('description')
            ->add('email', EmailType::class)
            ->add('imageEmp', FileType::class, array(
                'required' => false,
                'mapped' => false
            ))
            ->add('password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Vérifiez que vous avez bien  votre mot de passe.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options' => ['label' => 'ajouter votre (nouveau) mot de passe '],
                    'second_options' => ['label' => 'Retapez votre (nouveau) mot de passe']
                ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword($user, $data['password'])
            );
            $user->setEmail($data['email']);
            $user->setRoles(array('ROLE_CLUB'));
            $club->setNom($data['nom']);
            $club->setDescription($data['description']);
            $club->setDetail($data['detail']);
            $club->setDomaine($data['domaine']);

            /** @var UploadedFile $file */
            $file = $form['imageEmp']->getData();
            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $club->setImageEmp($filename);
            }
            $user->setClub($club);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->persist($club);
            $manager->flush();
            $this->get('security.token_storage')->setToken(null);
            return $this->redirect($this->generateUrl('app_login'));
        }
        return $this->render('blog/edit.html.twig',[
            'formClub'=>$form->createView()
        ]);
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/club/delete", name="delete")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserAuthentificatorAuthenticator $authenticator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteUserAction(Request $request, EntityManagerInterface $em, UserAuthentificatorAuthenticator $authenticator)
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
                $notif=$user->getNotif();
                foreach ($notif as $not) {
                    $em->remove($not);
                }
                $event=$user->getClub()->getEvent();
                foreach($event as $ev)
                { $comment=$ev->getComment();
                    foreach($comment as $com)
                    {
                    $em->remove($com);}
                    $em->remove($ev);
                }
                $em->remove($user->getClub());
                $em->remove($user);
                $em->flush();
                return $this->redirect($this->generateUrl('home'));
            }
        }
        return $this->render('blog/delete.html.twig',
            ['form' =>  $deleteUserForm->createView()]);
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/club/myevents", name="club_myevents")
     */
    public function showMyEvents(){
        $club=$this->getUser()->getClub();
        $events=$club->getEvent();
        if($club->getEvent()->isEmpty())
        {$this->addFlash('danger',"Vous n'avez pas des événements");}
        return $this->render('blog/showMyEvents.html.twig',[
            'events' => $events
        ]);
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/club/subscribers",name="club_subscribers")
     */
    public function showMesabonnés()
    {
        $user = $this->getUser();
        $club = $user->getClub();
        $etudiants = $club->getEtudiant();
        if($club->getEtudiant()->isEmpty())
        {
            $this->addFlash('danger',"Vous n'avez pas des abonnés");
        }
        return $this->render('blog/showMySubscribers.html.twig', [
            'etudiants' => $etudiants]);
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/unsubscribe/club/etudiant/{id}", name="unsubscribeClub_etudiant")
     * @param $id
     * @param EtudiantRepository $etudiantRepository
     * @return RedirectResponse
     */
    public function unsubscribeclubetudiant($id,EtudiantRepository $etudiantRepository)
    {
        $etudiant =$etudiantRepository->find($id);
        $club=$this->getUser()->getClub();
        $en = $this->getDoctrine()->getManager();
        $etudiant->removeClub($club);
        $en->flush();
        $this->addFlash('danger', 'un abonnée est effacé!');
        return $this->redirect($this->generateUrl('club_subscribers'));
    }


}