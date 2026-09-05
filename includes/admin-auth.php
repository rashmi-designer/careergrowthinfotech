<?php

declare(strict_types=1);

function is_admin_authenticated(): bool
{
    return !empty($_SESSION['user_id'])
        && !empty($_SESSION['user_role'])
        && $_SESSION['user_role'] === 'admin';
}

function require_admin(): void
{
    if (!is_admin_authenticated()) {
        header('Location: login.php');
        exit;
    }
}
