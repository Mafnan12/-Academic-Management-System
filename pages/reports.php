<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// 1. Count students per class
$studentsPerClass = $pdo->query("
    SELECT class, COUNT(*) as count 
    FROM students 
    WHERE class IS NOT NULL AND class != ''
    GROUP BY class 
    ORDER BY count DESC
")->fetchAll();

// 2. Count enrollments per course (using student_class_enrollment → classes → courses)
$enrollmentsPerCourse = $pdo->query("
    SELECT co.course_code, co.course_name, COUNT(sce.student_id) as enrolled_count
    FROM courses co
    LEFT JOIN classes cl ON co.id = cl.course_id
    LEFT JOIN student_class_enrollment sce ON cl.id = sce.class_id AND sce.status = 'enrolled'
    GROUP BY co.id, co.course_code, co.course_name
    ORDER BY enrolled_count DESC
")->fetchAll();

// 3. Student enrollment summary (replaces the old view query)
$viewData = $pdo->query("
    SELECT 
        s.id AS student_id,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        s.class,
        s.section,
        COUNT(sce.class_id) AS total_classes_enrolled,
        COALESCE(SUM(co.credits), 0) AS total_credits
    FROM students s
    LEFT JOIN student_class_enrollment sce ON s.id = sce.student_id AND sce.status = 'enrolled'
    LEFT JOIN classes cl ON sce.class_id = cl.id
    LEFT JOIN courses co ON cl.course_id = co.id
    GROUP BY s.id, s.first_name, s.last_name, s.class, s.section
    ORDER BY total_classes_enrolled DESC
    LIMIT 10
")->fetchAll();

// 4. Subquery: Students taking more credits than the average
$subqueryData = $pdo->query("
    SELECT sub.student_name, sub.class, sub.total_credits
    FROM (
        SELECT 
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            s.class,
            COALESCE(SUM(co.credits), 0) AS total_credits
        FROM students s
        LEFT JOIN student_class_enrollment sce ON s.id = sce.student_id AND sce.status = 'enrolled'
        LEFT JOIN classes cl ON sce.class_id = cl.id
        LEFT JOIN courses co ON cl.course_id = co.id
        GROUP BY s.id, s.first_name, s.last_name, s.class
    ) sub
    WHERE sub.total_credits > (
        SELECT AVG(inner_sub.total_credits) 
        FROM (
            SELECT COALESCE(SUM(co2.credits), 0) AS total_credits
            FROM students s2
            LEFT JOIN student_class_enrollment sce2 ON s2.id = sce2.student_id AND sce2.status = 'enrolled'
            LEFT JOIN classes cl2 ON sce2.class_id = cl2.id
            LEFT JOIN courses co2 ON cl2.course_id = co2.id
            GROUP BY s2.id
        ) inner_sub
    )
    ORDER BY sub.total_credits DESC
")->fetchAll();

// 5. Exam results summary
$examResults = $pdo->query("
    SELECT 
        co.course_code, co.course_name,
        e.exam_type, e.exam_name,
        COUNT(er.id) as results_entered,
        ROUND(AVG(er.marks_obtained), 1) as avg_marks,
        MAX(er.marks_obtained) as highest_marks,
        MIN(er.marks_obtained) as lowest_marks
    FROM exams e
    JOIN classes cl ON e.class_id = cl.id
    JOIN courses co ON cl.course_id = co.id
    LEFT JOIN exam_results er ON e.id = er.exam_id
    GROUP BY e.id, co.course_code, co.course_name, e.exam_type, e.exam_name
    HAVING results_entered > 0
    ORDER BY co.course_code, e.exam_date
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-[#1a237e]">System Reports</h1>
    <p class="text-sm text-gray-500">Analytics and statistical summaries</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Students per Class -->
    <div class="glass-card p-6 rounded-xl shadow-sm">
        <h2 class="text-lg font-bold text-[#1a237e] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">pie_chart</span> Students per Class
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-4 py-2">Class</th>
                        <th class="px-4 py-2 text-right">Student Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($studentsPerClass)): ?>
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-500">No data available.</td></tr>
                    <?php else: ?>
                        <?php foreach($studentsPerClass as $row): ?>
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-800"><?php echo htmlspecialchars($row['class']); ?></td>
                            <td class="px-4 py-3 text-right">
                                <span class="bg-indigo-100 text-[#1a237e] px-3 py-1 rounded-full font-bold"><?php echo $row['count']; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enrollments per Course -->
    <div class="glass-card p-6 rounded-xl shadow-sm">
        <h2 class="text-lg font-bold text-[#1a237e] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">bar_chart</span> Enrollments per Course
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-4 py-2">Course Code</th>
                        <th class="px-4 py-2">Course Name</th>
                        <th class="px-4 py-2 text-right">Enrollments</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($enrollmentsPerCourse as $row): ?>
                    <tr>
                        <td class="px-4 py-3 font-bold text-[#1a237e]"><?php echo htmlspecialchars($row['course_code']); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td class="px-4 py-3 text-right">
                            <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full font-bold"><?php echo $row['enrolled_count']; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Student Workload Summary -->
<div class="glass-card p-6 rounded-xl shadow-sm mb-6">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-[#1a237e] flex items-center gap-2">
            <span class="material-symbols-outlined">table_view</span> Student Workload Summary
        </h2>
        <p class="text-xs text-gray-500 mt-1">Shows top 10 students based on course load.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Student Name</th>
                    <th class="px-6 py-4">Class</th>
                    <th class="px-6 py-4">Total Classes Enrolled</th>
                    <th class="px-6 py-4">Total Credits</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($viewData)): ?>
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available.</td></tr>
                <?php else: ?>
                    <?php foreach($viewData as $view): ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($view['student_name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($view['class'] . ' ' . $view['section']); ?></td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $view['total_classes_enrolled']; ?> Classes</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $view['total_credits'] ?? 0; ?> Cr</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Above Average Workload (Subquery) -->
<div class="glass-card p-6 rounded-xl shadow-sm mb-6 border-l-4 border-emerald-500">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-[#1a237e] flex items-center gap-2">
            <span class="material-symbols-outlined">psychology</span> Above Average Workload
        </h2>
        <p class="text-xs text-gray-500 mt-1">Students taking more credits than the university average.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Student Name</th>
                    <th class="px-6 py-4">Class</th>
                    <th class="px-6 py-4">Total Credits</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($subqueryData)): ?>
                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No students found above average.</td></tr>
                <?php else: ?>
                    <?php foreach($subqueryData as $subData): ?>
                    <tr class="hover:bg-emerald-50/30 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($subData['student_name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($subData['class'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4">
                            <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $subData['total_credits']; ?> Cr</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Exam Results Summary -->
<?php if(!empty($examResults)): ?>
<div class="glass-card p-6 rounded-xl shadow-sm mb-6 border-l-4 border-blue-500">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-[#1a237e] flex items-center gap-2">
            <span class="material-symbols-outlined">quiz</span> Exam Results Summary
        </h2>
        <p class="text-xs text-gray-500 mt-1">Performance overview across all exams.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Course</th>
                    <th class="px-6 py-4">Exam</th>
                    <th class="px-6 py-4">Results</th>
                    <th class="px-6 py-4">Average</th>
                    <th class="px-6 py-4">Highest</th>
                    <th class="px-6 py-4">Lowest</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($examResults as $er): ?>
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-bold text-[#1a237e]"><?php echo htmlspecialchars($er['course_code']); ?></span>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($er['course_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold"><?php echo htmlspecialchars(ucfirst($er['exam_type'])); ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-700"><?php echo $er['results_entered']; ?> entries</td>
                    <td class="px-6 py-4 font-semibold text-gray-800"><?php echo $er['avg_marks']; ?></td>
                    <td class="px-6 py-4 text-green-700 font-bold"><?php echo $er['highest_marks']; ?></td>
                    <td class="px-6 py-4 text-red-600 font-bold"><?php echo $er['lowest_marks']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
