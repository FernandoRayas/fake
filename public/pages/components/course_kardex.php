<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../../modelo/conexion.php";

$grades = [];
$students = [];

if ($_SESSION['user_role'] == "user") {
    $sql = "SELECT
        a.assignment_name,
        MAX(saf.score) AS score,
        a.max_score,
        MAX(saf.status) AS status,
        MAX(saf.submitted_at) AS submitted_at
    FROM
        submissions_assignments_files saf
    JOIN
        assignments a ON saf.assignment = a.assignment_id
    WHERE
        saf.student = ?
    GROUP BY
        a.assignment_name, a.max_score;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
} else if ($_SESSION['user_role'] == "master") {
    $sql = "SELECT
    u.name AS student_name,
    a.assignment_name,
    MAX(saf.score) AS score,
    a.max_score,
    MAX(saf.status) AS status,
    MAX(saf.submitted_at) AS submitted_at
    FROM
        submissions_assignments_files saf
    JOIN
        assignments a ON saf.assignment = a.assignment_id
    JOIN
        users u ON saf.student = u.id
    JOIN
        topics t ON a.topic = t.topic_id
    WHERE
        t.course = ?
    GROUP BY
        u.name, a.assignment_name, a.max_score;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_GET['cid']);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }

    $students = [];
    foreach ($grades as $grade) {
        $studentName = $grade['student_name'];
        if (!isset($students[$studentName])) {
            $students[$studentName] = [];
        }
        $students[$studentName][] = $grade;
    }
}
?>

<?php if ($_SESSION['user_role'] == "user"): ?>
    <div class="py-2">
        <h2>Calificaciones por tarea</h2>
        <div class="border-bottom border-1 mb-2"></div>
        <ul class="list-group list-group-flush">
            <?php foreach ($grades as $grade): ?>
                <li class="list-group-item">
                    <div class="d-flex align-items-center justify-content-between">
                        <span><?= $grade['assignment_name'] ?></span>
                        <?php if ($grade['score'] == -1): ?>
                            <span>-/<?= $grade['max_score'] ?></span>
                        <?php else: ?>
                            <span><?= $grade['score'] ?>/<?= $grade['max_score'] ?></span>
                        <?php endif; ?>
                        <span><?= $grade['submitted_at'] ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif ($_SESSION['user_role'] == "master"): ?>
    <div class="py-2">
        <h2>Calificaciones por Estudiante</h2>
        <div class="border-bottom border-1"></div>
        <div class="accordion" id="studentsAccordion">
            <?php $accordionIndex = 1;
            foreach ($students as $studentName => $studentGrades): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $accordionIndex ?>">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $accordionIndex ?>" aria-expanded="true" aria-controls="collapse<?= $accordionIndex ?>">
                            <?= $studentName ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $accordionIndex ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $accordionIndex ?>" data-bs-parent="#studentsAccordion">
                        <div class="accordion-body">
                            <ul class="list-group">
                                <?php foreach ($studentGrades as $grade): ?>
                                    <li class="list-group-item">
                                        <?= $grade['assignment_name'] ?> - Calificación: <?= $grade['score'] ?> / <?= $grade['max_score'] ?> - Estado: <?= $grade['status'] ?> - Fecha: <?= $grade['submitted_at'] ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php $accordionIndex++;
            endforeach; ?>
        </div>
    </div>
<?php endif; ?>