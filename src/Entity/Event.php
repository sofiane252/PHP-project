<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $Heure = null;

    #[ORM\Column]
    private ?int $nbrMaxParticipants = null;

    #[ORM\Column]
    private ?bool $publique = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'createdEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'attendedEvents')]
    #[ORM\JoinTable(name: 'event_user')]
    private Collection $attendees;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeure(): ?string
    {
        return $this->Heure;
    }

    public function setHeure(string $Heure): static
    {
        $this->Heure = $Heure;

        return $this;
    }

    public function getNbrMaxParticipants(): ?int
    {
        return $this->nbrMaxParticipants;
    }

    public function setNbrMaxParticipants(int $nbrMaxParticipants): static
    {
        $this->nbrMaxParticipants = $nbrMaxParticipants;

        return $this;
    }

    public function isPublique(): ?bool
    {
        return $this->publique;
    }

    public function setPublique(bool $publique): static
    {
        $this->publique = $publique;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(User $user): static
    {
        if (!$this->attendees->contains($user)) {
            $this->attendees[] = $user;
        }

        return $this;
    }

    public function removeAttendee(User $user): static
    {
        $this->attendees->removeElement($user);

        return $this;
    }

    public function getNbrAttendees(): int
    {
        return $this->attendees->count();
    }
}
