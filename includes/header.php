<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>FAST University | Student Management System</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "surface-container": "#eeeef0",
                      "on-tertiary-fixed": "#390c00",
                      "outline": "#767683",
                      "secondary-fixed-dim": "#bac3ff",
                      "error": "#ba1a1a",
                      "surface-dim": "#d9dadc",
                      "on-secondary": "#ffffff",
                      "secondary-fixed": "#dee0ff",
                      "inverse-surface": "#2f3132",
                      "on-error": "#ffffff",
                      "on-error-container": "#93000a",
                      "primary-fixed-dim": "#bdc2ff",
                      "on-surface-variant": "#454652",
                      "on-secondary-container": "#11278e",
                      "on-background": "#1a1c1d",
                      "on-secondary-fixed": "#00105c",
                      "on-surface": "#1a1c1d",
                      "secondary": "#4355b9",
                      "tertiary-fixed": "#ffdbd0",
                      "surface-container-lowest": "#ffffff",
                      "surface-container-high": "#e8e8ea",
                      "on-primary-fixed": "#000767",
                      "surface-container-highest": "#e2e2e4",
                      "surface": "#f9f9fb",
                      "on-tertiary": "#ffffff",
                      "tertiary-fixed-dim": "#ffb59d",
                      "surface-tint": "#4c56af",
                      "on-primary-fixed-variant": "#343d96",
                      "surface-variant": "#e2e2e4",
                      "primary-container": "#1a237e",
                      "primary-fixed": "#e0e0ff",
                      "on-primary": "#ffffff",
                      "surface-bright": "#f9f9fb",
                      "inverse-primary": "#bdc2ff",
                      "on-primary-container": "#8690ee",
                      "on-tertiary-container": "#e17c5a",
                      "error-container": "#ffdad6",
                      "surface-container-low": "#f3f3f5",
                      "on-tertiary-fixed-variant": "#7b2e12",
                      "outline-variant": "#c6c5d4",
                      "secondary-container": "#8596ff",
                      "primary": "#000666",
                      "inverse-on-surface": "#f0f0f2",
                      "tertiary-container": "#5c1800",
                      "tertiary": "#380b00",
                      "on-secondary-fixed-variant": "#293ca0",
                      "background": "#f9f9fb"
              },
              "fontFamily": {
                      "body-md": ["Inter"],
                      "h2": ["Inter"],
                      "label-sm": ["Inter"],
                      "body-lg": ["Inter"],
                      "h1": ["Inter"]
              }
            },
          },
        }
    </script>
<style>
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background font-body-md text-on-background selection:bg-secondary-fixed selection:text-on-secondary-fixed">
<?php include 'sidebar.php'; ?>
<main class="ml-72 min-h-screen">
<header class="sticky top-0 z-40 flex items-center justify-between px-10 h-20 w-full bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-b border-white/40 shadow-[0_8px_30px_rgba(0,0,0,0.04)] font-['Inter'] antialiased">
<div class="flex items-center gap-4 w-1/3">
    <!-- Optional search globally -->
</div>
<div class="flex items-center gap-4">
<div class="flex items-center gap-2">
<button class="w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:bg-indigo-50/50 transition-all active:scale-95">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
</div>
<div class="h-8 w-[1px] bg-outline-variant mx-2"></div>
<div class="flex items-center gap-3 pl-2">
<div class="text-right">
<p class="text-body-md font-bold text-[#1a237e]"><?php echo htmlspecialchars(ucfirst($username)); ?></p>
<p class="text-[11px] font-bold text-outline uppercase tracking-wider"><?php echo htmlspecialchars($role); ?></p>
</div>
<div class="w-10 h-10 rounded-full bg-primary-container text-white flex items-center justify-center font-bold">
    <?php echo strtoupper(substr($username, 0, 1)); ?>
</div>
</div>
</div>
</header>
<div class="p-10 space-y-6">
<?php display_flash_messages(); ?>
