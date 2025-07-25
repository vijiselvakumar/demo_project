<?php
if (isset($_POST['name']) && isset($_POST['score']) && isset($_POST['moves']) && isset($_POST['time'])) {
    $data = date('Y-m-d H:i:s') . " | " . htmlspecialchars($_POST['name']) .
        " | Score: " . intval($_POST['score']) .
        " | Moves: " . intval($_POST['moves']) .
        " | Time Left: " . intval($_POST['time']) . " sec" . PHP_EOL;

    file_put_contents('results.txt', $data, FILE_APPEND);
    echo "Result saved!";
} else {
    echo "Invalid data!";
}
?>
