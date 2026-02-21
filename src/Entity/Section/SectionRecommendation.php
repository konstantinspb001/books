<?php

namespace App\Entity\Section;

use App\Repository\Section\SectionRecommendationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SectionRecommendationRepository::class)
 */
class SectionRecommendation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="recommendations")
     */
    private $section;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $botName;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getBotName(): ?string
    {
        return $this->botName;
    }

    public function setBotName(?string $botName): self
    {
        $this->botName = $botName;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }
}
