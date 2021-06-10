<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClubRepository")
 */
class Club 
{
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
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=4,max=15)
     */
    private $domaine;
    /**
     * @ORM\Column(type="string", length=2000)
     */
    private $detail;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * * @Assert\Length(min=10,max=150)
     */
    private $description;
    /**
     *
     * @ORM\Column(type="string", length=255)
     * * @Assert\Length(min=5)
     */
    private $imageEmp;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="club")
     */
    private $user;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Etudiant",mappedBy="club")
     */
    private $etudiant;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event",mappedBy="club")
     */
    private $event;

    public function __construct()
    {
        $this->etudiant = new ArrayCollection();
        $this->event = new ArrayCollection();
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

    public function getDomaine(): ?string
    {
        return $this->domaine;
    }

    public function setDomaine(string $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     * @return Collection|Etudiant[]
     */
    public function getEtudiant(): Collection
    {
        return $this->etudiant;
    }

    public function addEtudiant(Etudiant $etudiant): self
    {
        if (!$this->etudiant->contains($etudiant)) {
            $this->etudiant[] = $etudiant;
            $etudiant->addClub($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiant->contains($etudiant)) {
            $this->etudiant->removeElement($etudiant);
            $etudiant->removeClub($this);
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
            $event->setClub($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->event->contains($event)) {
            $this->event->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getClub() === $this) {
                $event->setClub(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getUser()->getEmail();
    }

}
