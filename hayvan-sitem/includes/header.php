<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\includes\header.php

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yuva Ol - Hayvan Dostları Platformu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header class="bg-primary text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-bold logo-text">Yuva Ol</h1>
        <nav>
            <ul class="flex space-x-4">
                <li><a href="index.php" class="nav-link">Ana Sayfa</a></li>
                <li><a href="ilanlar.php" class="nav-link">İlanlar</a></li>
                <li><a href="kayit.php" class="nav-link">Üye Ol</a></li>
                <li><a href="giris.php" class="nav-link">Giriş Yap</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container mx-auto">