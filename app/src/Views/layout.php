<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <?php include __DIR__ . '/partials/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
    
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="flex-shrink-0 mb-5">
        <div class="container">
            <?php include __DIR__ . '/partials/alerts.php'; ?>
            <?php require $viewPath; ?>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
