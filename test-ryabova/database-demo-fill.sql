/* Заполняем таблицы данными для демонстрации и тестирования */

/* Товары */
insert into product(article, name, cost)
	values ('ПР0001', 'Молоко Домик в деревне', 70.9);
insert into product(article, name, cost)
	values ('ОД0302', 'Носки красные мужские', 150);
insert into product(article, name, cost)
	values ('ТХ1966', 'Наушники AirPods', 780);
insert into product(article, name, cost)
	values ('КС7788-01', 'Тушь Maybelline', 450);
insert into product(article, name, cost)
	values ('КС1101', 'Духи YSL Opium', 1200);

/* Клиенты */
insert into client (name, phone)
	values ('Смирнов Андрей Витальевич', 9031112233);
insert into client (name, phone)
	values ('Кузнецов Иван Николаевич', 9261112200);
insert into client (name, phone)
	values ('Павлов Сергей Дмитриевич', 9154112233);
insert into client (name, phone)
	values ('Соколова Анна Макаровна', 9156112233);

/* Курьеры */
insert into courier (name, phone)
	values ('Иванов Федор Михайлович', 9030002233);
insert into courier (name, phone)
	values ('Скоблов Александр Алексеевич', 9130005678);


/* Таблицы заказов заполним с помощью нашего API */