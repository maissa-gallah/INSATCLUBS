<?php
namespace App\Services ;

use App\Entity\Notif;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\AdminRecipient;

class NotificationMaker
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    //Envoyer une notification au chaque etudiant.
    public function sendNotif1($etudiants, $en, $notifier, $title, $description, $id)
    {
        $notification = (new Notification($title, ['email']))
            ->content($description );
        foreach ($etudiants as $x => $y) {
            $notif = new Notif();
            $notif->setTitle($title);
            $notif->setDescription($description);
            $notif->setCreatedAt(new \DateTime('NOW'));
            $notif->setIsRead(false);
            $notif->setLinkPath($id);
            $user = $y->getUser();
            $user->addNotif($notif);
            $en->persist($notif);
            $en->flush();
            $recipient = new AdminRecipient($user->getEmail());
            try{
            $notifier->send($notification, $recipient);}
            catch(\Exception $exception){}
        }
    }

    //Envoyer une notification au club correspondant.
     public function sendNotif2($club, $en , $notifier , $title , $description, $id)
     {
         $notif = new Notif();
         $notif->setTitle($title);
         $notif->setDescription($description);
         $notif->setCreatedAt(new \DateTime('NOW'));
         $notif->setIsRead(false);
         $notif->setLinkPath($id);
         $notification = (new Notification($title, ['email']))
             ->content($description);
             $user= $club->getUser();
             $user->addNotif($notif);
             $en->persist($notif);
             $en->flush();
             $recipient = new AdminRecipient($user->getEmail());
         try{
             $notifier->send($notification, $recipient);}
         catch(\Exception $exception){}
     }

     //Envoyer une notification à chaque utilisateur
    public function sendNotif3( $users, $en, $notifier, $title, $description)
    {
        $notification = (new Notification($title, ['email']))
            ->content($description );
        foreach ($users as $x => $y) {
            //remove admin from here!
            if ( in_array("ROLE_ADMIN",$y->getRoles() ))
                continue;
            $notif = new Notif();
            $notif->setTitle($title);
            $notif->setDescription($description);
            $notif->setCreatedAt(new \DateTime('NOW'));
            $notif->setIsRead(false);
            $y->addNotif($notif);
            $en->persist($notif);
            $en->flush();
            $recipient = new AdminRecipient($y->getEmail());
            try{
                $notifier->send($notification, $recipient);}
            catch(\Exception $exception){}
        }
    }
}
