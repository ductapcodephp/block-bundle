# Block Preview Rule

## 1. Attribute

JS nhận diện block qua các `data-*`

### Block field

Dùng:

    data-prop = 'tên trường trong database'

Ví dụ:

``` html
<h2 contenteditable="true" data-prop="title">
    Title
</h2>
```


------------------------------------------------------------------------

### Listing item

Mỗi item bắt buộc:

``` html
<div data-item-uuid="{{ item.uuid }}">
```

Field dùng:

    data-prop-listing-field='tên trường trong database'

Ví dụ:

``` html
<input data-prop-listing-field="title">

<div data-prop-listing-field="description"></div>

<img data-prop-listing-field="image">
```

------------------------------------------------------------------------

### Action

Dùng:

    data-action

Các action:

    add_item
    delete_item
    save_item
    open_gallery
    open_ckeditor

Ví dụ:

``` html
<button data-action="save_item">
Save
</button>
```

------------------------------------------------------------------------

# 2. Block Type YAML

Bắt buộc tạo file:

    config/blocks_type.yaml

ở project chính.

Khai báo trong:

    config/services.yaml

``` yaml
imports:
    - { resource: 'blocks_type.yaml' }
```

------------------------------------------------------------------------

## blocks_type.yaml

Format:

``` yaml
parameters:

  blocks_type:

    program_reason:

      name: 'Program reason'

      backend:
        view: 'Admin/views/program_reason.html.twig'
        controller: 'Controller::method'

      frontend:
        view: '%themes%block/program_reason.html.twig'
```

------------------------------------------------------------------------

## Rule đặt tên

Block key:

    snake_case

Ví dụ:

    program_reason
    single_banner
    about_company

Tên file twig nên trùng block:

    program_reason

    ↓

    Admin/views/program_reason.html.twig

    ↓

    themes/block/program_reason.html.twig
