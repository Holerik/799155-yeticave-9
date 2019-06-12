<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title><?=$title;?></title>
<link href="../css/normalize.min.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/custom.css" rel="stylesheet">
<!-- Корректировка класса для размещения автара на главной странице -->
<style>
.main-header__search {
  position: relative;
  width: 380px;
  margin-right: 30px;
}

.main-header__search input[type="search"] {
  width: 100%;
  padding: 8px 19px 9px 19px;
  border: 1px solid #ffffff;
}

.main-header__search input[type="search"]:focus {
  border-color: #45abde;
}    
</style>
</head>
<body>
<div class="page-wrapper">
    <?=$header;?>
    <?=$content;?>
    <?=$footer;?>
</div>
</body>
</html>


