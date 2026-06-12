<?php

declare(strict_types=1);

namespace AmzsCMS\BlockBundle\Form\Common; // Điều chỉnh lại namespace cho phù hợp với project của bạn

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitySelect2Type extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // 1. Xử lý các class CSS mặc định của Keenthemes Select2
        $existingClass = $view->vars['attr']['class'] ?? '';
        $themeClass = '';

        if ($options['theme_style'] === 'solid') {
            $themeClass = 'form-select-solid';
        } elseif ($options['theme_style'] === 'transparent') {
            $themeClass = 'form-select-transparent';
        }

        // Gộp class lại (form-select là bắt buộc)
        $view->vars['attr']['class'] = trim(sprintf('form-select %s %s', $themeClass, $existingClass));

        // 2. Thêm data-control="select2" để Javascript của Keenthemes tự động nhận diện và init
        $view->vars['attr']['data-control'] = 'select2';

        // 3. Xử lý placeholder nếu có được truyền vào form
        if (!empty($options['placeholder'])) {
            $view->vars['attr']['data-placeholder'] = $options['placeholder'];
        }

        // 4. Xử lý dropdown parent (thường dùng khi Select2 nằm trong Bootstrap Modal để không bị che mất dropdown)
        if (isset($options['data-select2-dropdown-parent-value'])) {
            $view->vars['attr']['data-dropdown-parent'] = $options['data-select2-dropdown-parent-value'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Cho phép chọn style hiển thị: 'solid', 'transparent', hoặc 'default'
            'theme_style' => 'solid',
        ]);

        // Phải define option này để Symfony không ném ra lỗi "Undefined option" khi bạn khai báo ở Form Builder
        $resolver->setDefined(['data-select2-dropdown-parent-value']);
    }

    public function getParent(): string
    {
        // Kế thừa từ EntityType để có thể truyền class (Entity), query_builder, choice_label...
        return EntityType::class;
    }
}