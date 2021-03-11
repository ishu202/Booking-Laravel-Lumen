SET group_concat_max_len = 100000000;
SELECT
    booking.id, booking.order_id,
    booking_state.tool_id, booking_state.units, booking_state.date_from, booking_state.date_to, booking_state.pick_time, booking_state.drop_time,
    booking_state.payment_id, booking_state.response,
    booking_state.is_outgoing, booking_state.is_incoming, booking_state.is_past_due, booking_state.rental_status,
    user_info.customer_id, user_info.payment_method_id , user_info.f_name, user_info.l_name, user_info.email, user_info.phone, user_info.address,
    user_info.city, user_info.state, user_info.Country, user_info.zip, user_info.type_id,
    tblPaymentStatus.type AS PaymentStatus
FROM tblrinfo AS booking

         LEFT JOIN (
    SELECT
        booking_split.order_id,
        GROUP_CONCAT( booking_split.response SEPARATOR ' , ') AS response,
        GROUP_CONCAT( booking_split.table_id SEPARATOR ' , ' ) AS table_id,
        GROUP_CONCAT(booking_split.payment_id SEPARATOR ' , ') AS payment_id,
        GROUP_CONCAT( booking_split.tool_id SEPARATOR ' , ' ) AS tool_id,
        GROUP_CONCAT( booking_split.units SEPARATOR ' , ' ) AS units,
        GROUP_CONCAT( booking_split.date_from SEPARATOR ' , ' ) AS date_from,
        GROUP_CONCAT( booking_split.date_to SEPARATOR ' , ' ) AS date_to,
        GROUP_CONCAT( booking_split.pick_time SEPARATOR ' , ' ) AS pick_time,
        GROUP_CONCAT( booking_split.drop_time SEPARATOR ' , ' ) AS drop_time,
        GROUP_CONCAT( booking_split.rental_status SEPARATOR ' , ' ) AS rental_status,

        GROUP_CONCAT( booking_split.is_outgoing SEPARATOR ' , ' ) AS is_outgoing,
        GROUP_CONCAT( booking_split.is_incoming SEPARATOR ' , ' ) AS is_incoming,
        GROUP_CONCAT( booking_split.is_past_due SEPARATOR ' , ' ) AS is_past_due

    FROM (
             SELECT
                 original_mod_split.id,
                 original_mod_split.payment_id,
                 transactions.response,
                 original_mod_split.order_id,
                 original_mod_split.table_id,
                 original_mod_split.tool_id,
                 original_mod_split.units,
                 original_mod_split.date_from,
                 original_mod_split.date_to,
                 original_mod_split.pick_time,
                 original_mod_split.drop_time,
                 original_mod_split.rental_status,

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
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.tool_id, ' , ', tally.n ), ' , ', -1 ) AS tool_id,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.units, ' , ', tally.n ), ' , ', -1 ) AS units,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.date_from, ' , ', tally.n ), ' , ', -1 ) AS date_from,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.date_to, ' , ', tally.n ), ' , ', -1 ) AS date_to,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.pick_time, ' , ', tally.n ), ' , ', -1 ) AS pick_time,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.drop_time, ' , ', tally.n ), ' , ', -1 ) AS drop_time,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.payment_ids, ' , ', tally.n ), ' , ', -1 ) AS payment_id,
                          SUBSTRING_INDEX( SUBSTRING_INDEX( original_mod_state.rental_status, ' , ', tally.n ), ' , ', -1 ) AS rental_status

                      FROM (
                               SELECT o.id, o.order_id, o.tool_id, o.units, o.date_from, o.date_to, o.pick_time, o.drop_time,o.payment_ids, o.status AS rental_status, 1 AS table_id
                               FROM tblrinfo AS o

                               UNION ALL

                               SELECT m.id, m.order_id, m.tool_id, m.units, m.date_from, m.date_to, m.pick_time, m.drop_time,m.payment_ids, m.status AS rental_status, 2 AS table_id
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
                          JOIN transactions ON refund_state.payment_id = transactions.id

             ) AS refund_split
                                ON ( ( refund_split.order_id = original_mod_split.order_id ) AND ( refund_split.refundFromTable = original_mod_split.table_id ) AND ( refund_split.refundIdFromTable = original_mod_split.id ) AND ( refund_split.tool_id = original_mod_split.tool_id ) AND ( refund_split.payment_id = original_mod_split.payment_id ) )
                      JOIN transactions ON original_mod_split.payment_id = transactions.id

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
           ci.city,
           s.s_full AS state,
           c.country,
           u.zip,
           u.type_id,
           stripe.customerId AS customer_id,
           stripe.paymentMethodId AS payment_method_id
    FROM tblrinfo
             LEFT JOIN tblpinfo AS u ON ( u.u_id = tblrinfo.user_id )
             LEFT JOIN tblstripeCustomers AS stripe ON (stripe.user_id = tblrinfo.user_id)
             LEFT JOIN tblstate AS s ON (s.id = u.state_id)
             LEFT JOIN tblcountry AS c ON (c.id = u.country_id)
             LEFT JOIN tblcities AS ci ON (u.city_id = ci.id)
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
           ci.city,
           s.s_full AS state,
           c.country,
           g.zip,
           g.type_id,
           stripe.customerId AS customer_id,
           stripe.paymentMethodId AS payment_method_id
    FROM tblrinfo
             LEFT JOIN tblguest AS g ON ( g.id = tblrinfo.guest_id )
             LEFT JOIN tblstripeCustomers AS stripe ON (stripe.user_id = tblrinfo.guest_id)
             LEFT JOIN tblstate AS s ON (s.id = g.state_id)
             LEFT JOIN tblcountry AS c ON (c.id = g.country_id)
             LEFT JOIN tblcities AS ci ON (g.city_id = ci.id)
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
