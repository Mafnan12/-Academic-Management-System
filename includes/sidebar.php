<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'user';
?>
<aside class="fixed left-0 top-0 h-screen w-72 bg-white border-r border-gray-200 flex flex-col py-8 gap-2 z-50">
<div class="px-8 mb-8">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center">
<!-- Local logo will be loaded from /assets/images/logo.png -->
<img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="FAST Logo" class="w-8 h-8 object-contain" onerror="this.outerHTML='<span class=\'material-symbols-outlined text-white\'>school</span>'" />
</div>
<div>
<h1 class="text-lg font-black text-primary-container text-gray-900 uppercase tracking-wider">FAST Uni</h1>
<p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Management System</p>
</div>
</div>
</div>
<nav class="flex-1 space-y-1">
<?php if ($role === 'admin'): ?>
<a class="<?php echo $current_page == 'index.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/index.php">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-body-md">Dashboard</span>
</a>
<a class="<?php echo $current_page == 'students.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/students.php">
<span class="material-symbols-outlined">group</span>
<span class="font-body-md">Students</span>
</a>
<a class="<?php echo $current_page == 'instructors.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/instructors.php">
<span class="material-symbols-outlined">person</span>
<span class="font-body-md">Instructors</span>
</a>
<a class="<?php echo $current_page == 'courses.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/courses.php">
<span class="material-symbols-outlined">school</span>
<span class="font-body-md">Courses</span>
</a>
<a class="<?php echo $current_page == 'enrollments.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/enrollments.php">
<span class="material-symbols-outlined">how_to_reg</span>
<span class="font-body-md">Enrollments</span>
</a>
<a class="<?php echo $current_page == 'classes.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/classes.php">
<span class="material-symbols-outlined">meeting_room</span>
<span class="font-body-md">Classes</span>
</a>
<a class="<?php echo $current_page == 'exams.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/exams.php">
<span class="material-symbols-outlined">quiz</span>
<span class="font-body-md">Exams</span>
</a>
<a class="<?php echo $current_page == 'reports.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/reports.php">
<span class="material-symbols-outlined">assessment</span>
<span class="font-body-md">Reports</span>
</a>
<?php elseif ($role === 'instructor'): ?>
<a class="<?php echo $current_page == 'instructor_dashboard.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/instructor_dashboard.php">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-body-md">Dashboard</span>
</a>
<a class="<?php echo $current_page == 'courses.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/courses.php">
<span class="material-symbols-outlined">school</span>
<span class="font-body-md">Courses</span>
</a>
<a class="<?php echo $current_page == 'reports.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/reports.php">
<span class="material-symbols-outlined">assessment</span>
<span class="font-body-md">Reports</span>
</a>
<?php elseif ($role === 'student'): ?>
<a class="<?php echo $current_page == 'student_dashboard.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/student_dashboard.php">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-body-md">Dashboard</span>
</a>
<a class="<?php echo $current_page == 'courses.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/courses.php">
<span class="material-symbols-outlined">school</span>
<span class="font-body-md">Courses</span>
</a>
<a class="<?php echo $current_page == 'reports.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/reports.php">
<span class="material-symbols-outlined">assessment</span>
<span class="font-body-md">Reports</span>
</a>
<?php elseif ($role === 'parent'): ?>
<a class="<?php echo $current_page == 'parent_dashboard.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/parent_dashboard.php">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-body-md">Dashboard</span>
</a>
<a class="<?php echo $current_page == 'reports.php' ? 'bg-primary-container text-white shadow-lg shadow-red-900/20' : 'text-slate-600 hover:bg-gray-100 hover:text-gray-900'; ?> rounded-lg mx-4 px-4 py-3 flex items-center gap-3 transition-all duration-300" href="<?php echo BASE_URL; ?>/pages/reports.php">
<span class="material-symbols-outlined">assessment</span>
<span class="font-body-md">Reports</span>
</a>
<?php endif; ?>
</nav>
<div class="mt-auto border-t border-slate-100 pt-6">
<a class="text-error mx-4 px-4 py-3 flex items-center gap-3 hover:bg-error-container/20 rounded-lg transition-all duration-300" href="<?php echo BASE_URL; ?>/auth/logout.php">
<span class="material-symbols-outlined">logout</span>
<span class="font-body-md">Logout</span>
</a>
</div>
</aside>
