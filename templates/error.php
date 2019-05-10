<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?=$title;?></title>
    <link href="../css/normalize.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<main class="error-container">
    <div class="error-container__main-col">
    <header class="error-container__header">
        <h2 class="error-container__header-text">Ошибка</h2>
    </header>
    <article class="error-container__article">
        <p class="error-container__article-text"><?=$error;?></p>
    </article>
    </div>
</main>
</body>
</html>