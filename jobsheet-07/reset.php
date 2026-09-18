<?php
session_start();
session_destroy();
header('Location: index.php'); // sesuaikan dengan halaman utama project kamu
exit;