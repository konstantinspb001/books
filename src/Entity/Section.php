<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SectionRepository::class)
 */
class Section
{

    const PER_PAGE = 2900; //символов на страницу

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $bookName;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $changetAt;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="sections")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="parent")
     */
    private $sections;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sort;


    private $simbols;
    private $words;
    private $pages;
    public $html;

    public $simbolsSum = 0;    
    public $pagesSum = 0;    

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookName(): ?string
    {
        return $this->bookName;
    }

    public function setBookName(?string $bookName): self
    {
        $this->bookName = $bookName;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getChangetAt(): ?\DateTimeInterface
    {
        return $this->changetAt;
    }

    public function setChangetAt(?\DateTimeInterface $changetAt): self
    {
        $this->changetAt = $changetAt;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(self $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setParent($this);
        }

        return $this;
    }

    public function removeSection(self $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getParent() === $this) {
                $section->setParent(null);
            }
        }

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }


    public function getSimbols(): ?int
    {
        $length = 0;
        if($this->text) $length = mb_strlen($this->text, 'UTF-8');
        foreach ($this->sections as $subsection) {
            $length += $subsection->getSimbols();
        }

        return $length;
    }

    public function getWords(): ?int
    {
        $words = [];
        if($this->text) {
            $clean = preg_replace('/[[:punct:]]+/u', ' ', $this->text);
            $words = preg_split('/\s+/u', trim($clean));            
        }
        $wordsNum = sizeof($words);
        foreach ($this->sections as $subsection) {
            $wordsNum += $subsection->getWords();
        }

        return $wordsNum;
    }

    public function getPages(): ?int
    {
        $simbols = $this->getSimbols();
        $pages = ceil($simbols / self::PER_PAGE);

        return $pages;
    }

}
