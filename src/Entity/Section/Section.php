<?php

namespace App\Entity\Section;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity(repositoryClass=SectionRepository::class)
 */
class Section
{

    const PER_PAGE = 2900; //символов на страницу

    const STATYS_ACTIVE = 'active';
    const STATYS_ARCHIVE = 'archive';//архивирован/удалён из книги
    const STATYS_EDIT = 'edit'; //требует правок/доработки

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
     * @ORM\OrderBy({"sort" = "DESC", "id" = "ASC"})
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

    /**
     * @ORM\OneToMany(targetEntity=SectionArchive::class, mappedBy="section")
     */
    private $sectionArchives;

    /**
     * @ORM\OneToMany(targetEntity=SectionRecommendation::class, mappedBy="section")
     */
    private $recommendations;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $indexNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ideasNum;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $url;    

    public function __construct()
    {
        $this->status = self::STATYS_ACTIVE;
        $this->sections = new ArrayCollection();
        $this->sectionArchives = new ArrayCollection();
        $this->recommendations = new ArrayCollection();
    }

    //Активная? (пабликуем)
    public function isActive()
    {
        if($this->status == 'archive') return false;
        if(strlen($this->text) > 100) return true;
        if($this->sections) foreach ($this->sections as $section) {
            if($section->isActive()) return true;
        }

        return false;
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

    public function hasImage(): bool
    {
        if (!$this->text) {
            return false;
        }

        return (bool) preg_match('/!\[[^\]]*\]\([^)]+\)/u', $this->text);
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getChangetAt($format = false)
    {
        if($format && $this->changetAt) return $this->formatDateTime($this->changetAt);

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

    public function formatDateTime(DateTime $dt): string {
        $now = new DateTime();
        $today = $now->format('Y-m-d');
        $dtStr = $dt->format('Y-m-d');
        $time = $dt->format('H:i');
        
        if ($dtStr === $today) {
            return "Сегодня, $time";
        }
        
        $yesterday = $now->modify('-1 day')->format('Y-m-d');
        $now->modify('+1 day'); // Восстанавливаем $now
        
        if ($dtStr === $yesterday) {
            return "Вчера, $time";
        }
        
        return $dt->format('d') . ' ' . $dt::createFromFormat('!n', $dt->format('n'))->format('F') . ' ' . $dt->format('Y');
    }

    /**
     * @return Collection<int, SectionArchive>
     */
    public function getSectionArchives(): Collection
    {
        return $this->sectionArchives;
    }

    public function addSectionArchive(SectionArchive $sectionArchive): self
    {
        if (!$this->sectionArchives->contains($sectionArchive)) {
            $this->sectionArchives[] = $sectionArchive;
            $sectionArchive->setSection($this);
        }

        return $this;
    }

    public function removeSectionArchive(SectionArchive $sectionArchive): self
    {
        if ($this->sectionArchives->removeElement($sectionArchive)) {
            // set the owning side to null (unless already changed)
            if ($sectionArchive->getSection() === $this) {
                $sectionArchive->setSection(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SectionRecommendation>
     */
    public function getRecommendations(): Collection
    {
        return $this->recommendations;
    }

    public function addRecommendation(SectionRecommendation $recommendation): self
    {
        if (!$this->recommendations->contains($recommendation)) {
            $this->recommendations[] = $recommendation;
            $recommendation->setSection($this);
        }

        return $this;
    }

    public function removeRecommendation(SectionRecommendation $recommendation): self
    {
        if ($this->recommendations->removeElement($recommendation)) {
            // set the owning side to null (unless already changed)
            if ($recommendation->getSection() === $this) {
                $recommendation->setSection(null);
            }
        }

        return $this;
    }

    public function getIndexNumber(): ?int
    {
        return $this->indexNumber;
    }

    public function setIndexNumber(?int $indexNumber): self
    {
        $this->indexNumber = $indexNumber;

        return $this;
    }

    public function getIdeasNum(): ?int
    {
        return $this->ideasNum;
    }

    public function setIdeasNum(?int $ideasNum): self
    {
        $this->ideasNum = $ideasNum;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getData($name = null)
    {
        if($name) return $this->data[$name] ?? null;
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }


}
