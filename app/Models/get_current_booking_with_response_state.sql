SELECT
    booking.id, booking.order_id, booking_state.real_table_id,
    booking_state.tool_id,booking_state.t_name, booking_state.units, booking_state.date_from, booking_state.date_to, booking_state.pick_time, booking_state.drop_time,
    booking_state.payment_id,booking_state.amount, booking_state.response, booking_state.message,booking_state.payment_type, booking_state.order_status,
    booking_state.table_id, booking_state.creation_date, booking_state.updation_date,
    booking_state.is_outgoing, booking_state.is_incoming, booking_state.is_past_due, booking_state.rental_status,
    user_info.customer_id, user_info.payment_method_id , user_info.f_name, user_info.l_name, user_info.email, user_info.phone, user_info.address,
    user_info.city_id, user_info.state, user_info.country_id, user_info.zip, user_info.type_id, user_info.user_id as user_table_id,user_info.guest_id as guest_table_id,
    tblPaymentStatus.type AS PaymentStatus
FROM tblrinfo AS booking

         LEFT JOIN (
    SELECT
        booking_split.order_id,
        GROUP_CONCAT( booking_split.response SEPARATOR ' , ') AS response,
        GROUP_CONCAT( booking_split.message SEPARATOR ' , ') AS message,
        GROUP_CONCAT( booking_split.real_table_id SEPARATOR ' , ') AS real_table_id,
        GROUP_CONCAT( booking_split.payment_type SEPARATOR ' , ') AS payment_type,
        GROUP_CONCAT( booking_split.order_status SEPARATOR ' , ') AS order_status,
        GROUP_CONCAT( booking_split.table_id SEPARATOR ' , ' ) AS table_id,
        GROUP_CONCAT(booking_split.payment_id SEPARATOR ' , ') AS payment_id,
        GROUP_CONCAT( booking_split.tool_id SEPARATOR ' , ' ) AS tool_id,
        GROUP_CONCAT( booking_split.t_name SEPARATOR ' , ' ) AS t_name,
        GROUP_CONCAT( booking_split.units SEPARATOR ' , ' ) AS units,
        GROUP_CONCAT( booking_split.date_from SEPARATOR ' , ' ) AS date_from,
        GROUP_CONCAT( booking_split.date_to SEPARATOR ' , ' ) AS date_to,
        GROUP_CONCAT( booking_split.pick_time SEPARATOR ' , ' ) AS pick_time,
        GROUP_CONCAT( booking_split.drop_time SEPARATOR ' , ' ) AS drop_time,
        GROUP_CONCAT( booking_split.rental_status SEPARATOR ' , ' ) AS rental_status,
        GROUP_CONCAT( booking_split.amount SEPARATOR ' , ' ) AS amount,
        GROUP_CONCAT( booking_split.is_outgoing SEPARATOR ' , ' ) AS is_outgoing,
        GROUP_CONCAT( booking_split.is_incoming SEPARATOR ' , ' ) AS is_incoming,
        GROUP_CONCAT( booking_split.is_past_due SEPARATOR ' , ' ) AS is_past_due,
        GROUP_CONCAT( booking_split.creation_date SEPARATOR ' , ' ) AS creation_date,
        GROUP_CONCAT( booking_split.updation_date SEPARATOR ' , ' ) AS updation_date

    FROM (
             SELECT
                 original_mod_split.id,
                 original_mod_split.payment_id,
                 original_mod_split.real_table_id,
                 original_mod_split.message,
                 original_mod_split.payment_type,
                 transactions.response,
                 original_mod_split.order_id,
                 original_mod_split.table_id,
                 original_mod_split.tool_id,
                 tbltool.t_name,
                 original_mod_split.units,
                 original_mod_split.date_from,
                 original_mod_split.date_to,
                 original_mod_split.pick_time,
                 original_mod_split.drop_time,
                 original_mod_split.amount,
                 original_mod_split.rental_status,
                 original_mod_split.order_status,
                 original_mod_split.creation_date,
                 original_mod_split.updation_date,

                 (
                         STR_TO_DATE( original_mod_split.date_from, '%Y-%m-%d' )
                         =
                         CURRENT_DATE
                     ) AS is_outgoing,

                 (
                         STR_TO_DATE( original_mod_split.date_to, '%Y-%m-%d' )
                         =
                         CURRENT_DATE
                     ) AS is_incoming,

                 (
                         (
                                 STR_TO_DATE( original_mod_split.date_to, '%Y-%m-%d' )
                                 < CURRENT_DATE
                             )
                         AND
                         (
                                 original_mod_split.rental_status
                                 <
                                 4
                             )
                     ) AS is_past_due

             FROM (
                      SELECT
                          original_mod_state.id,
                          original_mod_state.order_id,
                          original_mod_state.table_id,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.payment_type, ' , ', tally.n ), ' , ', -1 ) AS payment_type,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.id, ' , ', tally.n ), ' , ', -1 ) AS real_table_id,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.message, ' , ', tally.n ), ' , ', -1 ) AS message,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.tool_id, ' , ', tally.n ), ' , ', -1 ) AS tool_id,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.units, ' , ', tally.n ), ' , ', -1 ) AS units,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.date_from, ' , ', tally.n ), ' , ', -1 ) AS date_from,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.date_to, ' , ', tally.n ), ' , ', -1 ) AS date_to,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.pick_time, ' , ', tally.n ), ' , ', -1 ) AS pick_time,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.drop_time, ' , ', tally.n ), ' , ', -1 ) AS drop_time,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.payment_ids, ' , ', tally.n ), ' , ', -1 ) AS payment_id,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.total_amount, ' , ', tally.n ), ' , ', -1 ) AS amount,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.rental_status, ' , ', tally.n ), ' , ', -1 ) AS rental_status,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.order_status, ' , ', tally.n ), ' , ', -1 ) AS order_status,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.CreationDate, ' , ', tally.n ), ' , ', -1 ) AS creation_date,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.UpdationDate, ' , ', tally.n ), ' , ', -1 ) AS updation_date

                      FROM (
                               SELECT o.id,o.message , o.payment_type, o.order_id, o.tool_id, o.units, o.date_from, o.date_to, o.pick_time, o.drop_time,o.payment_ids,o.total_amount, o.status AS rental_status, o.order_status, 1 AS table_id, o.CreationDate, o.UpdationDate
                               FROM tblrinfo AS o

                               UNION ALL

                               SELECT m.id, m.message , m.payment_type, m.order_id, m.tool_id, m.units, m.date_from, m.date_to, m.pick_time, m.drop_time,m.payment_ids,m.total_amount, m.status AS rental_status, m.order_status, 2 AS table_id , m.CreationDate, m.UpdationDate
                               FROM tblmodorders AS m
                           ) AS original_mod_state

                               JOIN tally ON ( ROUND( ( CHAR_LENGTH( original_mod_state.tool_id ) - CHAR_LENGTH( REPLACE( original_mod_state.tool_id, ' , ', '' ) ) ) / CHAR_LENGTH( ' , ' ) ) >= tally.n - 1 )
                  ) AS original_mod_split

                      LEFT JOIN (
                 SELECT
                     refund_state.id,
                     refund_state.order_id,
                     SUBSTRING_INDEX( SUBSTRING_INDEX( tool_id, ' , ', tally.n ), ' , ', -1 ) AS tool_id,
                     SUBSTRING_INDEX( SUBSTRING_INDEX( units, ' , ', tally.n ), ' , ', -1 ) AS units,
                     SUBSTRING_INDEX( SUBSTRING_INDEX( payment_id, ' , ', tally.n ), ' , ', -1 ) AS payment_id ,
                     SUBSTRING_INDEX( SUBSTRING_INDEX( refundFromTable, ' , ', tally.n ), ' , ', -1 ) AS refundFromTable,
                     SUBSTRING_INDEX( SUBSTRING_INDEX( refundIdFromTable, ' , ', tally.n ), ' , ', -1 ) AS refundIdFromTable

                 FROM (
                          SELECT r.id, r.order_id,r_concat.payment_id, r_concat.tool_id, r_concat.units, r_concat.refundFromTable, r_concat.refundIdFromTable
                          FROM tblrefundorder AS r

                                   INNER JOIN (
                              SELECT
                                  order_id,
                                  GROUP_CONCAT( tool_id SEPARATOR ' , ' ) AS tool_id,
                                  GROUP_CONCAT( units SEPARATOR ' , ' ) AS units,
                                  GROUP_CONCAT( payment_ids SEPARATOR ' , ')AS payment_id ,
                                  GROUP_CONCAT( refundFromTable SEPARATOR ' , ' ) AS refundFromTable,
                                  GROUP_CONCAT( refundIdFromTable SEPARATOR ' , ' ) AS refundIdFromTable
                              FROM tblrefundorder

                              GROUP BY order_id
                          ) AS r_concat
                                              ON( r_concat.order_id = r.order_id )
                      ) AS refund_state

                          JOIN tally ON ( ROUND( ( CHAR_LENGTH( refund_state.tool_id ) - CHAR_LENGTH( REPLACE( refund_state.tool_id, ' , ', '' ) ) ) / CHAR_LENGTH( ' , ' ) ) >= tally.n - 1 )

             ) AS refund_split
                                ON ( ( refund_split.order_id = original_mod_split.order_id ) AND ( refund_split.refundFromTable = original_mod_split.table_id ) AND ( refund_split.refundIdFromTable = original_mod_split.id ) AND ( refund_split.tool_id = original_mod_split.tool_id ) )
                      JOIN transactions ON (original_mod_split.payment_id = transactions.id)
                      LEFT JOIN tbltool ON (original_mod_split.tool_id = tbltool.id)

             WHERE(
                      ( ( refund_split.tool_id IS NULL ) OR ( (CONVERT( original_mod_split.units, UNSIGNED INTEGER ) - CONVERT( refund_split.units, UNSIGNED INTEGER )) > 0 ) )
                      )
         ) AS booking_split

    GROUP BY order_id
) AS booking_state
                   ON booking_state.order_id = booking.order_id

         LEFT JOIN (
    SELECT tblrinfo.id,
           tblrinfo.user_id,
           tblrinfo.guest_id,
           u.f_name,
           u.l_name,
           u.email,
           u.phone ,
           u.address,
           u.city_id,
           s.s_full AS state,
           u.country_id,
           u.zip,
           u.type_id,
           stripe.customerId AS customer_id,
           stripe.paymentMethodId AS payment_method_id
    FROM tblrinfo
             LEFT JOIN tblpinfo AS u ON ( u.u_id = tblrinfo.user_id )
             LEFT JOIN tblstripeCustomers AS stripe ON (stripe.user_id = tblrinfo.user_id)
             LEFT JOIN tblstate AS s ON (s.id = u.state_id)
    WHERE tblrinfo.user_id IS NOT NULL

    UNION ALL

    SELECT tblrinfo.id,
           tblrinfo.user_id,
           tblrinfo.guest_id,
           g.f_name,
           g.l_name,
           g.email,
           g.phone,
           g.address,
           g.city_id,
           s.s_full AS state,
           g.country_id,
           g.zip,
           g.type_id,
           stripe.customerId AS customer_id,
           stripe.paymentMethodId AS payment_method_id
    FROM tblrinfo
             LEFT JOIN tblguest AS g ON ( g.id = tblrinfo.guest_id )
             LEFT JOIN tblstripeCustomers AS stripe ON (stripe.user_id = tblrinfo.guest_id)
             LEFT JOIN tblstate AS s ON (s.id = g.state_id)
    WHERE tblrinfo.guest_id IS NOT NULL

) AS user_info
                   ON ( user_info.id = booking.id )

         INNER JOIN tblPaymentStatus ON (
        SUBSTRING(
                booking.payment_status,
                CHAR_LENGTH( booking.payment_status ),
                CHAR_LENGTH( booking.payment_status )
            ) = tblPaymentStatus.id
    )

