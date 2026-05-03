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
    SELECT er.*, e.exam_name, e.exam_type, e.total_marks, co.course_name, c.section
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.id
    JOIN classes c ON e.class_id = c.id
    JOIN courses co ON c.course_id = co.id
    WHERE er.student_id = $student_id
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
    JOIN classes c ON a.class_id = c.id
    JOIN courses co ON c.course_id = co.id
    WHERE a.student_id = $student_id
    GROUP BY c.id, co.course_name, c.section
")->fetchAll();
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

<section class="relative h-72 rounded-3xl overflow-hidden shadow-premium group mb-10">
    <div class="absolute inset-0 bg-gradient-to-r from-secondary-dark via-secondary-dark/60 to-transparent z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.png" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-accent font-bold uppercase tracking-[0.3em] text-xs mb-3">Academic Excellence</span>
        <h2 class="text-white text-4xl font-black mb-6 max-w-lg leading-tight font-serif">Welcome back,<br/><span class="text-primary-light"><?php echo htmlspecialchars($_SESSION['username']); ?>!</span></h2>
        <div class="flex gap-4">
            <a href="courses.php" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:bg-primary-dark transition-all flex items-center gap-2 group/btn">
                <span>View My Courses</span>
                <span class="material-symbols-outlined text-xl group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
            </a>
            <a href="reports.php" class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-8 py-3 rounded-xl font-bold hover:bg-white/20 transition-all">Academic Reports</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <!-- Stats Cards -->
    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">school</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Enrolled Courses</p>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo count($enrolled_classes); ?></h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">grade</span>
            </div>
            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">+2 New</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Results</p>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo count($recent_results); ?></h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">event_available</span>
            </div>
            <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Target 75%</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Avg Attendance</p>
        <h3 class="text-3xl font-black text-secondary-dark">
            <?php
            $avg_attendance = 0;
            if (!empty($attendance_summary)) {
                $avg_attendance = round(array_sum(array_column($attendance_summary, 'attendance_percentage')) / count($attendance_summary), 1);
            }
            echo $avg_attendance . '%';
            ?>
        </h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
            <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Due</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Pending Fees</p>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo count($pending_fees); ?></h3>
    </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- My Courses -->
    <div class="lg:col-span-2 glass-card rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-secondary-dark font-black font-serif">Current Enrollment</h2>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Fall Semester 2024</p>
            </div>
            <button class="text-primary text-xs font-black uppercase tracking-widest hover:underline">View All</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.15em]">
                        <th class="px-8 py-5">Course Details</th>
                        <th class="px-8 py-5">Section</th>
                        <th class="px-8 py-5">Instructor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($enrolled_classes)): ?>
                        <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium italic">No courses enrolled this semester.</td></tr>
                    <?php else: ?>
                        <?php foreach($enrolled_classes as $class): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="font-bold text-secondary-dark group-hover:text-primary transition-colors"><?php echo htmlspecialchars($class['course_code']); ?></div>
                                <div class="text-sm text-slate-500"><?php echo htmlspecialchars($class['course_name']); ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="bg-primary/5 text-primary px-3 py-1 rounded-lg text-xs font-black"><?php echo htmlspecialchars($class['section']); ?></span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500">
                                        <?php echo strtoupper(substr($class['instructor_first'], 0, 1)); ?>
                                    </div>
                                    <span class="text-sm text-slate-600 font-medium"><?php echo htmlspecialchars($class['instructor_first'] . ' ' . $class['instructor_last']); ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Results -->
    <div class="glass-card rounded-2xl overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b border-slate-100 bg-white/50">
            <h2 class="text-xl text-secondary-dark font-black font-serif">Recent Performance</h2>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Latest Exam Scores</p>
        </div>
        <div class="p-6 space-y-4 flex-1">
            <?php if(empty($recent_results)): ?>
                <div class="h-full flex flex-col items-center justify-center text-center p-8">
                    <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">assignment_late</span>
                    <p class="text-slate-400 text-sm font-medium italic">No results released yet.</p>
                </div>
            <?php else: ?>
                <?php foreach($recent_results as $result): ?>
                <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-primary/20 transition-all group">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-sm font-bold text-secondary-dark group-hover:text-primary transition-colors"><?php echo htmlspecialchars($result['course_name']); ?></h4>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?php echo htmlspecialchars($result['exam_type']); ?></p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-secondary-dark"><?php echo $result['marks_obtained']; ?></span>
                            <span class="text-[10px] text-slate-400 font-bold">/ <?php echo $result['total_marks']; ?></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic"><?php echo htmlspecialchars($result['section']); ?></span>
                        <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-black">GRADE <?php echo htmlspecialchars($result['grade']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="p-6 pt-0">
            <a href="reports.php" class="block w-full text-center py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Full Academic Transcript</a>
        </div>
    </div>
</div>

<!-- Attendance Analytics -->
<?php if (!empty($attendance_summary)): ?>
<section class="glass-card rounded-2xl overflow-hidden mt-8">
    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/50">
        <div>
            <h2 class="text-xl text-secondary-dark font-black font-serif">Attendance Analytics</h2>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Course-wise Attendance Tracking</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.15em]">
                    <th class="px-8 py-5">Course</th>
                    <th class="px-8 py-5">Classes</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach($attendance_summary as $attendance): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-8 py-5">
                        <div class="font-bold text-secondary-dark"><?php echo htmlspecialchars($attendance['course_name']); ?></div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?php echo htmlspecialchars($attendance['section']); ?></div>
                    </td>
                    <td class="px-8 py-5 text-sm text-slate-600 font-medium">
                        <span class="text-secondary-dark font-bold"><?php echo $attendance['present_count']; ?></span> / <?php echo $attendance['total_classes']; ?>
                    </td>
                    <td class="px-8 py-5">
                        <?php if($attendance['attendance_percentage'] >= 75): ?>
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Safe
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 text-rose-600 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Low
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-4">
                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full <?php echo $attendance['attendance_percentage'] >= 75 ? 'bg-emerald-500' : 'bg-rose-500'; ?> transition-all duration-1000" style="width: <?php echo $attendance['attendance_percentage']; ?>%"></div>
                            </div>
                            <span class="text-xs font-black text-secondary-dark w-10 text-right"><?php echo $attendance['attendance_percentage']; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>