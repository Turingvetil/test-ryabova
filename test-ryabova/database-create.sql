/* Создаем базу данных */
create database delivery_api default character set utf8 default collate utf8_general_ci;

/* Далее весь код выполняется в базе данных delivery_api */
/* Создаем таблицы. Справочники заполняем сразу */
create table delivery_zone (
	 id int(3) not null auto_increment,
     name varchar(100) not null comment 'Название',
	 cost decimal(10,2) not null comment 'Стоимость доставки в зону, руб.',
	 primary key (id),
	 unique delivery_zone_name_uq (name)
	)
	comment = 'Зоны доставки';

insert into delivery_zone (name, cost)
	values ('Центр', 100);
insert into delivery_zone (name, cost)
	values ('Окраина', 150);
insert into delivery_zone (name, cost)
	values ('За городом', 200);

create table product (
	 id int not null auto_increment,
     article varchar(50) not null comment 'Артикул',
	 name varchar(500) not null comment 'Название',
	 cost decimal(10,2) not null comment 'Цена, руб.',
	 primary key (id),
	 unique product_article_uq (article)
	)
	comment = 'Товары';

create table client (
	 id int not null auto_increment,
     name varchar(500) not null comment 'ФИО',
	 phone varchar(10) not null comment 'Телефон',
	 primary key (id),
	 unique client_phone_uq (phone)
	)
	comment = 'Клиенты';
	
create table delivery_time_interval (
	 id int(2) not null auto_increment,
	 time_from time not null comment 'Время начала интервала',
	 time_till time not null comment 'Время окончания интервала',
	 primary key (id)
	)
	comment = 'Интервалы времени доставки';

insert into delivery_time_interval (time_from, time_till)
	values ('08:00:00','12:00:00');
insert into delivery_time_interval (time_from, time_till)
	values ('12:00:00','16:00:00');
insert into delivery_time_interval (time_from, time_till)
	values ('16:00:00','20:00:00');

create table courier (
	 id int not null auto_increment,
     name varchar(500) not null comment 'ФИО',
	 phone varchar(10) not null comment 'Телефон',
	 primary key (id),
	 unique courier_phone_uq (phone)
	)
	comment = 'Курьеры';

create table order_status (
	 id int(2) not null auto_increment,
     name varchar(100) not null comment 'Название',
	 primary key (id),
	 unique order_status_name_uq (name)
	)
	comment = 'Статусы заказа';

insert into order_status (name)
	values ('Создан');
insert into order_status (name)
	values ('Выполнен');
insert into order_status (name)
	values ('Отменён');

create table delivery_order (
	 id int not null auto_increment,
     client_id int not null comment 'id клиента',
	 delivery_date date not null comment 'Дата доставки',
	 delivery_time_interval_id int(2) comment 'id интервала времени доставки',
	 delivery_zone_id int(3) not null comment 'id зоны доставки',
	 address varchar(500) not null comment 'Адрес',
	 order_comment varchar(2000) comment 'Комментарий к заказу',
	 order_status_id int(2) not null default 1 comment 'Статус заказа',
	 courier_id int comment 'id курьера',
	 primary key (id),
	 constraint delivery_order_client_fk foreign key (client_id) references client (id),
	 constraint delivery_order_time_fk foreign key (delivery_time_interval_id) references delivery_time_interval (id),
	 constraint delivery_order_zone_fk foreign key (delivery_zone_id) references delivery_zone (id),
	 constraint delivery_order_courier_fk foreign key (courier_id) references courier (id),
	 constraint delivery_order_status_fk foreign key (order_status_id) references order_status (id)
	)
	comment = 'Заказы';

create table delivery_order_product (
	 delivery_order_id int not null comment 'id заказа',
	 product_id int not null comment 'id товара',
	 amount int not null comment 'Количество товара в заказе, шт',
	 primary key (delivery_order_id, product_id),
	 constraint delivery_order_fk foreign key (delivery_order_id) references delivery_order (id),
	 constraint delivery_order_product_fk foreign key (product_id) references product (id)
	)
	comment = 'Состав заказа';