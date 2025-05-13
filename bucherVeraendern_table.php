<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Head und Header
global $conn;
include 'elementeWebseite/header.php';

// Verbindung Datenbank
include 'elementeWebseite/database_connection.php';

// Variabeln initialisieren
$suche = '';
$search_column = 'title'; // Default search column
$ASCDESC = 'ASC'; // Default Sort Order
$sortier_collumns = 'title'; // Default Sort Column
$limit = 20;
$page = 1; // Default auf die erste Seite

// Elemente vom Form nehmen
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['search'])) {
        $suche = $_GET['search'];
    }
    if (isset($_GET['search_column'])) {
        $search_column = $_GET['search_column'];
    }
    if (isset($_GET['sort_column'])) {
        $sortier_collumns = $_GET['sort_column'];
    }
    if (isset($_GET['sort_order'])) {
        $ASCDESC = $_GET['sort_order'];
    }
    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
        if ($page < 1) $page = 1; // Absicherung gegen ungültige Seitenzahlen
    }
}

// Offset berechnen
$offset = ($page - 1) * $limit;

// Whitelist von Sortierkolonnen
$erlaubte_collumns = ['id', 'katalog', 'nummer', 'kurztitle', 'kategorie', 'verkauft', 'kaufer', 'autor', 'title', 'foto', 'verfasser', 'zustand'];
if (!in_array($sortier_collumns, $erlaubte_collumns)) {
    $sortier_collumns = 'title'; // Default
}
if (!in_array($search_column, $erlaubte_collumns)) {
    $search_column = 'title'; // Default
}

// MySQL Befehl
$query = "SELECT b.*, z.beschreibung, k.kategorie 
          FROM buecher b, zustaende z, kategorien k 
          WHERE $search_column LIKE ? and b.kategorie = k.id and b.zustand = z.zustand 
          ORDER BY $sortier_collumns $ASCDESC 
          LIMIT ? OFFSET ?";

/**
 * // Debugging für den Query Aufbau (Diese Ausgabe sollten Sie in der Produktion entfernen)
 * echo "Debugging Query: $query<br>";
 * echo "Search Column: $search_column<br>";
 * echo "Search Parameter: " . htmlspecialchars('%' . $suche . '%') . "<br>";
 * echo "Page: $page<br>";
 * echo "Offset: $offset<br>";
 **/

// Vorbereiten
$stmt = $conn->prepare($query);
$search_param = '%' . $suche . '%';
$stmt->bind_param("sii", $search_param, $limit, $offset);

// Ausführen
$stmt->execute();

// Resultat als Variable speichern
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// Berechnet die Gesamtanzahl der Einträge
$total_query = "SELECT COUNT(*) as total FROM buecher WHERE $search_column LIKE ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("s", $search_param);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_entries = $total_row['total'];
$total_pages = ceil($total_entries / $limit);
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="pb-2 border-bottom">Bücher Suchen</h2>
        <a href="./bookManipulation/buchHinzufügen.php" class="btn btn-primary">
            <i class="material-icons">add</i> Neues Buch
        </a>
    </div>

    <!-- Zurück -->
    <div class="mb-3">
        <a href="admin_ansicht.php" class="btn btn-secondary">
            <i class="material-icons">arrow_back</i> Zurück zur Admin Ansicht
        </a>
    </div>

    <form method="GET" action="buecherSuchen_table.php">
        <!-- Suchleiste -->
        <div class="input-group mb-3">
            <select class="form-select" name="search_column" style="max-width: 150px;">
                <?php foreach ($erlaubte_collumns as $col): ?>
                    <option value="<?php echo $col; ?>" <?php if ($search_column == $col) echo 'selected'; ?>>
                        <?php echo ucfirst($col); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" class="form-control" name="search" placeholder="Nach...">
            <button class="btn btn-outline-secondary" type="submit">Suchen</button>
        </div>
    </form>

    <!-- Sortierleiste -->
    <div class="input-group mb-3">
        <label class="input-group-text" for="sort_column">Sortieren nach</label>
        <select class="form-select" id="sort_column" name="sort_column">
            <?php foreach ($erlaubte_collumns as $col): ?>
                <option value="<?php echo $col; ?>" <?php if ($sortier_collumns == $col) echo 'selected'; ?>>
                    <?php echo ucfirst($col); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="form-select" name="sort_order">
            <option value="ASC" <?php echo $ASCDESC == 'ASC' ? 'selected' : ''; ?>>Aufsteigend</option>
            <option value="DESC" <?php echo $ASCDESC == 'DESC' ? 'selected' : ''; ?>>Absteigend</option>
        </select>
    </div>
    </form>

    <!-- Tabelle Anzeige -->
    <table class="table-dark table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Titel</th>
            <th>Autor</th>
            <th>Katalog</th>
            <th>Kategorie</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?php echo htmlspecialchars($book['id']); ?></td>
                <td><?php echo htmlspecialchars(substr($book['kurztitle'],0, 35)); ?></td>
                <td><?php echo htmlspecialchars($book['autor']); ?></td>
                <td><?php echo htmlspecialchars($book['katalog']); ?></td>
                <td><?php echo htmlspecialchars($book['kategorie']); ?></td>
                <td>
                    <a href="editBook.php?id=<?php echo $book['id']; ?>"
                       class="btn btn-sm btn-warning">
                        <i class="material-icons">edit</i> Ändern
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>