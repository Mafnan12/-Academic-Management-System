<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'user';
?>
<aside class="fixed left-0 top-0 h-screen w-72 bg-white border-r border-slate-200 flex flex-col py-8 z-50 shadow-soft">
<div class="px-8 mb-10">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
            <span class="material-symbols-outlined text-white text-2xl">school</span>
        </div>
        <div>
            <h1 class="text-xl font-black text-primary font-serif tracking-tight leading-none mb-1">FAST Uni</h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Management</p>
        </div>
    </div>
</div>
<nav class="flex-1 space-y-1.5 px-4 overflow-y-auto">
<?php 
$nav_items = [
    'admin' => [
        ['page' => 'index.php', 'icon' => 'dashboard', 'label' => 'Dashboard', 'path' => '/index.php'],
        ['page' => 'students.php', 'icon' => 'group', 'label' => 'Students', 'path' => '/pages/students.php'],
        ['page' => 'instructors.php', 'icon' => 'person', 'label' => 'Instructors', 'path' => '/pages/instructors.php'],
        ['page' => 'courses.php', 'icon' => 'school', 'label' => 'Courses', 'path' => '/pages/courses.php'],
        ['page' => 'enrollments.php', 'icon' => 'how_to_reg', 'label' => 'Enrollments', 'path' => '/pages/enrollments.php'],
        ['page' => 'classes.php', 'icon' => 'meeting_room', 'label' => 'Classes', 'path' => '/pages/classes.php'],
        ['page' => 'exams.php', 'icon' => 'quiz', 'label' => 'Exams', 'path' => '/pages/exams.php'],
        ['page' => 'reports.php', 'icon' => 'assessment', 'label' => 'Reports', 'path' => '/pages/reports.php'],
    ],
    'instructor' => [
        ['page' => 'instructor_dashboard.php', 'icon' => 'dashboard', 'label' => 'Dashboard', 'path' => '/pages/instructor_dashboard.php'],
        ['page' => 'courses.php', 'icon' => 'school', 'label' => 'Courses', 'path' => '/pages/courses.php'],
        ['page' => 'reports.php', 'icon' => 'assessment', 'label' => 'Reports', 'path' => '/pages/reports.php'],
    ],
    'student' => [
        ['page' => 'student_dashboard.php', 'icon' => 'dashboard', 'label' => 'Dashboard', 'path' => '/pages/student_dashboard.php'],
        ['page' => 'courses.php', 'icon' => 'school', 'label' => 'Courses', 'path' => '/pages/courses.php'],
        ['page' => 'reports.php', 'icon' => 'assessment', 'label' => 'Reports', 'path' => '/pages/reports.php'],
    ],
    'parent' => [
        ['page' => 'parent_dashboard.php', 'icon' => 'dashboard', 'label' => 'Dashboard', 'path' => '/pages/parent_dashboard.php'],
        ['page' => 'reports.php', 'icon' => 'assessment', 'label' => 'Reports', 'path' => '/pages/reports.php'],
    ]
];

$current_role_items = $nav_items[$role] ?? [];
foreach ($current_role_items as $item):
    $is_active = ($current_page == $item['page']);
?>
<a class="sidebar-item <?php echo $is_active ? 'active' : 'text-slate-600'; ?> rounded-xl px-4 py-3 flex items-center gap-3 font-medium text-sm" href="<?php echo BASE_URL . $item['path']; ?>">
    <span class="material-symbols-outlined"><?php echo $item['icon']; ?></span>
    <span><?php echo $item['label']; ?></span>
</a>
<?php endforeach; ?>
</nav>
<div class="px-4 mt-auto pt-6 border-t border-slate-100">
<a class="sidebar-item text-error rounded-xl px-4 py-3 flex items-center gap-3 font-medium text-sm hover:bg-red-50" href="<?php echo BASE_URL; ?>/auth/logout.php">
<span class="material-symbols-outlined">logout</span>
<span>Logout</span>
</a>
</div>
</aside>

