<?php

namespace AmzsCMS\BlockBundle\DTO;

use AmzsCMS\BlockBundle\Entity\Block;

final class BlockDTO
{
    private  $title;
    private  $subTitle;
    private  $description;
    private  $content;
    private $sortOrder;
    private  $background;
    private  $image;
    private  $mobileBackground;
    private  $url;
    private  $config;
    private  $listingItem;
    private  $listingItemExtra;
    private  $button;
    private  $imageMobile;
    private  $extra;
    public function __construct( $title = null, ?int $sortOrder = null,  $background = null,  $subTitle = null,  $description = null,
       $config = null,  $content = null,  $listingItem = null,  $listingItemExtra = null,  $url = null,  $mobileBackground = null,
                                 $image = null, $button  = null, $imageMobile = null, $extra = null
    )
    {
        $this->config = $config;
        $this->content = $content;
        $this->url = $url;
        $this->image = $image;
        $this->listingItem = $listingItem;
        $this->listingItemExtra = $listingItemExtra;
        $this->subTitle = $subTitle;
        $this->description = trim($description);
        $this->mobileBackground = $mobileBackground;
        $this->background = $background;
        $this->sortOrder = $sortOrder;
        $this->title = ucwords($title);
        $this->button = $button;
        $this->imageMobile = $imageMobile;
        $this->extra = $extra;

    }



    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra($extraJson, Block $entity): void
    {
        $extraData = json_decode($extraJson, true) ?? [];
        if (empty($extraData)) {
            return;
        }

        $keys = array_keys($extraData);
        $fullKey = $keys[0];
        // Lấy key đầu tiên và tách thành mảng (vd: "meta.title" -> ["meta", "title"])
//        $fullKey = array_key_first($extraData);
        $parts = explode('.', $fullKey, 2);

        // Kiểm tra cấu trúc key có hợp lệ (phải có ít nhất 2 phần tách biệt bởi dấu chấm)
        if (count($parts) < 2) {
            throw new \InvalidArgumentException("Key trong extra phải có định dạng 'prefix.key'");
        }

        [$parentKey, $childKey] = $parts;

        // Giải mã content hiện tại, mặc định là mảng trống nếu null hoặc lỗi
        $content = json_decode($entity->getContent() ?? '', true) ?? [];

        // Cập nhật giá trị
        $content[$parentKey][$childKey] = $extraData[$fullKey];

        // Lưu lại vào entity
        $entity->setContent(json_encode($content, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return string|null
     */
    public function getMobileBackground() 
    {
        return $this->mobileBackground;
    }

    /**
     * @return string|null
     */
    public function getConfig() 
    {
        return $this->config;
    }

    /**
     * @return string|null
     */
    public function getUrl() 
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getListingItem() 
    {
        return $this->listingItem;
    }
    /**
     * @return string|null
     */
    public function getListingItemExtra() 
    {
        return $this->listingItemExtra;
    }

    /**
     * @return string|null
     */
    public function getButton() 
    {
        return $this->button;
    }
    /**
     * @return string|null
     */
    public function getImageMobile() 
    {
        return $this->imageMobile;
    }

    /**
     * @param string|null $listingItem
     * @param Block $entity
     */
    public function setListingItem( $listingItem, Block $entity): void
    {
        $data = json_decode($listingItem, true);
        $content = json_decode($entity->getContent(), true) ?? [];

        if (!isset($content['listingItem'])) {
            $content['listingItem'] = [];
        }

        foreach ($data as $key => $val) {
            if (isset($val['merge']) && $val['merge'] === true) {
                unset($val['merge']);
                $oldItemData = $content['listingItem'][$key] ?? [];
                $content['listingItem'][$key] = array_merge($oldItemData, $val);
            } else {
                $content['listingItem'][$key] = $val;
            }
        }

        $entity->setContent(json_encode($content));
    }
    /**
     * @param string|null $listingItemExtra
     * @param Block $entity
     */
    public function setListingItemExtra( $listingItemExtra, Block $entity): void
    {
        $data = json_decode($listingItemExtra, true);
        $content = json_decode($entity->getContent(), true) ?? [];

        if (!isset($content['listingItemExtra'])) {
            $content['listingItemExtra'] = [];
        }

        foreach ($data as $key => $val) {
            if (isset($val['merge']) && $val['merge'] === true) {
                unset($val['merge']);
                $oldItemData = $content['listingItemExtra'][$key] ?? [];
                $content['listingItemExtra'][$key] = array_merge($oldItemData, $val);
            } else {
                $content['listingItemExtra'][$key] = $val;
            }
        }

        $entity->setContent(json_encode($content));
    }

    /**
     * @return string|null
     */
    public function getDescription() 
    {
        return $this->description;
    }
    /**
     * @return string|null
     */
    public function getBackground() 
    {
        return $this->background;
    }

    /**
     * @return int|null
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }


    /**
     * @return string|null
     */
    public function getContent() 
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @param Block $entity
     */
    public function setContent( $content, Block $entity): void
    {
        $data = json_decode($content, true);
        $content = json_decode($entity->getContent(), true);
        foreach ($data as $key => $val) {
            $content[$key] = trim($val);
        }

        $entity->setContent(json_encode($content));
    }

    /**
     * @return string|null
     */
    public function getSubTitle() 
    {
        return $this->subTitle;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    public function run(Block $entity): void
    {
        $props = get_object_vars($this);

        foreach ($props as $prop => $value) {
            $methodGetter =  'get' . ucfirst($prop);
            $methodSetter =  'set' . ucfirst($prop);
            if(method_exists($this, $methodGetter)  && !empty($value)) {
                if(method_exists($this, $methodSetter)){
                    call_user_func([$this, $methodSetter], $value, $entity);
                    continue;
                }
            }
            if (method_exists($entity, $methodGetter) && !empty($value)) {
                if(method_exists($this, $methodSetter))
                    call_user_func([$this, $methodSetter], $value, $entity);
                else
                    call_user_func([$entity, $methodSetter], $value);
            }
        }
    }
}