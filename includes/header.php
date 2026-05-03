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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "primary": {
                        "DEFAULT": "#1e40af",
                        "dark": "#1e3a8a",
                        "light": "#3b82f6",
                        "soft": "#eff6ff"
                      },
                      "secondary": {
                        "DEFAULT": "#1f2937",
                        "dark": "#111827",
                        "light": "#374151"
                      },
                      "accent": "#f59e0b",
                      "success": "#10b981",
                      "warning": "#f59e0b",
                      "error": "#ef4444",
                      "background": "#f8fafc",
                      "surface": "#ffffff",
                      "muted": "#f1f5f9"
              },
              "fontFamily": {
                      "sans": ["Inter", "sans-serif"],
                      "serif": ["Playfair Display", "serif"]
              },
              "borderRadius": {
                "xl": "1rem",
                "2xl": "1.5rem",
                "3xl": "2rem"
              },
              "boxShadow": {
                "soft": "0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05)",
                "premium": "0 20px 50px -12px rgba(0, 0, 0, 0.12)"
              }
            },
          },
        }
    </script>
<style>
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            border-color: rgba(255, 255, 255, 0.8);
        }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background: rgba(165, 28, 48, 0.05);
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background: #A51C30;
            color: white;
            box-shadow: 0 10px 20px -5px rgba(165, 28, 48, 0.3);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .sidebar-item.active .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
</head>
<body class="bg-background font-sans text-secondary-dark antialiased">
<?php include 'sidebar.php'; ?>
<main class="ml-72 min-h-screen">
<header class="sticky top-0 z-40 flex items-center justify-between px-10 h-20 w-full bg-white/80 backdrop-blur-md border-b border-slate-200/50 shadow-sm font-sans">
<div class="flex items-center gap-4 w-1/3">
    <div class="relative w-full max-w-xs group">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
        <input type="text" placeholder="Search anything..." class="w-full pl-10 pr-4 py-2 bg-slate-100/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
    </div>
</div>
<div class="flex items-center gap-6">
    <div class="flex items-center gap-2">
        <button class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-primary transition-all relative">
            <span class="material-symbols-outlined">notifications</span>
            <span class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full border-2 border-white"></span>
        </button>
    </div>
    <div class="h-8 w-px bg-slate-200"></div>
    <div class="flex items-center gap-3">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-slate-900 leading-none mb-1"><?php echo htmlspecialchars(ucfirst($username)); ?></p>
            <p class="text-[10px] font-bold text-primary uppercase tracking-widest"><?php echo htmlspecialchars($role); ?></p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-primary-soft border border-primary/10 text-primary flex items-center justify-center font-bold text-lg shadow-sm">
            <?php echo strtoupper(substr($username, 0, 1)); ?>
        </div>
    </div>
</div>
</header>
<div class="p-10 space-y-8">
<?php display_flash_messages(); ?>
