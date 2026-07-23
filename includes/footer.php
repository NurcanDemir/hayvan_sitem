<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\index.php
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yuva Ol - Hayvan Sahiplendirme Platformu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100">

    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <a href="index.php" class="flex items-center">
                            <div class="text-3xl">🐾</div>
                            <h1 class="text-2xl font-bold text-primary-lighter ml-2">Yuva Ol</h1>
                        </a>
                    </div>
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="index.php" class="text-gray-800 hover:text-primary-lighter px-3 py-2 rounded-md text-sm font-medium">Ana Sayfa</a>
                        <a href="ilanlar.php" class="text-gray-800 hover:text-primary-lighter px-3 py-2 rounded-md text-sm font-medium">Sahiplendirme</a>
                        <a href="barinaklar.php" class="text-gray-800 hover:text-primary-lighter px-3 py-2 rounded-md text-sm font-medium">Barınaklar</a>
                        <a href="etkinlikler.php" class="text-gray-800 hover:text-primary-lighter px-3 py-2 rounded-md text-sm font-medium">Etkinlikler</a>
                    </div>
                    <div class="hidden md:flex items-center">
                        <a href="iletisim.php" class="bg-primary text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-primary-dark transition-all duration-300">İletişim</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-grow">
            <!-- Ana içerik buraya gelecek -->
        </main>

        <!-- SADECE BU FOOTER KALSIN -->
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>

<?php
if (isset($stmt)) $stmt->close();
if (isset($sahiplenen_stmt)) $sahiplenen_stmt->close();
$conn->close();
?>

</div> <!-- Ana içerik kapanış -->

<footer class="bg-gray-800 text-white py-8 mt-12">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="text-3xl mr-3">🐾</div>
                <h3 class="text-2xl font-bold text-pink-400">Yuva Ol</h3>
            </div>
            
            <p class="text-gray-300 mb-4">
                Hayvan dostlarımız için sevgi dolu yuvalar buluyoruz.
            </p>
            
            <div class="border-t border-gray-600 pt-4">
                <p class="text-gray-400 text-sm">
                    © <?= date('Y') ?> Yuva Ol - Hayvan Sahiplendirme Platformu. Tüm hakları saklıdır.
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollTop" class="fixed bottom-6 right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:bg-primary-dark transition-all duration-300 opacity-0 invisible">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Scroll to top functionality
window.addEventListener('scroll', function() {
    const scrollTop = document.getElementById('scrollTop');
    if (window.pageYOffset > 300) {
        scrollTop.classList.remove('opacity-0', 'invisible');
    } else {
        scrollTop.classList.add('opacity-0', 'invisible');
    }
});

document.getElementById('scrollTop').addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

</body>
</html>



