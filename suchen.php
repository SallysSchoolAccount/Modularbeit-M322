<?php
//Head und Header
global $conn;
include 'elementeWebseite/header.php';

// Verbindung Datenbank
include 'elementeWebseite/database_connection.php';

// Variabeln initialisieren
$suche = '';
$search_column = 'kurztitle'; // Default search column
$ASCDESC = 'ASC';
$sortier_collumns = 'kurztitle';
$limit = 20;
$page = 1;

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
    }
}

//Offset berechnen
$offset = ($page - 1) * $limit;

// Whitelist von Sortierkolonnen
$erlaubte_collumns = ['kurztitle', 'autor', 'katalog', 'kategorie', 'id'];
if (!in_array($sortier_collumns, $erlaubte_collumns)) {
    $sortier_collumns = 'kurztitle'; // Default
}
// Gleicher für sortieren
if (!in_array($search_column, $erlaubte_collumns)) {
    $search_column = 'kurztitle'; // Default
}

// MySQL befehl
$query = "SELECT b.*, z.beschreibung, k.kategorie 
          FROM buecher b, zustaende z, kategorien k 
          WHERE $search_column LIKE ? and b.kategorie = k.id and b.zustand = z.zustand 
          ORDER BY $sortier_collumns $ASCDESC 
          LIMIT ? OFFSET ?";


//Vorbereiten
$stmt = $conn->prepare($query);
$search_param = '%' . $suche . '%';
$stmt->bind_param("sii", $search_param, $limit, $offset);

//Ausfuehren
$stmt->execute();

//resultat als variable speichern
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// Berechnet die Gesamtanzahl der Einträge, die der Suchanfrage entsprechen
// und die Anzahl der Seiten basierend auf der Limitierung
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
        <h2 class="pb-2 border-bottom">Bücher Suchen</h2>
        <form method="GET" action="suchen.php">

            <!--Suchleiste-->
            <div class="input-group mb-3">
                <select class="form-select" name="search_column" style="max-width: 150px;">
                    <option value="kurztitle" <?php if ($search_column == 'kurztitle') echo 'selected'; ?>>Titel</option>
                    <option value="autor" <?php if ($search_column == 'autor') echo 'selected'; ?>>Autor</option>
                    <option value="katalog" <?php if ($search_column == 'katalog') echo 'selected'; ?>>Katalog</option>
                    <option value="kategorie" <?php if ($search_column == 'kategorie') echo 'selected'; ?>>Kategorie</option>
                    <option value="id" <?php if ($search_column == 'id') echo 'selected'; ?>>ID</option>
                </select>
                <input type="text" class="form-control" name="search" placeholder="Nach Bücher suchen" value="<?php echo htmlspecialchars($suche); ?>">
                <button class="btn btn-outline-secondary" type="submit">Suchen</button>
            </div>

            <!--Sortierleiste-->
            <div class="input-group mb-3">
                <label class="input-group-text" for="sort_column">Sort by</label>
                <select class="form-select" id="sort_column" name="sort_column">
                    <option value="kurztitle" <?php if ($sortier_collumns == 'kurztitle') echo 'selected'; ?>>Titel</option>
                    <option value="autor" <?php if ($sortier_collumns == 'autor') echo 'selected'; ?>>Autor</option>
                    <option value="katalog" <?php if ($sortier_collumns == 'katalog') echo 'selected'; ?>>Katalog</option>
                    <option value="kategorie" <?php if ($sortier_collumns == 'kategorie') echo 'selected'; ?>>Kategorie</option>
                    <option value="id" <?php if ($sortier_collumns == 'id') echo 'selected'; ?>>ID</option>
                </select>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="ASC" <?php if ($ASCDESC == 'ASC') echo 'selected'; ?>>Aufsteigend</option>
                    <option value="DESC" <?php if ($ASCDESC == 'DESC') echo 'selected'; ?>>Absteigend</option>
                </select>
            </div>

        </form>

        <!--Tabelle-->
        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Titel</th>
                <th scope="col">Autor</th>
                <th scope="col">Katalog</th>
                <th scope="col">Kategorie</th>
                <th scope="col">Cover</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($books as $book) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['id']); ?></td>
                    <td><a href="buchDetails.php?id=<?php echo $book['id']; ?>">
                            <?php echo htmlspecialchars(substr($book['kurztitle'], 0, 35)); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($book['autor']); ?></td>
                    <td><?php echo htmlspecialchars($book['katalog'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($book['kategorie'] ?? ''); ?></td>
                    <td><img src="images/book.jpg" alt="Book Cover" width="50"></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <!--Page navigation-->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <!-- Previous button -->
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php if ($page <= 1) { echo '#'; } else { echo "suchen.php?search=".urlencode($suche)."&search_column=".$search_column."&sort_column=".$sortier_collumns."&sort_order=".$ASCDESC."&page=".($page-1); } ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- First page -->
                <?php if ($page > 4): ?>
                    <li class="page-item">
                        <a class="page-link" href="suchen.php?search=<?php echo urlencode($suche); ?>&search_column=<?php echo $search_column; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>&page=1">1</a>
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
                        <a class="page-link" href="suchen.php?search=<?php echo urlencode($suche); ?>&search_column=<?php echo $search_column; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Last pages -->
                <?php if ($page < $total_pages - 3): ?>
                    <?php if ($page < $total_pages - 4): ?>
                        <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="suchen.php?search=<?php echo urlencode($suche); ?>&search_column=<?php echo $search_column; ?>&sort_column=<?php echo $sortier_collumns; ?>&sort_order=<?php echo $ASCDESC; ?>&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next button -->
                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php if ($page >= $total_pages) { echo '#'; } else { echo "suchen.php?search=".urlencode($suche)."&search_column=".$search_column."&sort_column=".$sortier_collumns."&sort_order=".$ASCDESC."&page=".($page+1); } ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

<?php include 'elementeWebseite/footer.php'; ?>