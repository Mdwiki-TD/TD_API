<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDWiki API Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="theme.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <span class="navbar-brand">MDWiki API</span>
            <div class="d-flex align-items-center">
                <button class="theme-toggle btn" aria-label="Toggle theme">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>MDWiki API Test Interface</h1>

        <ul class="nav nav-tabs" id="endpointTabs" role="tablist">
            <!-- Tab headers will be generated here by script.js -->
        </ul>
        <div class="tab-content" id="endpointTabContent">
            <!-- Tab content will be generated here by script.js -->
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="theme.js"></script>
    <script src="script.js"></script>
</body>

</html>
