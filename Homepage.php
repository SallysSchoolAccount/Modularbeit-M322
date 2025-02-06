<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "books");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the database
$result = mysqli_query($conn, "SELECT * FROM buecher LIMIT 3");

// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$books = [];
while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}
?>

<!--Head & Header-->
<?php include 'elementeWebseite/header.php' ?>

<!--Bestseller Vorschau-->
<div class="container px-4 py-5" id="custom-cards">
    <h2 class="pb-2 border-bottom">Custom cards</h2>

    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">
        <?php foreach ($books as $index => $book) { ?>
            <div class="col">
                <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg"

                     <!--#TODO Frau Duc fragen--->
                     style="background-image: url();">
                    <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                        <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold"><?php echo $book['kurztitle']; ?></h3>
                        <ul class="d-flex list-unstyled mt-auto">
                            <!--Autor pic-->
                            <li class="me-auto">
                                <!--#TODO Change it with the author pic-->
                                <img src="images/placeholder.jpeg"
                                     alt="Bootstrap"
                                     width="32" height="32"
                                     class="rounded-circle border border-white">
                            </li>
                            <li class="d-flex align-items-center me-3">
                                <i class="material-icons me-2">person</i>
                                <small><?php echo $book['autor']; ?></small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

<!--Footer-->
<?php include 'elementeWebseite/footer.php' ?>