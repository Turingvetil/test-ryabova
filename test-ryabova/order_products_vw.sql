/* Представление с подробной информацией о товарах в заказе */

create view order_products_vw as
select op.delivery_order_id order_id,
	   op.product_id,
       p.article,
       p.name,
       p.cost,
       op.amount
  from delivery_order_product op
  join product p on op.product_id = p.id;