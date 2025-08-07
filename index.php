<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$kullanici_adi = $_SESSION['username'];
$rol = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600&display=swap" rel="stylesheet">
    <link href="css/stylesheet.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<?php
include 'includes/sidebar.php';
?>
<div class="main">
<?php
include "includes/header.php";
?>
    <div class="stats">
        <div class="stat-box">
            <h3>TOPLAM PROJE</h3>
            <p>8</p>
        </div>
        <div class="stat-box">
            <h3>TAMAMLANAN PROJE</h3>
            <p>3</p>
        </div>
        <div class="stat-box">
            <h3>ÇALIŞAN SAYISI</h3>
            <p>23</p>
        </div>
    </div>

    <div class="chart-box">
        <canvas id="chart"></canvas>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script>
    localStorage.setItem("id", "<?= $_SESSION['user_id'] ?>");
    localStorage.setItem("username", "<?= $_SESSION['username'] ?>");
    localStorage.setItem("role", "<?= $_SESSION['role'] ?>");
</script>
<script>
    const ctx = document.getElementById('chart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
            datasets: [{
                label: 'Aktivite',
                data: [18, 27, 23, 34, 35, 22],
                borderColor: 'cyan',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointBackgroundColor: 'cyan'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
</body>
</html>
