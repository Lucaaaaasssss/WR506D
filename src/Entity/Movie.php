<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Put(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Patch(security: "is_granted('ROLE_ADMIN')"),
        new \ApiPlatform\Metadata\Delete(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
// SearchFilter: rechercher dans les champs texte (ipartial = insensible à la casse)
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'ipartial',           // recherche partielle insensible à la casse dans le nom
    'description' => 'ipartial',     // recherche partielle insensible à la casse dans la description
    'director.firstname' => 'ipartial', // recherche sur le prénom du director
    'director.lastname' => 'ipartial'   // recherche sur le nom du director
])]
// RangeFilter: filtrer les nombres dans une plage
#[ApiFilter(RangeFilter::class, properties: ['duration', 'budget', 'nbEntries'])]
// DateFilter: filtrer par date
#[ApiFilter(DateFilter::class, properties: ['releaseData', 'createdAt'])]
// ExistsFilter: vérifier si une propriété est null ou non
#[ApiFilter(ExistsFilter::class, properties: ['description', 'image', 'director'])]
// BooleanFilter: filtrer les films en brouillon/publiés et online/offline
#[ApiFilter(BooleanFilter::class, properties: ['draft', 'online'])]
// OrderFilter: trier les résultats (TRI ≠ FILTRE mais utile)
#[ApiFilter(OrderFilter::class, properties: ['name', 'duration', 'budget', 'releaseData', 'createdAt'])]
#[ORM\HasLifecycleCallbacks]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 30,
        max: 400,
        notInRangeMessage: 'La durée doit être entre {{ min }} et {{ max }} minutes',
    )]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $releaseData = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'movies')]
    private Collection $categories;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\ManyToMany(targetEntity: Actor::class, mappedBy: 'movies')]
    private Collection $actors;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le nombre d\'entrées doit être positif')]
    private ?int $nbEntries = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    #[Assert\NotNull(message: 'Le réalisateur est obligatoire')]
    #[\ApiPlatform\Metadata\ApiProperty(readableLink: true)]
    private ?Director $director = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL doit être valide')]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le budget doit être positif')]
    private ?float $budget = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $draft = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $online = true;

    #[ORM\OneToOne(inversedBy: 'movie', targetEntity: MediaObject::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?MediaObject $poster = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->actors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getReleaseData(): ?\DateTime
    {
        return $this->releaseData;
    }

    public function setReleaseData(?\DateTime $releaseData): static
    {
        $this->releaseData = $releaseData;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addMovie($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeMovie($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
            $actor->addMovie($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        if ($this->actors->removeElement($actor)) {
            $actor->removeMovie($this);
        }

        return $this;
    }

    public function getNbEntries(): ?int
    {
        return $this->nbEntries;
    }

    public function setNbEntries(?int $nbEntries): static
    {
        $this->nbEntries = $nbEntries;

        return $this;
    }

    public function getDirector(): ?Director
    {
        return $this->director;
    }

    public function setDirector(?Director $director): static
    {
        $this->director = $director;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function isDraft(): bool
    {
        return $this->draft;
    }

    public function setDraft(bool $draft): static
    {
        $this->draft = $draft;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    public function getPoster(): ?MediaObject
    {
        return $this->poster;
    }

    public function setPoster(?MediaObject $poster): static
    {
        $this->poster = $poster;

        return $this;
    }
}
