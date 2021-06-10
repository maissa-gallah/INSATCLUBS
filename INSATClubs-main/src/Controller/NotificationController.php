<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\NotifRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notif", name="notif.")
 */

class NotificationController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/",name="show")
     */
    public function showMyNotifs(NotifRepository $notifRepository)
    {
        $user = $this->getUser();
        $notifs = $notifRepository->findBy(['user' => $user->getId()],['createdAt' => 'DESC']);
        foreach ($notifs as $x => $y)
            $y->setIsRead(true);
        $en = $this->getDoctrine()->getManager();
        $en->flush();
        return $this->render('notif/notif.html.twig', [
           'notifs' => $notifs
        ]);
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/remove/{id}", name="remove")
     * @param $id
     */
    public function removeNotif($id, NotifRepository $notifRepository)
    {
        $notif = $notifRepository->find($id);
        if ($notif->getUser() === $this->getUser() ) {
            $en = $this->getDoctrine()->getManager();
            $en->remove($notif);
            $en->flush();
            $this->addFlash('success', 'notif effacée');
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimmer cette notif');
        }
        return $this->redirect($this->generateUrl('notif.show'));
    }

/**
 * @IsGranted("ROLE_USER")
 * @Route("/unread",name="showunread")
 */
  public function showUnreadNotif()
  {
      $user = $this->getUser();
      $i=0;
      $notif =null;
      $notifications=$user->getNotif();
      if ($user->getEtudiant())
          $type="etudiant";
     elseif($user->getClub())
     {  $type="club";}
     else $type="admin";



      foreach ($notifications as $notif)
      {if ($notif->getIsRead()===false)
          $i++;

      }
      return new JsonResponse(['data'=>$i,
          'type'=>$type]);

  }

    /**
     * @Route("/showcomments/{id}",name="showcomments")
     * @param $id
     */
    function showcomments($id,EventRepository $eventRepository)
{$event=$eventRepository->find($id);
$comments=$event->getComment();
 return $this->render('comments.html.twig',['comments'=>$comments]);
//return new JsonResponse(['comments'=>$comments]);

}



  }
