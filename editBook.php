<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
global $conn;
include './elementeWebseite/header.php';
include './elementeWebseite/database_connection.php';

// Get the ID from the GET parameter and validate it
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo "<div class='alert alert-danger'>Ungültige ID.</div>";
    exit();
}

// Fetch the book information by ID
$query = "SELECT * FROM buecher WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

// Check if the book exists
if (!$book) {
    echo "<div class='alert alert-danger'>Das Buch mit der ID $id wurde nicht gefunden.</div>";
    exit();
}

// Handle form submission for updating book details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kurztitle = $_POST['kurztitle'];
    $autor = $_POST['autor'];
    $kategorie = $_POST['kategorie'];
    $zustand = $_POST['zustand'];
    $title = $_POST['beschreibung'];

    // Update query and prepared statement to modify the book
    $query = "UPDATE buecher SET 
              kurztitle = ?, 
              autor = ?,
              kategorie = ?, 
              zustand = ?,
              title = ?
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisii", $kurztitle, $autor, $kategorie, $zustand, $title, $id);

    if ($stmt->execute()) {
        header("Location: bucherVeraendern_table.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Fehler beim Aktualisieren des Buches. Bitte versuchen Sie es erneut.</div>";
    }
}
?>

    <div class="container">
        <h2 class="pb-2 border-bottom">Buch bearbeiten</h2>
        <form method="POST" class="col-md-6">
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" value="<?php echo $book['id']; ?>" disabled>
            </div>

            <!-- Titel ändern -->
            <div class="mb-3">
                <label class="form-label">Titel</label>
                <input type="text" name="kurztitle" class="form-control" value="<?php echo htmlspecialchars($book['kurztitle']); ?>" required>
            </div>

            <!-- Autor ändern -->
            <div class="mb-3">
                <label class="form-label">Autor</label>
                <input type="text" name="autor" class="form-control" value="<?php echo htmlspecialchars($book['autor']); ?>" required>
            </div>

            <!-- Kategorie ändern -->
            <div class="mb-3">
                <label class="form-label">Kategorie</label>
                <select name="kategorie" class="form-control" required>
                    <?php
                    $categories = $conn->query("SELECT * FROM kategorien");
                    while ($category = $categories->fetch_assoc()) {
                        $selected = ($category['id'] == $book['kategorie']) ? 'selected' : '';
                        echo "<option value='{$category['id']}' $selected>{$category['kategorie']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Zustand ändern -->
            <div class="mb-3">
                <label class="form-label">Zustand</label>
                <select name="zustand" class="form-control" required>
                    <?php
                    $conditions = $conn->query("SELECT * FROM zustaende");
                    while ($condition = $conditions->fetch_assoc()) {
                        $selected = ($condition['zustand'] == $book['zustand']) ? 'selected' : '';
                        echo "<option value='{$condition['zustand']}' $selected>{$condition['title']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Beschreibung ändern -->
            <div class="mb-3">
                <label class="form-label">Beschreibung</label>
                <textarea name="beschreibung" class="form-control" rows="3"><?php echo htmlspecialchars($book['title']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Speichern</button>
            <a href="bucherVeraendern_table.php" class="btn btn-secondary">Zurück</a>
        </form>
    </div>

<?php include './elementeWebseite/footer.php'; ?>