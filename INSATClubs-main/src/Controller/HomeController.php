<?php

namespace App\Controller;

use App\Entity\Club;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{

    /**
     * @Route("/search", name="search")
     */
    public function search()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $search = $request->request->get('search');
        $clubs = $this->getDoctrine()->getRepository(Club::class)->searchByKeyword($search);
        $events = $this->getDoctrine()->getRepository(Event::class)->searchByKeyword($search);
        return $this->render('home/index.html.twig', [
            'clubs' => $clubs, 'events' => $events
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home2(){
        return $this->render('home/acceuil.html.twig');
    }
    /**
     * @Route("/termes", name="termes")
     */
    public function consulterTermes(){
        return $this->render('home/termes.html.twig');
    }
    /**
     * @Route("/erreur/accessDenied", name="error")
     */
    public function error(){
        return $this->render('home/error.html.twig');
    }

}
