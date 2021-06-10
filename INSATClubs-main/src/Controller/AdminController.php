<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Etudiant;
use App\Entity\Event;
use App\Entity\Sponsor;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\EtudiantRepository;
use App\Repository\EventRepository;
use App\Repository\SponsorRepository;
use App\Repository\UserRepository;
use App\Services\NotificationMaker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/theadminpage", name="admin_page")
 */
    public function adminPage(Request $request,NotificationMaker $maker, NotifierInterface $notifier,UserRepository $userRepository, EventRepository $eventRepository,ClubRepository $clubRepository, EtudiantRepository $etudiantRepository){
        $en=$this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->add('to',ChoiceType::class,[
                'choices'  => [
                    'Etudiant' => 'etudiant',
                    'Club' => 'club',
                    'All Users'=>"users",
                    "All Clubs" =>"clubs",
                    "All Etudiants" => 'etudiants'
                ]
            ])
            ->add('id', IntegerType::class,[
                'required' => false
            ])
            ->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class)
            ->add('content', \Symfony\Component\Form\Extension\Core\Type\TextType::class)
            ->add('send',SubmitType::class ,[
                'attr' => [
                    'class' => 'btn btn-primary float-right'
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $to = $data['to'];
            switch ($to) {
                case "etudiant":
                    $utilisateur= $etudiantRepository->find($data['id']);
                    $maker->sendNotif2($utilisateur, $en, $notifier,"[ADMIN]". $data['title'],$data['content'] , 0);
                    break;
                case "club":
                    $utilisateur= $clubRepository->find($data['id']);
                    $maker->sendNotif2($utilisateur, $en, $notifier,"[ADMIN]". $data['title'],$data['content'] , 0);
                    break;
                case "etudiants":
                    $utilisateur= $etudiantRepository->findAll();
                    $maker->sendNotif1($utilisateur, $en, $notifier,"[ADMIN]".  $data['title'], $data['content'] , 0);
                    break;
                case "clubs":
                    $utilisateur= $clubRepository->findAll();
                    $maker->sendNotif1($utilisateur, $en, $notifier, "[ADMIN]". $data['title'], $data['content'] , 0);
                    break;
                case "users":
                    $utilisateur= $userRepository->findAll() ;
                    $maker->sendNotif3($utilisateur, $en, $notifier, "[ADMIN]". $data['title'], $data['content'] );
                    break;
            }
        }

        return $this->render('admin/index.html.twig',[
            'form'=> $form->createView()
        ]);
    }



    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/showListeEtudiants", name="Etudiants")
     */
    public function Etudiants(EtudiantRepository $etudiantRepository,Request $request)
    { $formView = null;
        $form = $this->createFormBuilder()
            ->add("search")
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-outline-dark '
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $search = $data['search'];
            $etudiants = $etudiantRepository->searchByKeyword($search);
        } else
        {

        $repo=$this->getDoctrine()->getRepository(Etudiant::class);
    $etudiants=$repo->findAll();}
        $formView = $form->createView();

        return $this->render('admin/showEtudiants.html.twig', [
            'etudiants' => $etudiants,
            'form'=>$formView
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/showListeClubs", name="Clubs")
     */
    public function Clubs(ClubRepository $clubRepository,Request $request)
    {$formView = null;
        $form = $this->createFormBuilder()
            ->add("search")
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-outline-dark '
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $search = $data['search'];
            $clubs = $clubRepository->searchByKeyword($search);
        } else
        {
        $repo=$this->getDoctrine()->getRepository(Club::class);
        $clubs=$repo->findAll();}
        $formView = $form->createView();
        return $this->render('admin/showClubs.html.twig', [
            'clubs' => $clubs,
            'form'=>$formView
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/showListeEvents", name="Events")
     */
    public function Events(EventRepository $eventRepository,Request $request)
    {$formView = null;
        $form = $this->createFormBuilder()
            ->add("search")
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-outline-dark '
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $search = $data['search'];
            $events = $eventRepository->searchByKeyword($search);
        } else
        {

        $repo=$this->getDoctrine()->getRepository(Event::class);
        $events=$repo->findAll();}
        $formView=$form->createView();
        return $this->render('admin/showEvents.html.twig', [
            'events' => $events,
            'form'=>$formView
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/showListeSponsors", name="Sponsors")
     */
    public function Sponsors(SponsorRepository $sponsorRepository,Request $request)
    { $formView = null;
        $form = $this->createFormBuilder()
            ->add("search")
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-outline-dark '
                ]
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $search = $data['search'];
            $sponsors = $sponsorRepository->searchByKeyword($search);
        } else
        {$repo=$this->getDoctrine()->getRepository(Sponsor::class);
        $sponsors=$repo->findAll();}
        $formView=$form->createView();
        return $this->render('admin/showSponsors.html.twig', [
            'sponsors' => $sponsors,
            'form'=>$formView
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/removeSponsor/{id}", name="sponsor.delete")
     * @param $id
     * @param SponsorRepository $sponsorRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function DeleteSponsors($id,SponsorRepository $sponsorRepository)
    {  $sponsor=$sponsorRepository->find($id);
       $en = $this->getDoctrine()->getManager();
        $en->remove($sponsor);
        $en->flush();
        return $this->redirect($this->generateUrl('Sponsors'));
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/removeEtudiant/{id}", name="etudiant.supprimer")
     * @param $id
     * @param EtudiantRepository $etudiantRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function DeleteEtudiants($id,EtudiantRepository $etudiantRepository)
    {  $etudiant=$etudiantRepository->find($id);
        $em = $this->getDoctrine()->getManager();
        $comments=$etudiant->getComment();
        foreach ($comments as $cm) {
            $em->remove($cm);
        }
        $notif=$etudiant->getUser()->getNotif();
        foreach ($notif as $not) {
            $em->remove($not);
        }
        $user=$etudiant->getUser();
        $em->remove($etudiant);
        $em->remove($user);
        $em->flush();
        return $this->redirect($this->generateUrl('Etudiants'));
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/removeDelete/{id}", name="club.supprimer")
     * @param $id
     * @param ClubRepository $clubRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function DeleteClubs($id,ClubRepository $clubRepository)
    {  $club=$clubRepository->find($id);
        $em = $this->getDoctrine()->getManager();
        $event=$club->getEvent();
        foreach($event as $ev)
        { $comment=$ev->getComment();
            foreach($comment as $com)
            {
                $em->remove($com);}
            $em->remove($ev);
        }
        $notif=$club->getUser()->getNotif();
        foreach ($notif as $not) {
            $em->remove($not);
        }
        $user=$club->getUser();
        $em->remove($club);
        $em->remove($user);
        $em->flush();
        return $this->redirect($this->generateUrl('Clubs'));
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/dashboard/etudiant", name="dashboard.etudiant")
     */
    public function DashboardEtudiants()
    {$repo=$this->getDoctrine()->getRepository(Etudiant::class);
     $etudiants=$repo->findAll();
     return  $this->render('admin/etudiantsDash.html.twig', [
         'etudiants' => $etudiants
     ]);


    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/dashboard/user", name="dashboard.user")
     */
    public function DashboardUsers()
    {$repo=$this->getDoctrine()->getRepository(User::class);
        $users=$repo->findAll();
        $repo=$this->getDoctrine()->getRepository(Club::class);
        $clubs=$repo->findAll();
        $repo=$this->getDoctrine()->getRepository(Etudiant::class);
        $etudiants=$repo->findAll();
        $repo=$this->getDoctrine()->getRepository(Sponsor::class);
        $sponsors=$repo->findAll();
        return  $this->render('admin/usersDash.html.twig', [
            'users' => $users,
            'sponsors'=>$sponsors,
            'etudiants'=>$etudiants,
            'clubs'=>$clubs
        ]);


    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/dashboard/club", name="dashboard.club")
     */
    public function DashboardClub()
    {$repo=$this->getDoctrine()->getRepository(Club::class);
        $clubs=$repo->findAll();
        return  $this->render('admin/clubsDash.html.twig', [
            'clubs' => $clubs
        ]);


    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/dashboard/event", name="dashboard.event")
     */
    public function DashboardEvent()
    {$repo=$this->getDoctrine()->getRepository(Event::class);
        $events=$repo->findAll();
        return  $this->render('admin/eventsDash.html.twig', [
            'events' => $events
        ]);


    }

}
