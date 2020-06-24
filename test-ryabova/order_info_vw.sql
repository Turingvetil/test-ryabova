/* Представление с подробной информацией о заказе */

create view order_info_vw as
select o.id order_id,
       o.client_id,
       c.name client_name,
       c.phone client_phone,
       o.delivery_date,
	   o.delivery_time_interval_id,
       dti.time_from,
       dti.time_till,
	   o.delivery_zone_id,
       dz.name delivery_zone_name,
       dz.cost delivery_cost,
	   o.address,
	   o.order_comment,
	   o.order_status_id,
       os.name order_status_name,
	   o.courier_id,
       cr.name courier_name,
       cr.phone courier_phone
  from delivery_order o
  join client c on c.id = o.client_id
  join delivery_time_interval dti on dti.id = o.delivery_time_interval_id
  join delivery_zone dz on dz.id = o.delivery_zone_id
  join order_status os on os.id = o.order_status_id
  left join courier cr on cr.id = o.courier_id;