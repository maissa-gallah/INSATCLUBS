<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EtudiantRepository")
 */
class Etudiant
{   /**
 * @ORM\ManyToMany(targetEntity="App\Entity\Club",inversedBy="etudiant")
 */
    private $club;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event",mappedBy="etudiant")
     */
    private $event;
    /**
     *
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $imageEmp;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment",mappedBy="etudiant")
     */
    private $comment;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=180)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $prenom;
    /**
     * @ORM\Column(type="date", length=180)
     */
    private $datenaissance;
    /**
     * @ORM\Column(type="integer", length=180)
     */
    private $numCarteEtudiant;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="etudiant")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="Demandeur", inversedBy="etudiant")
     */
    private $demandeur;

    public function __construct()
    {
        $this->club = new ArrayCollection();
        $this->event = new ArrayCollection();
        $this->comment = new ArrayCollection();
    }
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDatenaissance(): ?\DateTimeInterface
    {
        return $this->datenaissance;
    }

    public function setDatenaissance(\DateTimeInterface $datenaissance): self
    {
        $this->datenaissance = $datenaissance;

        return $this;
    }

    public function getNumCarteEtudiant(): ?int
    {
        return $this->numCarteEtudiant;
    }

    public function setNumCarteEtudiant(int $numCarteEtudiant): self
    {
        $this->numCarteEtudiant = $numCarteEtudiant;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Club[]
     */
    public function getClub(): Collection
    {
        return $this->club;
    }

    public function addClub(Club $club): self
    {
        if (!$this->club->contains($club)) {
            $this->club[] = $club;
        }

        return $this;
    }

    public function removeClub(Club $club): self
    {
        if ($this->club->contains($club)) {
            $this->club->removeElement($club);
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
            $event->addEtudiant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->event->contains($event)) {
            $this->event->removeElement($event);
            $event->removeEtudiant($this);
        }

        return $this;
    }

    public function getImageEmp(): ?string
    {
        return $this->imageEmp;
    }

    public function setImageEmp(string $imageEmp): self
    {
        $this->imageEmp = $imageEmp;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComment(): Collection
    {
        return $this->comment;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comment->contains($comment)) {
            $this->comment[] = $comment;
            $comment->setEtudiant($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comment->contains($comment)) {
            $this->comment->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getEtudiant() === $this) {
                $comment->setEtudiant(null);
            }
        }

        return $this;
    }

    public function getDemandeur(): ?Demandeur
    {
        return $this->demandeur;
    }

    public function setDemandeur(?Demandeur $demandeur): self
    {
        $this->demandeur = $demandeur;

        return $this;
    }
    public function __toString()
    {
        return $this->getUser()->getEmail();
    }

}
