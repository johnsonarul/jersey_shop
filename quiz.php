<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_number = $_GET['order_id'];

// Verify order belongs to user and quiz hasn't been taken
$stmt = $conn->prepare("SELECT id, quiz_taken FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->bind_param("si", $order_number, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    header("Location: account.php");
    exit();
}

$order = $res->fetch_assoc();
if ($order['quiz_taken']) {
    // Already taken
    header("Location: account.php?msg=quiz_already_taken");
    exit();
}

$order_id = $order['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process answer
    $question_id = $_POST['question_id'];
    $selected_option = $_POST['option'] ?? ''; // 'A', 'B', 'C', 'D' or 'TIMEOUT'
    
    // Check answer
    $stmt_q = $conn->prepare("SELECT correct_option FROM quiz_questions WHERE id = ?");
    $stmt_q->bind_param("i", $question_id);
    $stmt_q->execute();
    $q_res = $stmt_q->get_result();
    $q_data = $q_res->fetch_assoc();
    
    $is_correct = false;
    if ($q_data && $selected_option == $q_data['correct_option']) {
        $is_correct = true;
        // Award point
        $conn->query("UPDATE users SET quiz_points = quiz_points + 1 WHERE id = $user_id");
    }
    
    // Mark quiz as taken
    $conn->query("UPDATE orders SET quiz_taken = 1 WHERE id = $order_id");
    
    if ($is_correct) {
        $_SESSION['quiz_msg'] = "<div class='alert alert-success mt-4'>Correct! You earned 1 point towards a free jersey!</div>";
    } else {
        $_SESSION['quiz_msg'] = "<div class='alert alert-danger mt-4'>Incorrect (or timeout). Better luck next time!</div>";
    }
    
    header("Location: account.php");
    exit();
}

// Fetch random question
$q_res = $conn->query("SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 1");
$question = $q_res->fetch_assoc();

include 'header.php';
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-card border-primary p-4 shadow-lg rounded">
                <h3 class="mb-3 font-weight-bold" style="color: var(--primary-color);">Sports Quiz Reward</h3>
                <p class="text-white mb-4">Answer correctly in 10 seconds to earn 1 point! 10 points = 1 Free Jersey.</p>
                
                <h1 class="display-4 text-warning mb-4" id="timer">10</h1>
                
                <h5 class="mb-4 text-white"><?= htmlspecialchars($question['question']) ?></h5>
                
                <form id="quizForm" method="POST" action="">
                    <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                    <input type="hidden" name="option" id="selectedOption" value="TIMEOUT">
                    
                    <div class="d-grid gap-2 mb-4">
                        <button type="button" class="btn btn-outline-light py-2 quiz-btn" data-opt="A"><?= htmlspecialchars($question['option_a']) ?></button>
                        <button type="button" class="btn btn-outline-light py-2 quiz-btn" data-opt="B"><?= htmlspecialchars($question['option_b']) ?></button>
                        <button type="button" class="btn btn-outline-light py-2 quiz-btn" data-opt="C"><?= htmlspecialchars($question['option_c']) ?></button>
                        <button type="button" class="btn btn-outline-light py-2 quiz-btn" data-opt="D"><?= htmlspecialchars($question['option_d']) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let timeLeft = 10;
    const timerEl = document.getElementById('timer');
    const form = document.getElementById('quizForm');
    const optInput = document.getElementById('selectedOption');
    
    const countdown = setInterval(() => {
        timeLeft--;
        timerEl.innerText = timeLeft;
        
        if (timeLeft <= 3) {
            timerEl.classList.remove('text-warning');
            timerEl.classList.add('text-danger');
        }
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            form.submit();
        }
    }, 1000);
    
    document.querySelectorAll('.quiz-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            clearInterval(countdown);
            optInput.value = this.getAttribute('data-opt');
            form.submit();
        });
    });
</script>

<?php include 'footer.php'; ?>
