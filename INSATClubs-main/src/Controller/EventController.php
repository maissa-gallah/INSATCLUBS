<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\Sponsor;
use App\Entity\User;
use App\Entity\Demandeur;
use App\Form\CommentType;
use App\Form\EventType;
use App\Form\SponsorType;
use App\Repository\DemandeurRepository;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use App\Services\FileUploader;
use App\Services\NotificationMaker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @Route("/event", name="event.")
*/
class EventController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param EventRepository $eventRepository
     * @param Request $request
     * @return Response
     */

    public function index(EventRepository $eventRepository,Request $request)
    {
        $page = $request->get("page") ?? 1;
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
            $nbPages = 1;
            $data = $form->getData();
            $search = $data['search'];
            $events = $eventRepository->searchByKeyword($search);
        } else {
            $nbEnregistrements = $eventRepository->count(array());
            $nbPages = ($nbEnregistrements % 10 ) ? ($nbEnregistrements / 10) +1 : ($nbEnregistrements /10)  ;
            $events = $eventRepository->findBy(array(), array('start_time' => 'DESC'), 10,($page -1) *10);
        }
        $formView = $form->createView();
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'form' => $formView,
            'nbPage' =>$nbPages
        ]);
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/subscribe/{id}", name="subscribe")
     * @param $id
     * @param EventRepository $eventRepository
     */
    public function subscribe($id,EventRepository $eventRepository,NotifierInterface $notifier, NotificationMaker $maker){
        $user=$this->getUser();
        $etudiant=$user->getEtudiant();
        $event=$eventRepository->find($id);
        $access=$event->getAccess();
        $en = $this->getDoctrine()->getManager();
        if($access == "Public"){
            $etudiant->addEvent($event);
            $en->flush();
            $maker->sendNotif2($event->getClub(), $en, $notifier, "Un nouvel abonné ", "Votre événement " . $event->getTitle() . " a un nouvel abboné " . $etudiant->getNom() . " " . $etudiant->getPrenom() . "." , $event->getId());
        }

        else {
            if (!$etudiant->getDemandeur())   {
                $demandeur=new Demandeur();

                $etudiant->setDemandeur($demandeur);}
            else{ $demandeur=$etudiant->getDemandeur();}
            /*$demandeur= $etudiant->getDemandeur();*/
            $event->addDemandeur($demandeur);
            $en->persist($demandeur);
            $en->flush();
            $maker->sendNotif2($event->getClub(), $en, $notifier, "Une nouvelle demande ",   "Vous avez une nouvelle demande de " . $etudiant->getNom()." ". $etudiant->getPrenom() . " à l'evenement " . $event->getTitle() .".", $event->getId());
        }
//        return $this->redirect($this->generateUrl('event.show', [
//            'id' => $id
//        ]));
        return new Response();
    }
    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/unsubscribeme/{id}", name="unsubscribeme")
     */
    public function unsubscribeme($id,EventRepository $eventRepository)
    {
        $event = $eventRepository->find($id);
        $etudiant=$this->getUser()->getEtudiant();
        $en = $this->getDoctrine()->getManager();
        $etudiant->removeEvent($event);
        $en->flush();
        $this->addFlash('success', 'Evénement effacé');
//        return $this->redirect($this->generateUrl('event.show', [
//            'id' => $id
//        ]));
        return new Response();
    }


    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/mydemandeurs/delete/{id}/{id1}", name="delete.demandeur")
     * @param $id
     * @param $id1
     * @param DemandeurRepository $demandeurRepository
     * @param EventRepository $eventRepository
     * @return RedirectResponse
     */

    public function refuser($id,$id1,DemandeurRepository $demandeurRepository,EventRepository $eventRepository){

        $demandeur= $demandeurRepository->find($id);
        $event=$eventRepository->find($id1);
        if($event->getClub()==$this->getUser()->getClub())
        {$en = $this->getDoctrine()->getManager();
            $event->removeDemandeur($demandeur);
            $en->flush();
            $this->addFlash('success', 'demandeur effacé');
        }
        return $this->redirect($this->generateUrl('event.mydemandeurs',['id'=>$id1 ] ));
    }
    /**
     * @IsGranted("ROLE_CLUB")
     *@Route("/mydemandeurs/accepte/{id}/{id1}", name="accepte.demandeur")
     * @param $id
     * @param $id1
     *  @param DemandeurRepository $demandeurRepository
     * @param EventRepository $eventRepository
     *@return RedirectResponse
     */
    public function accepter($id,$id1,DemandeurRepository $demandeurRepository,EventRepository $eventRepository,NotifierInterface $notifier, NotificationMaker $maker)
    {
        $demandeur= $demandeurRepository->find($id);
        $etudiant=$demandeur->getEtudiant();
        $event=$eventRepository->find($id1);
        $en = $this->getDoctrine()->getManager();
        $etudiant->addEvent($event);
        $event->removeDemandeur($demandeur);
        $en->flush();
        $maker->sendNotif2($etudiant, $en, $notifier, "Votre demande a été acceptée",   "Votre demande d'inscription à l'événement " . $event->getTitle() . " a été acceptée par ".$event->getClub()->getNom() .".", $event->getId());
        $this->addFlash('success', 'demandeur accepté');
        return $this->redirect($this->generateUrl('event.mydemandeurs',['id'=>$id1 ] ));

    }

    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/mydemandeurs/{id}", name="mydemandeurs")
     */
    public function showDemandeurs($id,EventRepository $eventRepository){
        $event=$eventRepository->find($id);
        if($event->getClub()=== $this->getUser()->getClub()) {
            $demandeurs = $event->getDemandeur();
            return $this->render('event/showMyDemandeurs.html.twig', [
                'demandeurs' => $demandeurs,
                'event'=>$event
            ]);
        }else{
            return $this->redirect($this->generateUrl('event.show', [
                'id' => $id
            ]));
        }
    }

    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/create", name="create")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param NotificationMaker $maker
     * @param NotifierInterface $notifier
     * @return RedirectResponse|Response
     */
    public function create(Request $request, FileUploader $fileUploader, NotificationMaker $maker, NotifierInterface $notifier)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        $form->getErrors();
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $request->files->get('event')['image'];
            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $event->setImage($filename);
            }
            $club=$this->getUser()->getClub();
            $club->addEvent($event);
            $en = $this->getDoctrine()->getManager();
            $en->persist($event);
            $en->flush();
            $this->addFlash('success', 'Evénement ajouté');
            $etudiants=$club->getEtudiant();
            $maker->sendNotif1($etudiants, $en, $notifier, "Un nouveau événement de " . $club->getNom(),"le club " . $club->getNom() . " a ajouter un neauveau événement " . $event->getTitle(), $event->getId());
            return $this->redirect($this->generateUrl("club_myevents"));
        }
        return $this->render('event/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param $id
     * @param EventRepository $eventRepository
     * @param Request $request
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function show($id, EventRepository $eventRepository, CommentRepository $commentRepository, Request $request,NotificationMaker $maker, NotifierInterface $notifier)
    {  $bool=false;
       $test=false;
        $event = $eventRepository->find($id);
        $commentFormView = null; //in case the form is completed
        if( $this->isGranted('ROLE_ETUDIANT') ) {
            /** @var User $user */
            $user = $this->getUser();
            $etudiant = $user->getEtudiant();
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);
            $commentForm->getErrors();
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $event->addComment($comment);
                $etudiant->addComment($comment);
                $en = $this->getDoctrine()->getManager();
                $en->persist($comment);
                $en->flush();
                $this->addFlash('success', 'Commentaire ajouté');
                $maker->sendNotif2($comment->getEvent()->getClub(), $en, $notifier, "Un nouveau commentaire",  "Votre événement " . $event->getTitle() . " a un nouveau commentaire de " . $etudiant->getNom() . $etudiant->getPrenom() . "." ,  $event->getId());
                // to reinitialize the form
                $comment = new Comment();
                $commentForm = $this->createForm(CommentType::class, $comment);
            }
            $commentFormView = $commentForm->createView();
            foreach ($etudiant->getEvent() as $events)
            {if($events->getId()===$event->getId())
            { $bool=true;}

            }
            $demand=$event->getDemandeur();
            foreach ($demand as $demandeur)
            {if
            ($demandeur->getEtudiant()===$etudiant)
                {$test=true;}

            }

        }
        //to show all comments for this particular event
        $comments = $commentRepository->findAllByEvent($id);
        //making the form for the sponsoring option
        $sponsor = new Sponsor();
        $event->addSponsor($sponsor);
        $sponsorFormView = null;
        $sponsorForm = $this->createForm(SponsorType::class, $sponsor);
        $sponsorForm->handleRequest($request);
        $sponsorForm->getErrors();
        if ($sponsorForm->isSubmitted() && $sponsorForm->isValid()) {
            $event->addSponsor($sponsor);
            $en = $this->getDoctrine()->getManager();
            $en->persist($sponsor);
            $en->flush();
            $maker->sendNotif2($event->getClub(), $en, $notifier, "Un nouveau sponsor", "Votre événement " . $event->getTitle() . " a un nouveau sponsor " . $sponsor->getNameEntreprise() . "." , $event->getId());
            // to reinitialize the form
            $sponsor = new Sponsor();
            $sponsorForm = $this->createForm(SponsorType::class, $sponsor);
        }
        $sponsorFormView = $sponsorForm->createView();
        return $this->render("event/show.html.twig", [
            'sponsorForm' => $sponsorFormView,
            'event' => $event,
            'commentForm'=>$commentFormView,
            'comments' => $comments,
            'bool'=>$bool,
            'test'=>$test
        ]);
        return new JsonResponse();
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/update/{id}", name="update")
     * @param $id
     * @param EventRepository $eventRepository
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return RedirectResponse|Response
     */
    public function update($id, EventRepository $eventRepository, Request $request, FileUploader $fileUploader,NotifierInterface $notifier, NotificationMaker $maker)
    {
        $event = $eventRepository->find($id);
        if ($event->getClub() === $this->getUser()->getClub()) {
            $event->setDescription($event->getDescription());
            $event->setTitle($event->getTitle());
            $event->setCategory($event->getCategory());
            $event->setPlace($event->getPlace());
            $event->setStartTime($event->getStartTime());
            $event->setEndTime($event->getEndTime());
            $event->setAccess($event->getAccess());
            $event->setImage($event->getImage());
            $form = $this->createForm(EventType::class, $event);
            if ($event->getImage()) {
                $fileorg = new File($this->getParameter('uploads_dir') . $event->getImage());
                $form->get('image')->setData($fileorg);
            }
            $form->handleRequest($request);
            $form->getErrors();
            if ($form->isSubmitted() && $form->isValid()) {
                $en = $this->getDoctrine()->getManager();
                /** @var UploadedFile $file */
                $file = $request->files->get('event')['image'];
                if ($file) {
                    $filename = $fileUploader->uploadFile($file);
                    $event->setImage($filename);
                }
                $en->persist($event);
                $en->flush();
                $etudiants=$event->getEtudiant();
                $maker->sendNotif1($etudiants, $en, $notifier,  "Mise à jour de l'événement " . $event->getTitle(), "l'événement " . $event->getTitle() . " a été modifié.", $event->getId());
                return $this->redirect($this->generateUrl("club_myevents"));

            }
            return $this->render('event/update.html.twig', [
                "form" => $form->createView()
            ]);
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet événement');
            return $this->redirect($this->generateUrl('event.show', [
                'id' => $id
            ]));
        }
    }
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_CLUB')")
     * @Route("/delete/{id}", name="delete")
     * @param $id
     * @param EventRepository $eventRepository
     * @return RedirectResponse
     */
    public function remove($id, EventRepository $eventRepository,NotifierInterface $notifier, NotificationMaker $maker)
    {
        $event = $eventRepository->find($id);
        $roles=$this->getUser()->getRoles();
        $bool=false;
        foreach ($roles as $role)
        {
            if($role==="ROLE_ADMIN")
            {$bool=true;}
        }
        if ($event->getClub() === $this->getUser()->getClub() || $bool===true) {
            $en = $this->getDoctrine()->getManager();
            $etudiants = $event->getEtudiant();
            $maker->sendNotif1($etudiants, $en, $notifier, "Suppression de l'événement " . $event->getTitle(), "l'événement " . $event->getTitle() . " a été supprimé." , $event->getId());
            $comment=$event->getComment();
            foreach ($comment as $comm) {
                $en->remove($comm);
            }
            $en->remove($event);
            $en->flush();
            $this->addFlash('success', 'Evénement effacé');
            if($bool===true)

            {return $this->redirect($this->generateUrl("Events"));}

         else{   return $this->redirect($this->generateUrl("club_myevents"));}
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer cet événement');
            return $this->redirect($this->generateUrl('event.show', [
                'id' => $id
            ]));
        }
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/deletecomment/{id}", name="deletecomment")
     * @param $id
     * @param CommentRepository $commentRepository
     * @return RedirectResponse
     */
    public function removeComment($id, CommentRepository $commentRepository)
    {
        $comment = $commentRepository->find($id);
        if ($comment->getEtudiant() === $this->getUser()->getEtudiant()|| $comment->getEvent()->getClub()=== $this->getUser()->getClub()) {
            $en = $this->getDoctrine()->getManager();
            $en->remove($comment);
            $en->flush();

//        return $this->redirect($this->generateUrl('event.show', [
//            'id' => $comment->getEvent()->getId()
//        ]));
        return new JsonResponse(['idd'=>$id]);
    }}

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/updatecomment/{id}", name="updatecomment")
     * @param $id
     * @param CommentRepository $commentRepository
     * @param Request $request
     * @return Response
     */
    public function updateComment($id, CommentRepository $commentRepository, Request $request)
    {
        $comment = $commentRepository->find($id);
        if ($comment->getEtudiant() === $this->getUser()->getEtudiant()) {
            $comment->setEvent($comment->getEvent());
            $comment->setContent($comment->getContent());
            $comment->setNote($comment->getNote());
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);
            $form->getErrors();
            if ($form->isSubmitted() && $form->isValid()) {
                $en = $this->getDoctrine()->getManager();
                $en->persist($comment);
                $en->flush();
                return $this->redirect($this->genyeserateUrl('event.show', [
                    'id' => $comment->getEvent()->getId()
                ]));
            }
            return $this->render("event/comment.html.twig", [
                'form' => $form->createView()
            ]);
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimmer ce commentaire');
            return $this->redirect($this->generateUrl('event.show', [
                'id' => $comment->getEvent()->getId()
            ]));
        }
    }

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/unsubscribe/{id}", name="unsubscribe")
     */
    public function unsubscribe($id,EventRepository $eventRepository)
    {
        $event = $eventRepository->find($id);
        $etudiant=$this->getUser()->getEtudiant();
        $en = $this->getDoctrine()->getManager();
        $etudiant->removeEvent($event);
        $en->flush();
        $this->addFlash('success', 'Evénement effacé');
        return $this->redirect($this->generateUrl('etudiant_myevents', [
            'id' => $id
        ]));
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/mysubscribers/{id}", name="mysubscribers")
     */
    public function showSubscribers($id,EventRepository $eventRepository){
        $event=$eventRepository->find($id);
        if($event->getClub()=== $this->getUser()->getClub()) {
            $etudiants = $event->getEtudiant();
            return $this->render('event/showMySubscribers.html.twig', [
                'etudiants' => $etudiants
            ]);
        }else{
            return $this->redirect($this->generateUrl('event.show', [
                'id' => $id
            ]));
        }
    }
    /**
     * @IsGranted("ROLE_CLUB")
     * @Route("/mysponsors/{id}", name="mysponsors")
     */
    public function showSponsors($id,EventRepository $eventRepository){
        $event=$eventRepository->find($id);
        if($event->getClub()=== $this->getUser()->getClub()) {
            $sponsors = $event->getSponsor();
            return $this->render('event/showMySponsors.html.twig', [
                'sponsors' => $sponsors
            ]);
        }else{
            return $this->redirect($this->generateUrl('event.show', [
                'id' => $id
            ]));
        }
    }

}
