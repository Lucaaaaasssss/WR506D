<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\State\MediaObjectProcessor;
use App\Repository\MediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MediaObjectRepository::class)]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            processor: MediaObjectProcessor::class,
            deserialize: false,
            validationContext: ['groups' => ['Default', 'media_object_create']],
            inputFormats: ['multipart' => ['multipart/form-data']]
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['media_object:read']],
    security: "is_granted('ROLE_USER')",
)]
class MediaObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['media_object:read'])]
    private ?int $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups(['media_object:read', 'movie:read', 'actor:read'])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath')]
    #[Assert\NotNull(groups: ['media_object_create'])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['media_object:read'])]
    public ?string $filePath = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['media_object:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // Relation avec Actor (un MediaObject peut avoir un seul Actor)
    #[ORM\OneToOne(mappedBy: 'photo', targetEntity: Actor::class)]
    private ?Actor $actor = null;

    // Relation avec Movie (un MediaObject peut avoir un seul Movie)
    #[ORM\OneToOne(mappedBy: 'poster', targetEntity: Movie::class)]
    private ?Movie $movie = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(?Actor $actor): self
    {
        // unset the owning side of the relation if necessary
        if ($actor === null && $this->actor !== null) {
            $this->actor->setPhoto(null);
        }

        // set the owning side of the relation if necessary
        if ($actor !== null && $actor->getPhoto() !== $this) {
            $actor->setPhoto($this);
        }

        $this->actor = $actor;
        return $this;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): self
    {
        // unset the owning side of the relation if necessary
        if ($movie === null && $this->movie !== null) {
            $this->movie->setPoster(null);
        }

        // set the owning side of the relation if necessary
        if ($movie !== null && $movie->getPoster() !== $this) {
            $movie->setPoster($this);
        }

        $this->movie = $movie;
        return $this;
    }
}
