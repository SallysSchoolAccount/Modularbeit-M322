<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

global $conn;
include 'elementeWebseite/database_connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kurztitle = $_POST['kurztitle'];
    $autor = $_POST['autor'];
    $kategorie = $_POST['kategorie'];
    $zustand = $_POST['zustand'];
    $beschreibung = $_POST['beschreibung'];

    $query = "UPDATE buecher SET 
              kurztitle = ?, 
              autor = ?,
              kategorie = ?, 
              zustand = ? 
              WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisi", $kurztitle, $autor, $kategorie, $zustand, $id);

    if ($stmt->execute()) {
        header("Location: bücher_veraendern.php");
        exit();
    }
}

$query = "SELECT * FROM buecher WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

include 'elementeWebseite/header.php';
?>

    <div class="container">
        <h2 class="pb-2 border-bottom">Buch bearbeiten</h2>

        <form method="POST" class="col-md-6">
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" value="<?php echo $book['id']; ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Titel</label>
                <input type="text" name="kurztitle" class="form-control" value="<?php echo htmlspecialchars($book['kurztitle']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Autor</label>
                <input type="text" name="autor" class="form-control" value="<?php echo htmlspecialchars($book['autor']); ?>" required>
            </div>

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

            <div class="mb-3">
                <label class="form-label">Zustand</label>
                <select name="zustand" class="form-control" required>
                    <?php
                    $conditions = $conn->query("SELECT * FROM zustaende");
                    while ($condition = $conditions->fetch_assoc()) {
                        $selected = ($condition['zustand'] == $book['zustand']) ? 'selected' : '';
                        echo "<option value='{$condition['zustand']}' $selected>{$condition['beschreibung']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Beschreibung</label>
                <textarea name="beschreibung" class="form-control" rows="3"><?php echo htmlspecialchars($book['title']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Speichern</button>
            <a href="bücher_veraendern.php" class="btn btn-secondary">Zurück</a>
        </form>
    </div>

<?php include 'elementeWebseite/footer.php'; ?>