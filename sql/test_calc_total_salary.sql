-- SELECT *,(IF TRUE,(SELECT @prev_diff:=1),0) FROM (SELECT start_time, end_time, start_employee_type_id,end_employee_type_id,id_diff,next_row_id FROM (SELECT `_tmp_result1`.time AS start_time, `_tmp_result2`.time AS end_time, `_tmp_result1`.employee_type_id AS start_employee_type_id, `_tmp_result2`.employee_type_id AS end_employee_type_id, next_row_id, `_tmp_result1`.curr_row_id, (_tmp_result2.`next_row_id` - _tmp_result1.`curr_row_id`) AS id_diff FROM (SELECT i.*, (@row_num:=@row_num+1) AS curr_row_id FROM (SELECT * FROM `time_sheet`WHERE user_id = 1 ORDER BY `time`) AS i, (SELECT @row_num:=0) AS tmp_num, (SELECT @next_row_num:=1) AS next_tmp_num) AS _tmp_result1 INNER JOIN (SELECT i.*, (@next_row_num:=@next_row_num+1) AS next_row_id FROM (SELECT * FROM `time_sheet` WHERE user_id = 1 ORDER BY `time` LIMIT 1,100000) AS i, (SELECT @row_num:=0) AS tmp_num, (SELECT @next_row_num:=1) AS next_tmp_num) AS _tmp_result2 WHERE _tmp_result1.state='working' AND _tmp_result2.state='left_work' AND next_row_id - curr_row_id = 1) AS _tmp_result3) AS work_time_result, (SELECT @row_num_2:=0) AS x,(SELECT @prev_diff:=9999999) AS y;


SELECT *,wage*diff_time/3600 AS income FROM (SELECT start_time, end_time,TIMESTAMPDIFF(SECOND,start_time,end_time) AS diff_time, start_employee_type_id AS employee_type_id,id_diff,next_row_id FROM 
    (SELECT `_tmp_result1`.time AS start_time, `_tmp_result2`.time AS end_time, `_tmp_result1`.employee_type_id AS start_employee_type_id, `_tmp_result2`.employee_type_id AS end_employee_type_id, next_row_id, `_tmp_result1`.curr_row_id, (_tmp_result2.`next_row_id` - _tmp_result1.`curr_row_id`) AS id_diff FROM (SELECT i.*, (@row_num:=@row_num+1) AS curr_row_id FROM 
    -- The below user id can be selected by :user_id.
    (SELECT * FROM `time_sheet`WHERE user_id = 1 ORDER BY `time`) AS i, 
    (SELECT @row_num:=0) AS tmp_num, 
    (SELECT @next_row_num:=1) AS next_tmp_num) AS _tmp_result1 
    INNER JOIN (SELECT i.*, (@next_row_num:=@next_row_num+1) AS next_row_id FROM 
        (SELECT * FROM `time_sheet` WHERE user_id = 1 ORDER BY `time` LIMIT 1,1000000) AS i, 
        (SELECT @row_num:=0) AS tmp_num, 
        (SELECT @next_row_num:=1) AS next_tmp_num) AS _tmp_result2 WHERE _tmp_result1.state='working' AND _tmp_result2.state='left_work' AND next_row_id - curr_row_id = 1) AS _tmp_result3 WHERE 
        -- Removing inconsistent record; An occuaption at the start and an occupation at the end is different.
        start_employee_type_id=end_employee_type_id) AS working_time JOIN occupation USING (employee_type_id);