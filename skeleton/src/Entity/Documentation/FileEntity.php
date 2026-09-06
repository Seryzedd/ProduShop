<?php

namespace App\Entity\Documentation;

use App\Repository\Documentation\FileEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;

#[ORM\Entity(repositoryClass: FileEntityRepository::class)]
class FileEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    const FILE_TYPE = [
        'CGV' => 'CGV',
        'CGU' => 'CGU'
    ];

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column]
    private ?DateTime $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $folder = null;

    #[ORM\Column(length: 50)]
    private string $format = 'pdf';

    private string $directory = '';

    public function __construct()
    {
        $this->createdAt = new DateTime();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getSource(): string
    {
        return '/uploads/files/Documentations/' . $this->folder . '/' . $this->name;
    }
}
