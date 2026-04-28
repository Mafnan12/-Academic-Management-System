<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();
if (!is_student()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Get student info
$student_id = $pdo->query("SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();

// Fetch student's enrolled classes
$enrolled_classes = $pdo->query("
    SELECT sce.*, c.section, co.course_name, co.course_code, co.credits,
           i.first_name as instructor_first, i.last_name as instructor_last
    FROM student_class_enrollment sce
    JOIN classes c ON sce.class_id = c.id
    JOIN courses co ON c.course_id = co.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    WHERE sce.student_id = $student_id AND sce.status = 'enrolled'
")->fetchAll();

// Fetch recent results
$recent_results = $pdo->query("
    SELECT r.*, e.exam_type, e.total_marks, co.course_name, c.section
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    JOIN classes c ON e.class_id = c.id
    JOIN courses co ON c.course_id = co.id
    WHERE r.student_id = $student_id
    ORDER BY e.exam_date DESC
    LIMIT 5
")->fetchAll();

// Fetch attendance summary
$attendance_summary = $pdo->query("
    SELECT
        co.course_name,
        c.section,
        COUNT(a.id) as total_classes,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1) as attendance_percentage
    FROM attendance a
    JOIN student_class_enrollment sce ON a.student_class_id = sce.id
    JOIN classes c ON sce.class_id = c.id
    JOIN courses co ON c.course_id = co.id
    WHERE sce.student_id = $student_id
    GROUP BY c.id, co.course_name, c.section
")->fetchAll();

// Fetch pending fees
$pending_fees = $pdo->query("
    SELECT f.*, SUM(p.amount_paid) as paid_amount
    FROM fees f
    LEFT JOIN payments p ON f.id = p.fee_id
    WHERE f.student_id = $student_id AND f.status != 'paid'
    GROUP BY f.id
    HAVING f.amount > COALESCE(SUM(p.amount_paid), 0)
")->fetchAll();

require_once '../includes/header.php';
?>

<section class="relative h-64 rounded-xl overflow-hidden shadow-2xl group mb-8">
    <div class="absolute inset-0 bg-gradient-to-r from-[#1a237e]/90 via-[#1a237e]/60 to-[#1a237e]/30 z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.jpg" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-white/80 font-label-sm uppercase tracking-[0.2em] mb-2">Student Portal</span>
        <h2 class="text-white text-3xl font-bold mb-4 max-w-lg leading-tight">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <div class="flex gap-4">
            <a href="my_courses.php" class="bg-white text-[#1a237e] px-6 py-2.5 rounded-lg font-bold shadow-xl hover:bg-gray-100 transition-all">View My Courses</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">school</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Enrolled Courses</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo count($enrolled_classes); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">grade</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Results</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo count($recent_results); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Avg Attendance</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php
            $avg_attendance = 0;
            if (!empty($attendance_summary)) {
                $avg_attendance = round(array_sum(array_column($attendance_summary, 'attendance_percentage')) / count($attendance_summary), 1);
            }
            echo $avg_attendance . '%';
            ?>
        </h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">payment</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Pending Fees</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo count($pending_fees); ?></h3>
    </div>
</section>

<section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- My Courses -->
    <div class="glass-card rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-[#1a237e] font-bold">My Courses</h2>
                <p class="text-sm text-gray-500">Current semester enrollment</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4">Course</th>
                        <th class="px-6 py-4">Section</th>
                        <th class="px-6 py-4">Instructor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($enrolled_classes)): ?>
                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No courses enrolled.</td></tr>
                    <?php else: ?>
                        <?php foreach($enrolled_classes as $class): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($class['course_code']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($class['course_name']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($class['section']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                <?php echo htmlspecialchars($class['instructor_first'] . ' ' . $class['instructor_last']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Results -->
    <div class="glass-card rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-[#1a237e] font-bold">Recent Results</h2>
                <p class="text-sm text-gray-500">Latest exam scores</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4">Course</th>
                        <th class="px-6 py-4">Exam</th>
                        <th class="px-6 py-4">Score</th>
                        <th class="px-6 py-4">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($recent_results)): ?>
                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No results available.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_results as $result): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($result['course_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($result['section']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($result['exam_type']); ?></td>
                            <td class="px-6 py-4">
                                <span class="font-semibold"><?php echo $result['marks_obtained']; ?>/<?php echo $result['total_marks']; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($result['grade']); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Attendance Summary -->
<?php if (!empty($attendance_summary)): ?>
<section class="glass-card rounded-xl shadow-sm overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
        <div>
            <h2 class="text-xl text-[#1a237e] font-bold">Attendance Summary</h2>
            <p class="text-sm text-gray-500">Current semester attendance by course</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Course</th>
                    <th class="px-6 py-4">Section</th>
                    <th class="px-6 py-4">Classes Attended</th>
                    <th class="px-6 py-4">Total Classes</th>
                    <th class="px-6 py-4">Attendance %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($attendance_summary as $attendance): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($attendance['course_name']); ?></td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($attendance['section']); ?></span>
                    </td>
                    <td class="px-6 py-4"><?php echo $attendance['present_count']; ?></td>
                    <td class="px-6 py-4"><?php echo $attendance['total_classes']; ?></td>
                    <td class="px-6 py-4">
                        <span class="font-semibold <?php echo $attendance['attendance_percentage'] >= 75 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $attendance['attendance_percentage']; ?>%
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>