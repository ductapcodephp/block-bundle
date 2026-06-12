<?php


namespace AmzsCMS\BlockBundle\Entity;

use AmzsCMS\BlockBundle\DataType\BlockDataType;
use AmzsCMS\BlockBundle\Traits\DoctrineContentTrait;
use AmzsCMS\BlockBundle\Traits\DoctrineDescriptionTrait;
use AmzsCMS\BlockBundle\Traits\DoctrineIdentifierTrait;
use AmzsCMS\BlockBundle\Traits\DoctrineThumbnailTrait;
use AmzsCMS\BlockBundle\Traits\DoctrineTitleSubtitleTrait;
use AmzsCMS\CoreBundle\Traits\Doctrine\Timestampable;
use AmzsCMS\PageBundle\Entity\Page;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="AmzsCMS\BlockBundle\Repository\BlockRepository")
 * @ORM\Table(name="amzs_block_content")
 * @ORM\HasLifecycleCallbacks
 */
class Block
{
    use DoctrineTitleSubtitleTrait, DoctrineDescriptionTrait, DoctrineContentTrait, DoctrineThumbnailTrait, DoctrineIdentifierTrait,Timestampable;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sortOrder;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $config;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="AmzsCMS\PageBundle\Entity\Page", inversedBy="blocks")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id",nullable=true)
     */
    private $page;


    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=true)
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $imageIcon;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $imageMobile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $background;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $mobileBackground;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $textIcon;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $videoUrl;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $kind = BlockDataType::KIND_DYNAMIC;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */

    private $button;

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(?int $sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getImageIcon(): ?string
    {
        return $this->imageIcon;
    }

    public function setImageIcon(?string $imageIcon)
    {
        $this->imageIcon = $imageIcon;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image)
    {
        $this->image = $image;

        return $this;
    }

    public function getImageMobile(): ?string
    {
        return $this->imageMobile;
    }

    public function setImageMobile(?string $imageMobile)
    {
        $this->imageMobile = $imageMobile;

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background)
    {
        $this->background = $background;

        return $this;
    }

    public function getMobileBackground(): ?string
    {
        return $this->mobileBackground;
    }

    public function setMobileBackground(?string $mobileBackground)
    {
        $this->mobileBackground = $mobileBackground;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getTextIcon(): ?string
    {
        return $this->textIcon;
    }

    public function setTextIcon(?string $textIcon)
    {
        $this->textIcon = $textIcon;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location)
    {
        $this->location = $location;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl)
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(?string $kind)
    {
        $this->kind = $kind;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return mixed
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * @param mixed $button
     */
    public function setButton($button): void
    {
        $this->button = $button;
    }

    public function toArrayForSync(): array
    {
        return array(
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'image_mobile' => $this->imageMobile,
            'background' => $this->background,
            'mobile_background' => $this->mobileBackground,
            'content' => $this->content,
            'url' => $this->url,
            'location' => $this->location,
            'video_url' => $this->videoUrl,
            'kind' => $this->kind,

        );
    }
}