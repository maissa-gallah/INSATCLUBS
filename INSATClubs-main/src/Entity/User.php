<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;



    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];
    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToOne(targetEntity="Club", mappedBy="user")
     */
    private $club;
    /**
     * @ORM\OneToOne(targetEntity="Etudiant", mappedBy="user")
     */
    private $etudiant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notif",mappedBy="user")
     */
    private $notif;

    public function __construct()
    {
        $this->comment = new ArrayCollection();
        $this->notification = new ArrayCollection();
        $this->notif = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): self
    {
        $this->club = $club;

        // set (or unset) the owning side of the relation if necessary
        $newUser = null === $club ? null : $this;
        if ($club->getUser() !== $newUser) {
            $club->setUser($newUser);
        }

        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;

        // set (or unset) the owning side of the relation if necessary
        $newUser = null === $etudiant ? null : $this;
        if ($etudiant->getUser() !== $newUser) {
            $etudiant->setUser($newUser);
        }
        return $this;
    }

    /**
     * @return Collection|Notif[]
     */
    public function getNotif(): Collection
    {
        return $this->notif;
    }

    public function addNotif(Notif $notif): self
    {
        if (!$this->notif->contains($notif)) {
            $this->notif[] = $notif;
            $notif->setUser($this);
        }

        return $this;
    }

    public function removeNotif(Notif $notif): self
    {
        if ($this->notif->contains($notif)) {
            $this->notif->removeElement($notif);
            // set the owning side to null (unless already changed)
            if ($notif->getUser() === $this) {
                $notif->setUser(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getEmail();
    }




}
