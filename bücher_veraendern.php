<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

global $conn;
include 'elementeWebseite/database_connection.php';
include 'elementeWebseite/header.php';

// Variabili di paginazione e ordinamento
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$sortier_collumns = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'id';
$ASCDESC = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Query per ottenere i libri
$query = "SELECT b.*, k.kategorie, z.beschreibung 
          FROM buecher b 
          LEFT JOIN kategorien k ON b.kategorie = k.id 
          LEFT JOIN zustaende z ON b.zustand = z.zustand 
          ORDER BY $sortier_collumns $ASCDESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Conteggio totale per la paginazione
$total_query = "SELECT COUNT(*) as total FROM buecher";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit);
?>

    <div class="container">
        <h2 class="pb-2 border-bottom">Bücher Verwalten</h2>

        <!-- Form di ricerca -->
        <form method="GET" action="bücher_veraendern.php" class="mb-4">
            <div class="input-group mb-3">
                <select class="form-select" name="search_column" style="max-width: 150px;">
                    <option value="kurztitle" <?php echo (isset($_GET['search_column']) && $_GET['search_column'] == 'kurztitle') ? 'selected' : ''; ?>>Titel</option>
                    <option value="autor" <?php echo (isset($_GET['search_column']) && $_GET['search_column'] == 'autor') ? 'selected' : ''; ?>>Autor</option>
                    <option value="kategorie" <?php echo (isset($_GET['search_column']) && $_GET['search_column'] == 'kategorie') ? 'selected' : ''; ?>>Kategorie</option>
                    <option value="id" <?php echo (isset($_GET['search_column']) && $_GET['search_column'] == 'id') ? 'selected' : ''; ?>>ID</option>
                </select>
                <input type="text" class="form-control" name="search" placeholder="Nach Bücher suchen"
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-secondary" type="submit">Suchen</button>
            </div>

            <!-- Ordinamento -->
            <div class="input-group mb-3">
                <label class="input-group-text">Sortieren nach</label>
                <select class="form-select" name="sort_column">
                    <option value="kurztitle" <?php echo ($sortier_collumns == 'kurztitle') ? 'selected' : ''; ?>>Titel</option>
                    <option value="autor" <?php echo ($sortier_collumns == 'autor') ? 'selected' : ''; ?>>Autor</option>
                    <option value="kategorie" <?php echo ($sortier_collumns == 'kategorie') ? 'selected' : ''; ?>>Kategorie</option>
                    <option value="id" <?php echo ($sortier_collumns == 'id') ? 'selected' : ''; ?>>ID</option>
                </select>
                <select class="form-select" name="sort_order">
                    <option value="ASC" <?php echo ($ASCDESC == 'ASC') ? 'selected' : ''; ?>>Aufsteigend</option>
                    <option value="DESC" <?php echo ($ASCDESC == 'DESC') ? 'selected' : ''; ?>>Absteigend</option>
                </select>
            </div>
        </form>

        <div class="mb-3">
            <a href="buch_hinzufuegen.php" class="btn btn-primary">Neues Buch hinzufügen</a>
        </div>

        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Titel</th>
                <th scope="col">Autor</th>
                <th scope="col">Kategorie</th>
                <th scope="col">Zustand</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($book = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['id']); ?></td>
                    <td><?php echo htmlspecialchars($book['kurztitle']); ?></td>
                    <td><?php echo htmlspecialchars($book['autor']); ?></td>
                    <td><?php echo htmlspecialchars($book['kategorie']); ?></td>
                    <td><?php echo htmlspecialchars($book['beschreibung']); ?></td>
                    <td>
                        <a href="buch_bearbeiten.php?id=<?php echo $book['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="material-icons">edit</i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php } ?>

                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

<?php include 'elementeWebseite/footer.php'; ?>