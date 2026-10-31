<?php
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>upload tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wrapper { display: flex; width: 100%; min-height: 100vh; }
        #sidebar { min-width: 260px; max-width: 260px; background: #1e293b; color: #fff; transition: all 0.3s; }
        #sidebar .nav-link { color: #94a3b8; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 4px 12px; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: #2563eb; color: #fff; }
        #content { width: 100%; padding: 25px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="wrapper">
