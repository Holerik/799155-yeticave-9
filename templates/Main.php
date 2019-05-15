<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
        <ul class="promo__list">
            <!--заполните этот список из массива категорий-->
            <?php foreach ($catsArray as $cats): ?>
                <li class="promo__item promo__item--<?=$cats['code'];?>">
                    <a class="promo__link" href="all-lots.php?cat_id=<?=$cats['id'];?>&user_id=<?=$user_id;?>"><?=$cats['name'];?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <!--заполните этот список из массива с товарами-->
            <?php foreach ($catsInfoArray as $catsInfo): ?>
            <li class="lots__item lot">
                <div class="lot__image">
                    <img src="<?=$catsInfo['URL картинки'];?>" width="350" height="260" alt="">
                </div>
                <div class="lot__info">
                    <span class="lot__category"><?=htmlspecialchars($catsInfo['Категория']);?></span>

                    <h3 class="lot__title"><a class="text-link" href="lot.php?lot_id=<?=$catsInfo['lot_id'];?>&user_id=<?=$user_id;?>"><?=htmlspecialchars($catsInfo['Название']);?></a></h3>

                    <div class="lot__state">
                        <div class="lot__rate">
                            <span class="lot__amount">Стартовая цена</span>
                            <span class="lot__cost"><?=format_price($catsInfo['Цена']);?></span>
                        </div>
                        <?php
                            $time = remained_time($catsInfo['dt_fin']);
                        ?>
                        <div class="lot__timer timer <?php if ($time[0] <= 1):?>timer--finishing<?php endif; ?>">
                            <?php 
                                echo($time[0] . ":" . $time[1]);
                            ?>
                        </div>
                    </div>
                </div>
            </li>
            <?php endforeach ?>
        </ul>
    </section>
</main>
