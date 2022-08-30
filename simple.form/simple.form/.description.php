<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => 'Простая форма',
    "DESCRIPTION" => 'Отправка и сохранение в инфоблоки форм сайта',
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "utility",
        "CHILD" => array(
            "ID" => "navigation",
            "NAME" => 'Victory simple form'
        )
    ),
);

?>