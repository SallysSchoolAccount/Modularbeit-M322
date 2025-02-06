<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Antiquariat Bieber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="otherStyling.css" rel="stylesheet">
    <style>
        body {
            background-color: black
        }

        .nav-link {
            color: darkgoldenrod;
        }
        .nav-link:hover {
            color: aliceblue;
        }

        .material-icons {
            color: wheat;
        }

        .btn-outline-primary {
            color: darkgoldenrod;
        }
        .pb-2 {
            color: whitesmoke;
        }

    </style>

</head>
<body>
<div class="container">
    <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
        <div class="col-md-3 mb-2 mb-md-0">
            <a href="/" class="d-inline-flex link-body-emphasis text-decoration-none">
                <i class="material-icons"
                   style="font-size: 40px;">
                    home
                </i>
            </a>
        </div>

        <!--Navbar-->
        <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0">
            <li><a href="Homepage.php" class="nav-link px-2">Home</a></li>
            <li><a href="#" class="nav-link px-2">Autoren</a></li>
            <li><a href="#" class="nav-link px-2">Suchen</a></li>
            <li><a href="überUns.php" class="nav-link px-2">über und</a></li>
        </ul>

        <!--Login-->
        <div class="col-md-3 text-end">
            <button type="button" class="btn btn-outline-primary me-2">Login</button>
        </div>
    </header>
</div>
</body>
</html>