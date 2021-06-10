<?php

namespace App\Entity;

use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Symfony\Component\Validator\Constraints as Assert ;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
    use TimeTrait;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Range(max = 20, maxMessage = "Donner une note sur 20.")
     */
    private $note;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event",inversedBy="comment")
     */
    private $event;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etudiant",inversedBy="comment")
     */
    private $etudiant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): self
    {
        $this->note = $note;

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
        $this->updatedAt = new \DateTime('NOW');    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;

        return $this;
    }

}
