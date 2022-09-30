<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [
        'FIELDS' => [
            'NAME' => 'Поля',
            'SORT' => '100',
        ],
        'EMAIL' => [
            'NAME' => 'Получатель',
            'SORT' => 200,
        ],
    ],
    'PARAMETERS' => [
        'FIELD_CATEGORY' => [
            'PARENT' => 'FIELDS',
            'NAME' => 'Значения поля Категория',
            'TYPE' => 'STRING',
            'DEFAULT' => [
                'Масла, автохимия, фильтры. Автоаксессуары, обогреватели, запчасти, сопутствующие товары',
                'Шины, диски'
            ],
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'FIELD_APPLICATION_TYPE' => [
            'PARENT' => 'FIELDS',
            'NAME' => 'Значения поля Вид заявки',
            'TYPE' => 'STRING',
            'DEFAULT' => [
                'Запрос цены и сроков поставки',
                'Пополнение складов',
                'Спецзаказ'
            ],
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'FIELD_STORAGE' => [
            'PARENT' => 'FIELDS',
            'NAME' => 'Значения поля Склад поставки',
            'TYPE' => 'STRING',
            'DEFAULT' => [
                'Склад 1',
                'Склад 2',
                'Склад 3',
            ],
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'FIELD_BRAND' => [
            'PARENT' => 'FIELDS',
            'NAME' => 'Значения поля Бренд',
            'TYPE' => 'STRING',
            'DEFAULT' => [
                'Бренд 1',
                'Бренд 2',
                'Бренд 3',
            ],
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'RECIPIENT_EMAIL' => [
            'PARENT' => 'EMAIL',
            'NAME' => 'Почта получателя',
            'TYPE' => 'STRING',
        ],
    ],
];