<?php
session_start();

// Database connection
$databasePath = '/Users/arpine/Documents/my_database.db'; // Update with your actual database path

try {
    // Create (or open) the SQLite database connection
    $pdo = new PDO("sqlite:$databasePath");

    // Create the tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS singer_name (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS song_type (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL UNIQUE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS song_name (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        singer_id INTEGER NOT NULL,
        type_id INTEGER NOT NULL,
        UNIQUE(name, singer_id, type_id),
        FOREIGN KEY (singer_id) REFERENCES singer_name(id),
        FOREIGN KEY (type_id) REFERENCES song_type(id)
    )");

    // Initialize error messages
    $errorMessages = [
        'singer' => '',
        'type' => '',
        'song' => ''
    ];

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handling the singer input
        if (isset($_POST['submit_singer'])) {
            $singerName = strtoupper(trim(preg_replace('/\s+/', ' ', $_POST['singer_name'])));

            // Check if singer already exists
            $stmt = $pdo->prepare("SELECT id FROM singer_name WHERE name = ?");
            $stmt->execute([$singerName]);
            if ($stmt->fetch()) {
                $errorMessages['singer'] = "Error: The singer name '$singerName' already exists.";
            } else {
                // Insert the new singer
                $stmt = $pdo->prepare("INSERT INTO singer_name (name) VALUES (?)");
                $stmt->execute([$singerName]);
                echo "<p class='message success'>Singer name '$singerName' added successfully!</p>";
            }
        }

        // Handling the song type input
        if (isset($_POST['submit_type'])) {
            $songType = strtoupper(trim(preg_replace('/\s+/', ' ', $_POST['song_type'])));

            // Check if song type already exists
            $stmt = $pdo->prepare("SELECT id FROM song_type WHERE type = ?");
            $stmt->execute([$songType]);
            if ($stmt->fetch()) {
                $errorMessages['type'] = "Error: The song type '$songType' already exists.";
            } else {
                // Insert the new song type
                $stmt = $pdo->prepare("INSERT INTO song_type (type) VALUES (?)");
                $stmt->execute([$songType]);
                echo "<p class='message success'>Song type '$songType' added successfully!</p>";
            }
        }

        // Handling the song input
        if (isset($_POST['submit_song'])) {
            $songName = strtoupper(trim(preg_replace('/\s+/', ' ', $_POST['song_name'])));
            $singerId = (int)$_POST['singer_id'];
            $typeId = (int)$_POST['type_id'];

            // Check if the song already exists with the same singer and type
            $stmt = $pdo->prepare("SELECT id FROM song_name WHERE name = ? AND singer_id = ? AND type_id = ?");
            $stmt->execute([$songName, $singerId, $typeId]);
            if ($stmt->fetch()) {
                $errorMessages['song'] = "Error: The song '$songName' by this singer and type already exists.";
            } else {
                // Insert the new song
                $stmt = $pdo->prepare("INSERT INTO song_name (name, singer_id, type_id) VALUES (?, ?, ?)");
                $stmt->execute([$songName, $singerId, $typeId]);
                echo "<p class='message success'>Song '$songName' added successfully!</p>";
            }
        }
    }

    // Fetching data to display tables
    $singers = $pdo->query("SELECT * FROM singer_name")->fetchAll(PDO::FETCH_ASSOC);
    $types = $pdo->query("SELECT * FROM song_type")->fetchAll(PDO::FETCH_ASSOC);
    $songs = $pdo->query("SELECT song_name.id, song_name.name as song, singer_name.name as singer, song_type.type as type 
                          FROM song_name
                          JOIN singer_name ON song_name.singer_id = singer_name.id
                          JOIN song_type ON song_name.type_id = song_type.id")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMessages['general'] = "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Song Input</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .form-container, .table-container {
            display: inline-block;
            vertical-align: top;
            margin: 0 20px;
        }
        .form-container {
            width: 45%;
        }
        .table-container {
            width: 45%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        input[type="text"], select {
            width: 100%; /* Ensure inputs fit within their container */
            padding: 8px;
            margin: 4px 0;
            box-sizing: border-box; /* Include padding and border in element's total width */
        }
        input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            display: none;
            text-align: center;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            display: block;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            display: block;
        }
        .aligned-tables {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        h2 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="aligned-tables">
    <div class="form-container">
        <h2>Enter Song Details</h2>
        <!-- Input form for adding details -->
        <form method="POST">
            <table>
                <tr>
                    <td><label for="singer_name">Singer Name:</label></td>
                    <td>
                        <input type="text" id="singer_name" name="singer_name" required>
                        <?php if ($errorMessages['singer']): ?>
                            <p class="message error"><?= htmlspecialchars($errorMessages['singer']) ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit_singer" value="Add Singer"></td>
                </tr>
                <tr>
                    <td><label for="song_type">Song Type:</label></td>
                    <td>
                        <input type="text" id="song_type" name="song_type" required>
                        <?php if ($errorMessages['type']): ?>
                            <p class="message error"><?= htmlspecialchars($errorMessages['type']) ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit_type" value="Add Song Type"></td>
                </tr>
                <tr>
                    <td><label for="song_name">Song Name:</label></td>
                    <td>
                        <input type="text" id="song_name" name="song_name" required>
                        <?php if ($errorMessages['song']): ?>
                            <p class="message error"><?= htmlspecialchars($errorMessages['song']) ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><label for="singer_id">Select Singer:</label></td>
                    <td>
                        <select id="singer_id" name="singer_id" required>
                            <?php foreach ($singers as $singer) {
                                echo "<option value='" . $singer['id'] . "'>" . htmlspecialchars($singer['name']) . "</option>";
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="type_id">Select Song Type:</label></td>
                    <td>
                        <select id="type_id" name="type_id" required>
                            <?php foreach ($types as $type) {
                                echo "<option value='" . $type['id'] . "'>" . htmlspecialchars($type['type']) . "</option>";
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit_song" value="Add Song"></td>
                </tr>
            </table>
        </form>
    </div>

    <!-- Display the song names in a table beside the form -->
    <div class="table-container">
        <h2>Song List</h2>
        <table>
            <tr><th>ID</th><th>Song Name</th><th>Singer Name</th><th>Song Type</th></tr>
            <?php foreach ($songs as $song) {
                echo "<tr><td>" . htmlspecialchars($song['id']) . "</td><td>" . htmlspecialchars($song['song']) . "</td><td>" . htmlspecialchars($song['singer']) . "</td><td>" . htmlspecialchars($song['type']) . "</td></tr>";
            } ?>
        </table>
    </div>
</div>

<script>
    document.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('focus', function() {
            const message = input.nextElementSibling;
            if (message) {
                message.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
