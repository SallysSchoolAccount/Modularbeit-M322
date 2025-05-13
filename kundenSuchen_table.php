<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

//Head und Header
global $conn;
include 'elementeWebseite/header.php';

// Verbindung Datenbank
include 'elementeWebseite/database_connection.php';

// Variabeln initialisieren
$suche = '';
$search_column = 'name'; // Default search column
$ASCDESC = 'ASC'; // Default Sort Order
$sortier_collumns = 'name'; // Default Sort Column
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
$erlaubte_collumns = ['vorname', 'name', 'email', 'geburtstag', 'kunde_seit', 'kontaktpermail', 'id'];
if (!in_array($sortier_collumns, $erlaubte_collumns)) {
    $sortier_collumns = 'name'; // Default
}
if (!in_array($search_column, $erlaubte_collumns)) {
    $search_column = 'name'; // Default
}

// MySQL Befehl
$query = "SELECT * 
          FROM kunden 
          WHERE $search_column LIKE ? 
          ORDER BY $sortier_collumns $ASCDESC 
          LIMIT ? OFFSET ?";

/**
//Debugging für den Query Aufbau (Diese Ausgabe sollten Sie in der Produktion entfernen)
echo "Debugging Query: $query<br>";
echo "Search Column: $search_column<br>";
echo "Search Parameter: " . htmlspecialchars('%' . $suche . '%') . "<br>";
echo "Page: $page<br>";
echo "Offset: $offset<br>";
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
    $row['geschlecht'] = $row['geschlecht'] === 'M' ? 'Männlich' : ($row['geschlecht'] === 'F' ? 'Weiblich' : 'Unbekannt');
    $row['kontaktpermail'] = $row['kontaktpermail'] == 1 ? 'Ja' : 'Nein';
    $books[] = $row;
}

// Berechnet die Gesamtanzahl der Einträge
$total_query = "SELECT COUNT(*) as total FROM kunden WHERE $search_column LIKE ?";
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
        <h2 class="pb-2 border-bottom">Kunden Suchen</h2>
        <a href="kundenHinzufügen.php" class="btn btn-primary">
            <i class="material-icons">add</i> Neuer Kunde
        </a>
    </div>

    <!-- Zurück -->
    <div class="mb-3">
        <a href="admin_ansicht.php" class="btn btn-secondary">
            <i class="material-icons">arrow_back</i> Zurück zur Admin Ansicht
        </a>
    </div>

    <form method="GET" action="kundenSuchen_table.php">
        <!-- Suchleiste -->
        <div class="input-group mb-3">
            <select class="form-select" name="search_column" style="max-width: 150px;">
                <?php foreach ($erlaubte_collumns as $col): ?>
                    <option value="<?php echo $col; ?>" <?php if ($search_column == $col) echo 'selected'; ?>>
                        <?php echo ucfirst($col); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" class="form-control" name="search" placeholder="Nach Kunden suchen" value="<?php echo htmlspecialchars($suche); ?>">
            <button class="btn btn-outline-secondary" type="submit">Suchen</button>
        </div>

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
            <th>Geburtstag</th>
            <th>Name</th>
            <th>Vorname</th>
            <th>Email</th>
            <th>Kunde Seit</th>
            <th>Kunde per Mail</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($books as $customer): ?>
            <tr>
                <td><?php echo htmlspecialchars($customer['kid']); ?></td>
                <td><?php echo htmlspecialchars($customer['geburtstag']); ?></td>
                <td><?php echo htmlspecialchars($customer['vorname']); ?></td>
                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                <td><?php echo htmlspecialchars($customer['kunde_seit']); ?></td>
                <td><?php echo htmlspecialchars($customer['kontaktpermail']); ?></td>
                <td>
                <a href="editKunden.php?id=<?php echo $customer['kid']; ?>"
                   class="btn btn-sm btn-warning">
                    <i class="material-icons">edit</i> Ändern
                </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Navigation Bar -->
    <nav aria-label="Seitennavigation">
        <ul class="pagination justify-content-center">
            <!-- Zurück Button -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="kundenSuchen_table.php?page=<?php echo $page - 1; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>"><<</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">Zurück</span>
                </li>
            <?php endif; ?>

            <!-- Seitenzahlen -->
            <?php
            // Only display certain page ranges
            $range = 2; // Number of pages to show before and after the current page
            $start = max(1, $page - $range);
            $end = min($total_pages, $page + $range);

            // Display "1 ..." if we're beyond page 3
            if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="kundenSuchen_table.php?page=1&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>">1</a>
                </li>
                <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Main Page Range -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="kundenSuchen_table.php?page=<?php echo $i; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <!-- Display "… total_pages" if more pages exist -->
            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="kundenSuchen_table.php?page=<?php echo $total_pages; ?>
                    &sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>">
                        <?php echo $total_pages; ?>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Weiter Button -->
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="kundenSuchen_table.php?page=<?php echo $page + 1; ?>&sort_column=
                    <?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>">>>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">Weiter</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>