<?php

namespace App\Entity;

use App\Entity\Traits\TimeTrait;
use App\Entity\Demandeur;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

use Symfony\Component\Validator\Constraints as Assert ;


/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Event
{

    use TimeTrait;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $place;

    /**
     * @ORM\Column(type="string", length=100,nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(name="start_time", type="datetime")
     */
    private $start_time;

    /**
     * @Assert\GreaterThan(propertyPath="start_time")
     * @ORM\Column(name="end_time", type="datetime")
     */

    private $end_time;

    /**
     * @ORM\Column(type="string",length=100)
     */
    private $access;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="event")
     */
    private $comment;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sponsor")
     */
    private $sponsor;
//
//    private $createdAt;
//
//    private $updatedAt;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Etudiant",inversedBy="event")
     */
    private $etudiant;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Demandeur",inversedBy="event")
     */
    private $demandeur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Club",inversedBy="event")
     */
    private $club;
    public function __construct()
    {
        $this->comment = new ArrayCollection();
        $this->sponsor = new ArrayCollection();
        $this->etudiant = new ArrayCollection();
        $this->demandeur=new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

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

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->start_time;
    }
    public function setStartTime(\DateTimeInterface $start_time): self
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->end_time;
    }

    public function setEndTime(\DateTimeInterface $end_time): self
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function getAccess(): ?string
    {
        return $this->access;
    }

    public function setAccess(string $access): self
    {
        $this->access = $access;

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
            $comment->setEvent($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comment->contains($comment)) {
            $this->comment->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getEvent() === $this) {
                $comment->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sponsor[]
     */
    public function getSponsor(): Collection
    {
        return $this->sponsor;
    }

    public function addSponsor(Sponsor $sponsor): self
    {
        if (!$this->sponsor->contains($sponsor)) {
            $this->sponsor[] = $sponsor;
        }

        return $this;
    }

    public function removeSponsor(Sponsor $sponsor): self
    {
        if ($this->sponsor->contains($sponsor)) {
            $this->sponsor->removeElement($sponsor);
        }

        return $this;
    }
    /**
     * @PrePersist()
     */
    public function onPersist(){
        $this->createdAt = new \DateTime('NOW');
    }
    /**
     * *@PreUpdate
     */
    public function onUpdate(){
        $this->updatedAt = new \DateTime('NOW');
    }
    /**
     * *@ORM\PostUpdate()
     */
    public function afterUpdate(){

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
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiant->contains($etudiant)) {
            $this->etudiant->removeElement($etudiant);
        }

        return $this;
    }



    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): self
    {
        $this->club = $club;

        return $this;
    }

    /**
     * @return Collection|Demandeur[]
     */
    public function getDemandeur(): Collection
    {
        return $this->demandeur;
    }

    public function addDemandeur(Demandeur $demandeur): self
    {
        if (!$this->demandeur->contains($demandeur)) {
            $this->demandeur[] = $demandeur;
        }

        return $this;
    }

    public function removeDemandeur(Demandeur $demandeur): self
    {
        if ($this->demandeur->contains($demandeur)) {
            $this->demandeur->removeElement($demandeur);
        }

        return $this;
    }
    public function __toString()
    {
        return $this->title;
    }

}
