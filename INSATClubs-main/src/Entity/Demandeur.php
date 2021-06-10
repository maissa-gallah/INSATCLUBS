<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DemandeurRepository")
 */
class Demandeur
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @ORM\OneToOne(targetEntity="Etudiant", mappedBy="demandeur")
     */
    private $etudiant;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event",mappedBy="demandeur")
     */
    private $event;

    public function __construct()
    {
        $this->event = new ArrayCollection();
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;

        // set (or unset) the owning side of the relation if necessary
        $newDemandeur = null === $etudiant ? null : $this;
        if ($etudiant->getDemandeur() !== $newDemandeur) {
            $etudiant->setDemandeur($newDemandeur);
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->event->contains($event)) {
            $this->event[] = $event;
            $event->addDemandeur($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->event->contains($event)) {
            $this->event->removeElement($event);
            $event->removeDemandeur($this);
        }

        return $this;
    }
    public function __toString()
    {if($this->getEtudiant()===null) return "null";
        return $this->getEtudiant()->getUser()->getEmail();
    }



}
