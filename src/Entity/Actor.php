<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ActorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActorRepository::class)]
#[ApiResource(
    security: "is_granted('ROLE_USER')",
    operations: [
        new \ApiPlatform\Metadata\GetCollection(security: "is_granted('ROLE_USER')"),
        new \ApiPlatform\Metadata\Get(security: "is_granted('ROLE_USER')"),
        new \ApiPlatform\Metadata\Post(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Put(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Patch(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Delete(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
// SearchFilter: rechercher par nom/prénom
#[ApiFilter(SearchFilter::class, properties: [
    'lastname' => 'partial',
    'firstname' => 'partial',
    'bio' => 'partial'
])]
// DateFilter: filtrer par dates de naissance/décès
#[ApiFilter(DateFilter::class, properties: ['dob', 'dod', 'createdAt'])]
// ExistsFilter: vérifier si l'acteur est décédé (dod existe ou non)
#[ApiFilter(ExistsFilter::class, properties: ['dod', 'firstname'])]
// OrderFilter: trier par nom, dates
#[ApiFilter(OrderFilter::class, properties: ['lastname', 'firstname', 'dob', 'createdAt'])]
class Actor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de famille est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date de naissance ne peut pas être dans le futur'
    )]
    private ?\DateTime $dob = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date de décès ne peut pas être dans le futur'
    )]
    #[Assert\Expression(
        "this.getDod() === null or this.getDob() === null or this.getDod() > this.getDob()",
        message: 'La date de décès doit être postérieure à la date de naissance'
    )]
    private ?\DateTime $dod = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La biographie est obligatoire')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La biographie doit contenir au moins {{ limit }} caractères'
    )]
    private ?string $bio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\ManyToMany(targetEntity: Movie::class, inversedBy: 'actors')]
    private Collection $movies;

    #[ORM\OneToOne(inversedBy: 'actor', targetEntity: MediaObject::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?MediaObject $photo = null;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getDob(): ?\DateTime
    {
        return $this->dob;
    }

    public function setDob(?\DateTime $dob): static
    {
        $this->dob = $dob;

        return $this;
    }

    public function getDod(): ?\DateTime
    {
        return $this->dod;
    }

    public function setDod(?\DateTime $dod): static
    {
        $this->dod = $dod;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        $this->movies->removeElement($movie);

        return $this;
    }

    public function getPhoto(): ?MediaObject
    {
        return $this->photo;
    }

    public function setPhoto(?MediaObject $photo): static
    {
        $this->photo = $photo;

        return $this;
    }
}
