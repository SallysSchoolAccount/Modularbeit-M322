<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

//Header
global $conn;
include '../elementeWebseite/header.php';

//Connessione alla banca date
include '../elementeWebseite/database_connection.php';

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanificazione input
    $formData['kurztitle'] = filter_input(INPUT_POST, 'kurztitle');
    $formData['autor'] = filter_input(INPUT_POST, 'autor');
    $formData['kategorie'] = filter_input(INPUT_POST, 'kategorie', FILTER_SANITIZE_NUMBER_INT);
    $formData['zustand'] = filter_input(INPUT_POST, 'zustand', FILTER_SANITIZE_NUMBER_INT);
    $formData['beschreibung'] = filter_input(INPUT_POST, 'beschreibung');
    $formData['katalog'] = filter_input(INPUT_POST, 'katalog');

    // Validazione server-side
    if (empty($formData['kurztitle'])) {
        $errors[] = 'Titel ist erforderlich';
    }
    if (empty($formData['autor'])) {
        $errors[] = 'Autor ist erforderlich';
    }

    // Verifica che la categoria esista nel database
    if (empty($formData['kategorie'])) {
        $errors[] = 'Kategorie ist erforderlich';
    } else {
        $stmt = $conn->prepare("SELECT id FROM kategorien WHERE id = ?");
        $stmt->bind_param("i", $formData['kategorie']);
        $stmt->execute();
        if (!$stmt->get_result()->num_rows) {
            $errors[] = 'Ungültige Kategorie';
        }
    }

    // Verifica che lo stato esista nel database
    if (empty($formData['zustand'])) {
        $errors[] = 'Zustand ist erforderlich';
    } else {
        $stmt = $conn->prepare("SELECT zustand FROM zustaende WHERE zustand = ?");
        $stmt->bind_param("i", $formData['zustand']);
        $stmt->execute();
        if (!$stmt->get_result()->num_rows) {
            $errors[] = 'Ungültiger Zustand';
        }
    }

    // Se non ci sono errori, procedi con l'inserimento
    if (empty($errors)) {
        $query = "INSERT INTO buecher (kurztitle, autor, kategorie, zustand, title, katalog) 
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssissi",
            $formData['kurztitle'],
            $formData['autor'],
            $formData['kategorie'],
            $formData['zustand'],
            $formData['beschreibung'],
            $formData['katalog']
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Buch wurde erfolgreich hinzugefügt';
            header("Location: ../bucherVeraendern_table.php");
            exit();
        } else {
            $errors[] = 'Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es erneut oder wenden Sie sich an den Administrator.';
            $errors[] = 'Datenbankfehler: ' . $stmt->error;
        }
    }
}

?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="pb-2 border-bottom mb-4">Neues Buch hinzufügen</h2>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <form method="POST">
                    <div class="mb-3">
                        <label for="kurztitle" class="form-label">Titel *</label>
                        <input type="text" id="kurztitle" name="kurztitle" class="form-control"
                               value="<?php echo htmlspecialchars($formData['kurztitle'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="autor" class="form-label">Autor *</label>
                        <input type="text" id="autor" name="autor" class="form-control"
                               value="<?php echo htmlspecialchars($formData['autor'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="kategorie" class="form-label">Kategorie *</label>
                        <select id="kategorie" name="kategorie" class="form-select">
                            <option value="">Kategorie wählen</option>
                            <?php
                            $categories = $conn->query("SELECT * FROM kategorien ORDER BY kategorie");
                            while ($category = $categories->fetch_assoc()):
                                $selected = ($formData['kategorie'] ?? '') == $category['id'] ? 'selected' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($category['kategorie']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="zustand" class="form-label">Zustand *</label>
                        <select id="zustand" name="zustand" class="form-select">
                            <option value="">Zustand wählen</option>
                            <?php
                            $conditions = $conn->query("SELECT * FROM zustaende ORDER BY beschreibung");
                            while ($condition = $conditions->fetch_assoc()):
                                $selected = ($formData['zustand'] ?? '') == $condition['zustand'] ? 'selected' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($condition['zustand']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($condition['beschreibung']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="katalog" class="form-label">Katalog</label>
                        <input type="text" id="katalog" name="katalog" class="form-control"
                               value="<?php echo htmlspecialchars($formData['katalog'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="beschreibung" class="form-label">Beschreibung</label>
                        <textarea id="beschreibung" name="beschreibung" class="form-control" rows="3"><?php echo htmlspecialchars($formData['beschreibung'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex gap-2 mb-4">
                        <button type="submit" class="btn btn-primary">Buch hinzufügen</button>
                        <a href="bucherVeraendern_table.php" class="btn btn-secondary">Zurück</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include '../elementeWebseite/footer.php'; ?>