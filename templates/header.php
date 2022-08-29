<?php

use PHPOffice\App\Config;

require_once($_SERVER['DOCUMENT_ROOT'] . '/export/PHPOffice/vendor/autoload.php');

global $APPLICATION;
?>
<!DOCTYPE html>
<html xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
<head>
    <? $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->showTitle() ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
    <link rel="stylesheet" href="/<?= Config::$shops_root_dir ?>/assets/styles/bootstrap_5.min.css">
    <link rel="stylesheet" href="/<?= Config::$shops_root_dir ?>/assets/styles/common.css">
</head>
<body>
<main class="pb-5">
    <header class="shop-header">
        <div class="container shop-header__wrapper">
            <h1 class="shop-header__title">ВПалитре</h1>
        </div>
    </header>
    <div class="container mb-5">
        <h2>Магазин - «<?php $APPLICATION->ShowTitle(false) ?>»</h2>
    </div>