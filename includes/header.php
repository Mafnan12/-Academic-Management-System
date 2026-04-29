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
                      "primary-container": "#A51C30", /* Harvard Crimson */
                      "primary-dark": "#750F1D", /* Darker Crimson */
                      "secondary-accent": "#D4AF37", /* Gold for contrast */
                      "success-green": "#10b981", 
                      "surface-glass": "rgba(255, 255, 255, 0.90)",
                      "background": "#F5F5F5" /* Premium soft gray */
              },
              "fontFamily": {
                      "body-md": ["Inter", "sans-serif"],
                      "h2": ["Playfair Display", "serif"],
                      "label-sm": ["Inter", "sans-serif"],
                      "body-lg": ["Inter", "sans-serif"],
                      "h1": ["Playfair Display", "serif"]
              }
            },
          },
        }
    </script>
<style>
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.12);
            border-color: rgba(255, 255, 255, 0.8);
        }
        .glass-card-dark {
            background: #A51C30;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            box-shadow: 0 10px 40px -10px rgba(165,28,48,0.4);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
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
<p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars(ucfirst($username)); ?></p>
<p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest"><?php echo htmlspecialchars($role); ?></p>
</div>
<div class="w-10 h-10 rounded-xl bg-gray-100 border border-gray-200 text-primary-container flex items-center justify-center font-bold font-serif text-lg">
    <?php echo strtoupper(substr($username, 0, 1)); ?>
</div>
</div>
</div>
</header>
<div class="p-10 space-y-6">
<?php display_flash_messages(); ?>
