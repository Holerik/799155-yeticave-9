USE YetiCave;

INSERT INTO categories
SET name = 'Доски и лыжи', code = 'boards';
INSERT INTO categories
SET name = 'Крепления', code = 'attachment';
INSERT INTO categories
SET name = 'Ботинки', code = 'boots';
INSERT INTO categories
SET name = 'Одежда', code = 'clothing';
INSERT INTO categories
SET name = 'Инструменты', code = 'tools';
INSERT INTO categories
SET name = 'Разное', code = 'other';

INSERT INTO users
SET email = 'i_ivanov@gmail.com', name = 'ivanov24', password = 'gh34ju56q', info = 'https://vk.com/page-87938575_51386471';
INSERT INTO users
SET email = 'p_petrov@rambler.ru', name = 'petr_iv', password = 'whj545lkw08', info = 'https://ok.ru/petr_petrov';
INSERT INTO users
SET email ='irina_vlad@mail.ru', name = 'flower11', password = 'gfds13io84t', info = 'https://ok.ru/irina_flower11';
INSERT INTO users
SET email ='pavel1278@mail.ru', name = 'pavel1278@mail', password = 'gert54vb12qw', info = 'https://ok.ru/pave157';

INSERT INTO lots
SET name = 'Беговые лыжи FISCHER RCS Skate Jr', descr = 'Профессиональная гоночная модель от компании FISCHER для юниоров и юных лыжников. Модель является третьей гоночной моделью в линейке, обладает гоночной скользящей поверхностью, уступая старшим братьям по весовым показателям.', 
img_url = 'img/fisher_rcs.jpg',  price = '8000', rate_step = '100', cat_id = '1', autor_id = '1';
INSERT INTO lots
SET name = 'Ботинки лыжные ATOMIC Pro Skate Prolink', descr = 'Отличная модель для активных лыжников и амбициозных лыжников, предпочитающих передвижение коньковым ходом.',
img_url = 'img/atomic_pro.jpg',  price = '4000', rate_step = '100', cat_id = '3', autor_id = '1';
INSERT INTO lots
SET name = 'Палатка Trek Planet Alaska 3', descr = 'Трехместная двухслойная камуфляжная палатка Alaska 3 имеет удобный тамбур для вещей.', 
img_url = 'img/alaska_3.jpg', price = '2000', rate_step = '50', cat_id = '6', autor_id = '2';

INSERT INTO rates
SET price = '6000', user_id = '3', lot_id = '1';
INSERT INTO rates
SET price = '1000', user_id = '4', lot_id = '3';

SELECT * FROM categories;

SELECT l.name, l.price, img_url, r.price, c.name FROM lots l
JOIN rates r ON r.lot_id = l.key_id 
JOIN categories c ON l.cat_id = c.key_id
WHERE dt_fin IS NULL;

SELECT l.name, c.name FROM lots l
JOIN categories c ON l.cat_id = c.key_id;

UPDATE lots SET name = 'Палатка 3-х местная Lanyu 1677' WHERE key_id = 3;
UPDATE rates SET dt_add = CURRENT_TIMESTAMP WHERE key_id = 2;

SELECT r.price FROM rates r
JOIN lots l ON r.lot_id = l.key_id
WHERE r.dt_add > '2019-04-27';

