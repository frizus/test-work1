<?php

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$asset = Asset::getInstance();
$asset->addCss($this->GetFolder() . '/lib/bootstrap/css/bootstrap.min.css');
$asset->addJs($this->GetFolder() . '/lib/jquery/jquery-3.6.1.min.js');
$asset->addJs($this->GetFolder() . '/lib/bootstrap/js/bootstrap.js');
$asset->addJs($this->GetFolder() . '/script-after.js');

if ($arResult['SENT'] ?? false) {
    echo '<div class="container">';
    echo '<h1 class="h3 mb-4">Заявка отправлена</h1>';
    echo '<p class="lead">Заявка успешно отправлена.</p>';
    echo '<a href="' . $APPLICATION->GetCurPageParam() . '">Вернуться</a>';
    echo '</div>';
    return;
}

/**
 * @var FrizusRequestFormHelper $form
 */
$form = new FrizusRequestFormHelper($arResult);
$compnentId = $this->getEditAreaId('');

echo '<div class="container frizus-request-form" id="' . $compnentId . '">';
$form->open();
echo '<h1 class="h3 mb-4">Новая заявка';
$form->showCsrfErrors();
$form->showMailErrors();
echo '</h1>';

$form->text('title', [
    'label' => 'Заголовок заявки',
    'required' => true,
]);

$form->radio('category', [
    'label' => 'Категория',
    'values' => $arParams['~FIELD_CATEGORY'],
    'required' => true,
]);

$form->radio('application_type', [
    'label' => 'Вид заявки',
    'values' => $arParams['~FIELD_APPLICATION_TYPE'],
    'required' => true,
]);

$form->select('storage', [
    'label' => 'Склад поставки',
    'values' => $arParams['~FIELD_STORAGE'],
    'defaultText' => 'Выберите склад',
]);

$orderList = new FrizusRequestFormOrderListHelper($form);
$orderList->orderList('order_list', [
    'labels' => [
        'brand' => 'Бренд',
        'name' => 'Наименование',
        'quantity' => 'Количество',
        'packaging' => 'Фасовка',
        'client' => 'Клиент',
    ],
    'fields' => $arResult['ORDER_LIST_FIELDS'],
    'brandValues' => $arParams['FIELD_BRAND'],
    'brandDefaultText' => 'Выберите бренд',
]);

$form->file('file', [
    'label' => 'Прикрепить файлы',
    'multiple' => true
]);

$form->textarea('comment', [
    'label' => 'Комментарий',
]);

$form->submit('Отправить заявку');
$form->close();
echo '</div>';

echo "<script>
(function($, window, document) {
    $(document).ready(function() {
        $('#$compnentId').frizusRequestForm()
    })
})($, window, document)
</script>";