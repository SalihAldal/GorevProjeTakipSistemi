<?php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$kullanici_adi = $_SESSION['username'];
$rol = $_SESSION['role'];
?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="css/header.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .profile {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        .dropdownn {
            position: relative;
        }

        .dropdownn img {
            cursor: pointer;
            border-radius: 50%;
            transition: transform 0.2s;
        }

        .dropdownn img:hover {
            transform: scale(1.05);
        }

        .dropdownn-menu {
            position: absolute;
            top: 50px;
            right: 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 15px;
            display: none;
            flex-direction: column;
            min-width: 250px;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .dropdownn-menu.showw {
            display: flex;
        }

        .dropdownn-menu .username {
            font-weight: bold;
            font-size: 22px;
            margin-bottom: 10px;
            color: #2a3f54;
        }

        .dropdownn-menu .logout-btn {
            text-decoration: none;
            color: white;
            background-color: #e74c3c;
            padding: 8px 12px;
            border-radius: 8px;
            text-align: center;
            transition: 0.3s;
        }

        .dropdownn-menu .logout-btn:hover {
            background-color: #c0392b;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>
</head>
<body>

<div class="topbar">
    <span class="menu-btn" onclick="toggleSidebar()">&#9776;</span>

    <div class="profile">
        <a href="messages.php" style="text-decoration: none; color: black">
            <i class="fa fa-bell"></i>
        </a>

        <div class="dropdownn">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="profil" width="40" onclick="Dropdown()">
            <div id="dropdownMenuu" class="dropdownn-menu">
                <span class="username">👋 <?= htmlspecialchars($kullanici_adi) ?></span>
                <a href="cikis.php" class="logout-btn">🔓 Çıkış Yap</a>
            </div>
        </div>
    </div>
</div>

<script>
    function Dropdown() {
        document.getElementById('dropdownMenuu').classList.toggle('showw');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropdownn img')) {
            var dropdowns = document.getElementsByClassName("dropdownn-menu");
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].classList.remove('show');
            }
        }
    }
</script>

</body>
</html>
