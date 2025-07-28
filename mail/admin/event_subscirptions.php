<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\mail\admin\event_subscriptions.php

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../config/mail_config.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get subscription statistics
$stats_sql = "SELECT 
    COUNT(*) as total_subscriptions,
    COUNT(DISTINCT email) as unique_subscribers,
    COUNT(CASE WHEN reminded_at IS NOT NULL THEN 1 END) as reminders_sent,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_subscriptions,
    COUNT(CASE WHEN DATE(subscribed_at) = CURDATE() THEN 1 END) as today_subscriptions
    FROM event_subscriptions";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get upcoming events with subscription counts
$upcoming_events_sql = "SELECT 
    e.id,
    e.baslik,
    e.etkinlik_tarihi,
    e.etkinlik_saati,
    COUNT(es.id) as subscription_count,
    COUNT(CASE WHEN es.reminded_at IS NOT NULL THEN 1 END) as reminders_sent
    FROM hayvan_etkinlikleri e
    LEFT JOIN event_subscriptions es ON e.id = es.event_id AND es.is_active = 1
    WHERE e.etkinlik_tarihi >= CURDATE() AND e.aktif = 1
    GROUP BY e.id
    ORDER BY e.etkinlik_tarihi ASC
    LIMIT 10";
$upcoming_events = $conn->query($upcoming_events_sql);

// Get recent subscriptions with pagination
$recent_sql = "SELECT es.*, e.baslik as event_title, e.etkinlik_tarihi
               FROM event_subscriptions es
               INNER JOIN hayvan_etkinlikleri e ON es.event_id = e.id
               ORDER BY es.subscribed_at DESC
               LIMIT $per_page OFFSET $offset";
$recent_result = $conn->query($recent_sql);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM event_subscriptions";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Abonelikleri - Yuva Ol Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .table-row:hover {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">📧 Etkinlik Abonelikleri</h1>
            <div class="flex space-x-4">
                <a href="../cron/send_reminders.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-paper-plane mr-2"></i>Test Reminders
                </a>
                <a href="../../admin/" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Admin Panel
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Toplam Abonelik</p>
                        <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['total_subscriptions']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-user-plus text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Benzersiz Abone</p>
                        <p class="text-2xl font-bold text-green-600"><?= number_format($stats['unique_subscribers']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-bell text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Gönderilen Hatırlatma</p>
                        <p class="text-2xl font-bold text-purple-600"><?= number_format($stats['reminders_sent']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <i class="fas fa-check-circle text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Aktif Abonelik</p>
                        <p class="text-2xl font-bold text-indigo-600"><?= number_format($stats['active_subscriptions']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-calendar-day text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Bugünkü Kayıtlar</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= number_format($stats['today_subscriptions']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Events -->
        <div class="bg-white rounded-lg shadow-lg mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">📅 Yaklaşan Etkinlikler</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etkinlik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aboneler</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatırlatmalar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                        <tr class="table-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($event['baslik']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d.m.Y l', strtotime($event['etkinlik_tarihi'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $event['etkinlik_saati'] ? substr($event['etkinlik_saati'], 0, 5) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $event['subscription_count'] ?> abone
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?= $event['reminders_sent'] ?> gönderildi
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Subscriptions -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">📋 Son Abonelikler</h2>
                <div class="text-sm text-gray-500">
                    Sayfa <?= $page ?> / <?= $total_pages ?> (Toplam: <?= number_format($total_records) ?>)
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-posta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etkinlik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etkinlik Tarihi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Abone Tarihi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatırlatma</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($subscription = $recent_result->fetch_assoc()): ?>
                        <tr class="table-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($subscription['email']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($subscription['event_title']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d.m.Y', strtotime($subscription['etkinlik_tarihi'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d.m.Y H:i', strtotime($subscription['subscribed_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($subscription['is_active']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-times-circle mr-1"></i>Pasif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($subscription['reminded_at']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-bell mr-1"></i>Gönderildi
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?= date('d.m.Y H:i', strtotime($subscription['reminded_at'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Bekliyor
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Toplam <strong><?= number_format($total_records) ?></strong> kayıt gösteriliyor
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                <i class="fas fa-chevron-left"></i> Önceki
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?>" class="px-3 py-2 text-sm rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                Sonraki <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>