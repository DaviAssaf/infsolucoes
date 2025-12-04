<?php
session_start();

$usuario = isset($_SESSION['user_id']) && isset($_SESSION['user_name']);

if (!$usuario) {
    header("Location: ../login");
    exit();
}
