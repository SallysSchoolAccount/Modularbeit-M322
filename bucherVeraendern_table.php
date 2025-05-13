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
$erlaubte_collumns = ['id', 'katalog', 'kurztitle', 'kategorie', 'autor', 'title'];
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
            <a href="./bucherHinzufügen.php" class="btn btn-primary">
                <i class="material-icons">add</i> Neues Buch
            </a>
        </div>

        <!-- Zurück -->
        <div class="mb-3">
            <a href="admin_ansicht.php" class="btn btn-secondary">
                <i class="material-icons">arrow_back</i> Zurück zur Admin Ansicht
            </a>
        </div>

        <form method="GET" action="bucherVeraendern_table.php" class="mb-3">
            <!-- Suchleiste -->
            <div class="input-group mb-3">
                <select class="form-select" name="search_column" style="max-width: 150px;">
                    <?php foreach ($erlaubte_collumns as $col): ?>
                        <option value="<?php echo $col; ?>" <?php if ($search_column == $col) echo 'selected'; ?>>
                            <?php echo ucfirst($col); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" class="form-control" name="search" placeholder="Nach..."
                       value="<?php echo htmlspecialchars($suche); ?>">

                <button class="btn btn-outline-secondary" type="submit">Suchen</button>
            </div>

            <!-- Sortierung -->
            <div class="input-group mb-3">
                <label for="sort-column" class="input-group-text">Sortieren nach:</label>
                <select id="sort-column" class="form-select" name="sort_column" style="max-width: 200px;">
                    <?php foreach ($erlaubte_collumns as $col): ?>
                        <option value="<?php echo $col; ?>" <?php if ($sortier_collumns == $col) echo 'selected'; ?>>
                            <?php echo ucfirst($col); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="form-select" name="sort_order">
                    <option value="ASC" <?php if ($ASCDESC == 'ASC') echo 'selected'; ?>>Aufsteigend</option>
                    <option value="DESC" <?php if ($ASCDESC == 'DESC') echo 'selected'; ?>>Absteigend</option>
                </select>
            </div>
        </form>

        <!-- Tabelle -->
        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Katalog</th>
                <th>Titel</th>
                <th>Kategorie</th>
                <th>Autor</th>
                <th>Beschreibung</th>
                <th>Zustand</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['id']); ?></td>
                    <td><?php echo htmlspecialchars($book['katalog']); ?></td>
                    <td><?php echo htmlspecialchars($book['kurztitle']); ?></td>
                    <td><?php echo htmlspecialchars($book['kategorie']); ?></td>
                    <td><?php echo htmlspecialchars($book['autor']); ?></td>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['beschreibung']); ?></td>
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
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <!-- Previous button -->
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php if ($page <= 1) {
                        echo '#';
                    } else {
                        echo "bucherSuchen_table.php?search=" . urlencode($suche) . "&search_column=" . $search_column . "&sort_column=" . $sortier_collumns . "&sort_order=" . $ASCDESC . "&page=" . ($page - 1);
                    } ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- First page -->
                <?php if ($page > 4): ?>
                    <li class="page-item">
                        <a class="page-link"
                           href="bucherSuchen_table.php?search=<?php echo urlencode($suche); ?>&search_column=<?php echo $search_column; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>&page=1">1</a>
                    </li>
                    <?php if ($page > 5): ?>
                        <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Pages around current page -->
                <?php
                $start_page = max(1, $page - 3);
                $end_page = min($total_pages, $page + 3);

                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link"
                           href="bucherSuchen_table.php?search=<?php echo urlencode($suche); ?>&search_column=<?php echo $search_column; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Last pages -->
                <?php if ($page < $total_pages - 3): ?>
                    <?php if ($page < $total_pages - 4): ?>
                        <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link"
                           href="bucherSuchen_table.php?search=<?php echo urlencode($suche); ?>&search_column=<?php echo $search_column; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next button -->
                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php if ($page >= $total_pages) {
                        echo '#';
                    } else {
                        echo "bucherSuchen_table.php?search=" . urlencode($suche) . "&search_column=" . $search_column . "&sort_column=" . $sortier_collumns . "&sort_order=" . $ASCDESC . "&page=" . ($page + 1);
                    } ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

<?php
// Footer
include 'elementeWebseite/footer.php';
?>