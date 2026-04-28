<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();
if (!is_instructor()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Get instructor info
$instructor_id = $pdo->query("SELECT id FROM instructors WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();

// Fetch instructor's classes
$classes = $pdo->query("
    SELECT c.*, co.course_name, co.course_code,
           COUNT(sce.student_id) as enrolled_students
    FROM classes c
    JOIN courses co ON c.course_id = co.id
    LEFT JOIN student_class_enrollment sce ON c.id = sce.class_id
    WHERE c.instructor_id = $instructor_id
    GROUP BY c.id
")->fetchAll();

// Fetch recent results entered
$recent_results = $pdo->query("
    SELECT r.*, s.first_name, s.last_name, e.exam_type, co.course_name
    FROM results r
    JOIN students s ON r.student_id = s.id
    JOIN exams e ON r.exam_id = e.id
    JOIN classes cl ON e.class_id = cl.id
    JOIN courses co ON cl.course_id = co.id
    WHERE r.entered_by = {$_SESSION['user_id']}
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

// Fetch upcoming assignments due
$upcoming_assignments = $pdo->query("
    SELECT a.*, c.course_name, cl.section
    FROM assignments a
    JOIN classes cl ON a.class_id = cl.id
    JOIN courses c ON cl.course_id = c.id
    WHERE cl.instructor_id = $instructor_id AND a.due_date >= CURDATE()
    ORDER BY a.due_date ASC
    LIMIT 5
")->fetchAll();

require_once '../includes/header.php';
?>

<section class="relative h-64 rounded-xl overflow-hidden shadow-2xl group mb-8">
    <div class="absolute inset-0 bg-gradient-to-r from-[#1a237e]/90 via-[#1a237e]/60 to-[#1a237e]/30 z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.jpg" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-white/80 font-label-sm uppercase tracking-[0.2em] mb-2">Instructor Portal</span>
        <h2 class="text-white text-3xl font-bold mb-4 max-w-lg leading-tight">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <div class="flex gap-4">
            <a href="my_classes.php" class="bg-white text-[#1a237e] px-6 py-2.5 rounded-lg font-bold shadow-xl hover:bg-gray-100 transition-all">View My Classes</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">class</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">My Classes</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo count($classes); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">group</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Students</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php echo array_sum(array_column($classes, 'enrolled_students')); ?>
        </h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">assignment</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Assignments</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php echo count($upcoming_assignments); ?>
        </h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">grade</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Results Entered</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php echo count($recent_results); ?>
        </h3>
    </div>
</section>

<section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- My Classes -->
    <div class="glass-card rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-[#1a237e] font-bold">My Classes</h2>
                <p class="text-sm text-gray-500">Courses I'm teaching this semester</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4">Course</th>
                        <th class="px-6 py-4">Section</th>
                        <th class="px-6 py-4">Students</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($classes)): ?>
                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No classes assigned.</td></tr>
                    <?php else: ?>
                        <?php foreach($classes as $class): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($class['course_code']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($class['course_name']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($class['section']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-700"><?php echo $class['enrolled_students']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="glass-card rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-[#1a237e] font-bold">Recent Activity</h2>
                <p class="text-sm text-gray-500">Latest results and assignments</p>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <?php if(empty($recent_results) && empty($upcoming_assignments)): ?>
                <p class="text-center text-gray-500">No recent activity.</p>
            <?php else: ?>
                <?php foreach($recent_results as $result): ?>
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                        <span class="material-symbols-outlined text-sm">grade</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-[#1a237e]">Result entered for <?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($result['course_name']); ?> - <?php echo htmlspecialchars($result['exam_type']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach($upcoming_assignments as $assignment): ?>
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <span class="material-symbols-outlined text-sm">assignment</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-[#1a237e]">Assignment due: <?php echo htmlspecialchars($assignment['title']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($assignment['course_name']); ?> - Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>