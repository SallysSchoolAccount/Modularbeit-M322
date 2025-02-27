<?php
//Head und Header
include 'elementeWebseite/header.php';

// Verbindung Datenbank
include 'elementeWebseite/database_connection.php';

// Variabeln initialisieren
$search_query = '';
$sort_order = 'ASC';
$sort_column = 'kurztitle';
$limit = 20;
$page = 1;

// Sachen fürs Form
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['search'])) {
        $search_query = $_GET['search'];
    }
    if (isset($_GET['sort_column'])) {
        $sort_column = $_GET['sort_column'];
    }
    if (isset($_GET['sort_order'])) {
        $sort_order = $_GET['sort_order'];
    }
    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
    }
}

$offset = ($page - 1)* $limit;

// MySQL befehl
$query = "SELECT * FROM buecher WHERE kurztitle LIKE ? ORDER BY $sort_column $sort_order LIMIT ? OFFSET ?";
echo $query." ".$offset;
//Vorbereiten
$stmt = $conn->prepare($query);

$search_param = '%' . $search_query . '%';
$stmt->bind_param("sdd", $search_param, $limit, $offset);

//Ausfuehren
$stmt->execute();

//resultat als variable speichern
// TODO Cant reach this
$result = $stmt->get_result();
echo $result.
$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

//Rechnet die nummer aller
$total_query = "SELECT COUNT(*) as total FROM buecher WHERE kurztitle LIKE ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("s", $search_param);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_entries = $total_row['total'];
$total_pages = ceil($total_entries / $limit);

?>

    <div class="container">

        <!--Suchleiste-->
        <h2 class="pb-2 border-bottom">Bücher Suchen</h2>
        <form method="GET" action="suchen.php">
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="search" placeholder="Nach Bücher suchen" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-outline-secondary" type="submit">Suchen</button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text" for="sort_column">Sort by</label>
                <select class="form-select" id="sort_column" name="sort_column">
                    <option value="kurztitle" <?php if ($sort_column == 'kurztitle') echo 'selected'; ?>>Titel</option>
                    <option value="autor" <?php if ($sort_column == 'autor') echo 'selected'; ?>>Autor</option>
                </select>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Aufsteigend</option>
                    <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Absteigend</option>
                </select>
            </div>
        </form>
        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th scope="col">Titel</th>
                <th scope="col">Autor</th>
                <th scope="col">Cover</th>
            </tr>
            </thead>
            <tbody>
            <?php echo "Hello";?>

            <!--TODO cant reach this-->
            <?php foreach ($books as $book) { ?>
                <tr>
                    <td><a href="buchDetails.php?id=<?php echo $book['id']; ?>">
                            <?php echo htmlspecialchars(substr($book['kurztitle'], 0, 35)); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($book['autor']); ?></td>
                    <td><img src="images/book.jpg" alt="Book Cover" width="50"></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <!--Page navigation-->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="suchen.php?search=<?php echo urlencode($search_query); ?>
                            &sort_column=<?php echo $sort_column; ?>
                            &sort_order=<?php echo $sort_order; ?>
                            &page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>

<!--Footer-->
<?php include 'elementeWebseite/footer.php'; ?>