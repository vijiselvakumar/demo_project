<?php
$cards = [
    ['2 + 3', '5'],
    ['4 × 2', '8'],
    ['9 - 4', '5'],
    ['6 ÷ 2', '3'],
    ['5 × 5', '25'],
    ['10 - 7', '3'],
    ['12 ÷ 4', '3'],
    ['7 + 2', '9']
];

$flatCards = [];
foreach ($cards as $pair) {
    $flatCards[] = $pair[0];
    $flatCards[] = $pair[1];
}
shuffle($flatCards);
?>

<!DOCTYPE html>
<html>
<head>
    <title>KCSKNC - BRAIN STORMING ROUND - I</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 0; padding: 0; }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 100px);
            gap: 10px;
            margin: 20px auto;
            justify-content: center;
            align-items: center;
            display: none;
        }
        .card {
            width: 100px; height: 100px;
            background: #3498db; color: white;
            display: flex; justify-content: center; align-items: center;
            font-size: 20px; border-radius: 10px;
            cursor: pointer;
        }
        .flipped { background: #2ecc71; }
        .matched { background: #27ae60; }
        #timer, #memorizeTimer { font-size: 20px; color: red; }
        #playerName {
            font-size: 24px;
            color: darkblue;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .instructions {
            border: 2px solid #000; padding: 15px; width: 60%; margin: 20px auto;
            background: #f0f0f0; text-align: left;
        }
        button { padding: 10px 20px; margin-top: 10px; cursor: pointer; }
    </style>
</head>
<body>

<h2>🧠 BRAIN STORMING - ROUND I 🕒</h2>

<div class="instructions" id="instructionsBox">
    <h3>📝 Game Instructions:</h3>
    <ol>
        <li>Enter your name to start the game.</li>
        <li><strong>Memorizing Phase (30 sec):</strong> All cards are face-up with a countdown.</li>
        <li><strong>Game Phase (60 sec):</strong> Cards flip back and matching begins with timer.</li>
    </ol>
    <input type="text" id="nameInput" placeholder="Enter your name">
    <br><br>
    <button onclick="startMemorizing()">Start Game</button>
</div>

<p id="playerName" style="display:none;"></p>

<div id="memorizeStats" style="display:none;">
    🧠 Memorizing Time Left: <span id="memorizeTimer">30</span> sec
</div>

<div id="gameStats" style="display:none;">
    Score: <span id="score">0</span> |
    Moves: <span id="moves">0</span> |
    Time Left: <span id="timer">60</span> sec
</div>

<div class="grid" id="gameBoard">
    <?php foreach ($flatCards as $index => $card): ?>
        <div class="card" data-content="<?= htmlspecialchars($card) ?>" data-index="<?= $index ?>">?</div>
    <?php endforeach; ?>
</div>

<script>
    let playerName = '';
    let flippedCards = [];
    let lock = true;
    let score = 0;
    let moves = 0;
    let memorizeTimeLeft = 30;
    let gameTimeLeft = 60;
    let gameOver = false;
    let memorizeTimer, gameTimer;

    function startMemorizing() {
        const nameInput = document.getElementById('nameInput').value.trim();
        if (!nameInput) {
            alert("Please enter your name to start the game!");
            return;
        }
        playerName = nameInput;
        document.getElementById('playerName').textContent = "Player: " + playerName;
        document.getElementById('playerName').style.display = 'block';
        document.getElementById('instructionsBox').style.display = 'none';
        document.getElementById('gameBoard').style.display = 'grid';
        document.getElementById('memorizeStats').style.display = 'block';

        document.querySelectorAll('.card').forEach(card => {
            card.textContent = card.getAttribute('data-content');
        });

        memorizeTimer = setInterval(updateMemorizeTimer, 1000);
    }

    function updateMemorizeTimer() {
        if (memorizeTimeLeft > 0) {
            memorizeTimeLeft--;
            document.getElementById('memorizeTimer').textContent = memorizeTimeLeft;
            if (memorizeTimeLeft === 0) {
                clearInterval(memorizeTimer);
                document.getElementById('memorizeStats').style.display = 'none';
                startGame();
            }
        }
    }

    function startGame() {
        document.getElementById('gameStats').style.display = 'block';
        document.querySelectorAll('.card').forEach(card => {
            card.textContent = '?';
            card.addEventListener('click', handleCardClick);
        });

        lock = false;
        gameTimer = setInterval(updateGameTimer, 1000);
    }

    function updateGameTimer() {
        if (gameTimeLeft > 0) {
            gameTimeLeft--;
            document.getElementById('timer').textContent = gameTimeLeft;
            if (gameTimeLeft === 0) {
                endGame();
            }
        }
    }

    function updateStats() {
        document.getElementById('score').textContent = score;
        document.getElementById('moves').textContent = moves;
    }

    function endGame() {
        if (gameOver) return;
        gameOver = true;
        clearInterval(gameTimer);

        fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `name=${encodeURIComponent(playerName)}&score=${score}&moves=${moves}&time=${gameTimeLeft}`
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            alert(`⭐ Game Over, ${playerName}! ⭐\n\n🎯 Final Score: ${score} Matches\n🕹️ Total Moves: ${moves}`);
        });

        document.querySelectorAll('.card').forEach(c => c.style.pointerEvents = 'none');
    }

    function handleCardClick() {
        if (lock || this.classList.contains('flipped') || this.classList.contains('matched') || gameOver) return;

        this.textContent = this.getAttribute('data-content');
        this.classList.add('flipped');
        flippedCards.push(this);

        if (flippedCards.length === 2) {
            lock = true;
            moves++;
            updateStats();

            const [first, second] = flippedCards;
            const content1 = first.getAttribute('data-content');
            const content2 = second.getAttribute('data-content');

            if (isMathMatch(content1, content2)) {
                first.classList.add('matched');
                second.classList.add('matched');
                flippedCards = [];
                lock = false;
                score++;
                updateStats();

                if (score === <?= count($cards) ?>) {
                    endGame();
                }

            } else {
                setTimeout(() => {
                    flippedCards.forEach(c => {
                        c.textContent = '?';
                        c.classList.remove('flipped');
                    });
                    flippedCards = [];
                    lock = false;
                }, 1000);
            }
        }
    }

    function isMathMatch(a, b) {
        try {
            return eval(a.replace('×', '*').replace('÷', '/')) == b ||
                   eval(b.replace('×', '*').replace('÷', '/')) == a;
        } catch {
            return false;
        }
    }
</script>

</body>
</html>
