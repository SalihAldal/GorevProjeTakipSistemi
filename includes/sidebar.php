<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="css/sidebar.css" rel="stylesheet" type="text/css">

</head>
<body>
<div class="sidebar" id="sidebarr">
    <?php if ($_SESSION['role'] == 1): ?>
   <a href="index.php" style="text-decoration: none;color: black"> <h2>ADMIN<br>PANELI</h2></a>
    <?php else:?>
    <h2>KULLANICI<br>PANELI</h2>
    <?php endif;?>
    <?php if ($_SESSION['role'] == 1): ?>
    <div class="dropdown" onclick="toggleDropdown(this)">
        <a  style="justify-content: left"><i class="fa fa-user"></i> Kullanıcı <i class="fa-solid fa-chevron-right"></i></a>
        <div class="dropdown-menu">
            <a href="kullanicilistele.php">Listele</a>
            <a href="kullaniciekle.php">Ekle</a>
        </div>
    </div>
    <?php endif;?>
    <div class="dropdown" onclick="toggleDropdown(this)">
        <a  style="justify-content: left"><i class="fa fa-file"></i> Proje <i class="fa-solid fa-chevron-right"></i></a>
        <div class="dropdown-menu">
            <a href="#">Tüm Projeler</a>
            <a href="#">Yeni Proje</a>
        </div>
    </div>
    <div class="dropdown" onclick="toggleDropdown(this)">
        <a  style="justify-content: left"><i class="fa fa-clock"></i> Görev <i class="fa-solid fa-chevron-right"></i></a>
        <div class="dropdown-menu">
            <a href="#">Aktif Görevler</a>
            <a href="#">Yeni Görev</a>
        </div>
    </div>
    <div class="dropdown" onclick="toggleDropdown(this)">
        <a style="justify-content: left" href="messages.php"><i class="fa fa-comment"></i> Mesaj </a>

    </div>
</div>
<script src="js/sidebar.js"></script>
</body>
</html>