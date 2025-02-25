DROP FUNCTION IF EXISTS `SET_CLASS_RANK_MP`;
DELIMITER $$
CREATE FUNCTION `SET_CLASS_RANK_MP`(
	mp_id int
) RETURNS int(11)
BEGIN

DECLARE done INT DEFAULT 0;
DECLARE marking_period_id INT;
DECLARE college_roll_no INT;
DECLARE rank NUMERIC;

declare cur1 cursor for
select
  mp.marking_period_id,
  sgc.college_roll_no,
 (select count(*)+1 
   from student_gpa_calculated sgc3
   where sgc3.gpa > sgc.gpa
     and sgc3.marking_period_id = mp.marking_period_id 
     and sgc3.college_roll_no in (select distinct sgc2.college_roll_no 
                                                from student_gpa_calculated sgc2, student_enrollment se2
                                                where sgc2.college_roll_no = se2.college_roll_no 
                                                and sgc2.marking_period_id = mp.marking_period_id 
                                                and se2.grade_id = se.grade_id
                                                and se2.syear = se.syear
                                                group by gpa
                                )
  ) as rank
  from student_enrollment se, student_gpa_calculated sgc, marking_periods mp
  where se.college_roll_no = sgc.college_roll_no
    and sgc.marking_period_id = mp.marking_period_id
    and mp.marking_period_id = mp_id
    and se.syear = mp.syear
    and not sgc.gpa is null
  order by grade_id, rank;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

open cur1;
fetch cur1 into marking_period_id,college_roll_no,rank;

while not done DO
	update student_gpa_calculated sgc
	  set
	    class_rank = rank
	where sgc.marking_period_id = marking_period_id
	  and sgc.college_roll_no = college_roll_no;
	fetch cur1 into marking_period_id,college_roll_no,rank;
END WHILE;
CLOSE cur1;

RETURN 1;
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `CALC_CUM_GPA_MP`;
DELIMITER $$
CREATE FUNCTION `CALC_CUM_GPA_MP`(
mp_id int
) RETURNS int(11)
BEGIN

DECLARE req_mp INT DEFAULT 0;
DECLARE done INT DEFAULT 0;
DECLARE gp_points DECIMAL(10,2);
DECLARE college_roll_no INT;
DECLARE gp_points_weighted DECIMAL(10,2);
DECLARE divisor DECIMAL(10,2);
DECLARE credit_earned DECIMAL(10,2);
DECLARE cgpa DECIMAL(10,2);

DECLARE cur1 CURSOR FOR
   SELECT srcg.college_roll_no,
                  IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned)),
                      IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned),
                         ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))
                        ))as gp_points,

                      SUM(srcg.weighted_gp*srcg.credit_earned) as gp_points_weighted,
                      SUM(srcg.credit_attempted) as divisor,
                      SUM(srcg.credit_earned) as credit_earned,
   		      IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned))/ sum(srcg.credit_attempted),
                          IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned)/sum(srcg.credit_attempted),
                             ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))/sum(srcg.credit_attempted)
                            )
                         ) as cgpa

            FROM marking_periods mp,temp_cum_gpa srcg
            INNER JOIN colleges sc ON sc.id=srcg.college_id
            WHERE srcg.marking_period_id= mp.marking_period_id AND srcg.gp_scale<>0 AND srcg.marking_period_id NOT LIKE 'E%'
            AND mp.marking_period_id IN (SELECT marking_period_id  FROM marking_periods WHERE mp_type=req_mp )
            GROUP BY srcg.college_roll_no;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;


  CREATE TEMPORARY TABLE tmp(
    college_roll_no int,
    sum_weighted_factors decimal(10,6),
    count_weighted_factors int,
    sum_unweighted_factors decimal(10,6),
    count_unweighted_factors int,
    grade_level_short varchar(10)
  );

  INSERT INTO tmp(college_roll_no,sum_weighted_factors,count_weighted_factors,
    sum_unweighted_factors, count_unweighted_factors,grade_level_short)
  SELECT
    srcg.college_roll_no,
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors,
    COUNT(*) AS count_weighted_factors,
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors,
    COUNT(*) AS count_unweighted_factors,
    eg.short_name
  FROM student_report_card_grades srcg
  INNER JOIN colleges s ON s.id=srcg.college_id
  LEFT JOIN enroll_grade eg on eg.college_roll_no=srcg.college_roll_no AND eg.syear=srcg.syear AND eg.college_id=srcg.college_id
  WHERE srcg.marking_period_id=mp_id AND srcg.gp_scale<>0 AND srcg.marking_period_id NOT LIKE 'E%'
  GROUP BY srcg.college_roll_no,eg.short_name;

  UPDATE student_mp_stats sms
    INNER JOIN tmp t on t.college_roll_no=sms.college_roll_no
  SET
    sms.sum_weighted_factors=t.sum_weighted_factors,
    sms.count_weighted_factors=t.count_weighted_factors,
    sms.sum_unweighted_factors=t.sum_unweighted_factors,
    sms.count_unweighted_factors=t.count_unweighted_factors
  WHERE sms.marking_period_id=mp_id;

  INSERT INTO student_mp_stats(college_roll_no,marking_period_id,sum_weighted_factors,count_weighted_factors,
    sum_unweighted_factors,count_unweighted_factors,grade_level_short)
  SELECT
      t.college_roll_no,
      mp_id,
      t.sum_weighted_factors,
      t.count_weighted_factors,
      t.sum_unweighted_factors,
      t.count_unweighted_factors,
      t.grade_level_short
    FROM tmp t
    LEFT JOIN student_mp_stats sms ON sms.college_roll_no=t.college_roll_no AND sms.marking_period_id=mp_id
    WHERE sms.college_roll_no IS NULL;

  UPDATE student_mp_stats g
    INNER JOIN (
	SELECT s.college_roll_no,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN colleges sc ON sc.id=s.college_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY college_roll_no) gg ON gg.college_roll_no=g.college_roll_no
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor, g.cum_weighted_factor=gg.cum_weighted_factor;


    SELECT mp_type INTO @mp_type FROM marking_periods WHERE marking_period_id=mp_id;

 
    IF @mp_type = 'quarter'  THEN
           set req_mp = 'quarter';
    ELSEIF @mp_type = 'semester'  THEN
        IF EXISTS(SELECT college_roll_no FROM student_report_card_grades srcg WHERE srcg.marking_period_id IN (SELECT marking_period_id  FROM marking_periods WHERE mp_type=@mp_type)) THEN
           set req_mp  = 'semester';
       ELSE
           set req_mp  = 'quarter';
        END IF;
   ELSEIF @mp_type = 'year'  THEN
           IF EXISTS(SELECT college_roll_no FROM student_report_card_grades srcg WHERE srcg.MARKING_PERIOD_ID IN (SELECT marking_period_id  FROM marking_periods WHERE mp_type='semester')
                     UNION  SELECT college_roll_no FROM student_report_card_grades srcg WHERE srcg.MARKING_PERIOD_ID IN (SELECT marking_period_id  FROM history_marking_periods WHERE mp_type='semester')
                     ) THEN
                 set req_mp  = 'semester';
         
          ELSE
                  set req_mp  = 'quarter ';
            END IF;
   END IF;



open cur1;
fetch cur1 into college_roll_no, gp_points,gp_points_weighted,divisor,credit_earned,cgpa;

while not done DO
    IF EXISTS(SELECT college_roll_no FROM student_gpa_running WHERE  student_gpa_running.college_roll_no=college_roll_no) THEN
    UPDATE student_gpa_running gc
               SET gpa_points=gp_points,gpa_points_weighted=gp_points_weighted,gc.divisor=divisor,credit_earned=credit_earned,gc.cgpa=cgpa where gc.college_roll_no=college_roll_no;
    ELSE
        INSERT INTO student_gpa_running(college_roll_no,marking_period_id,gpa_points,gpa_points_weighted, divisor,credit_earned,cgpa)
          VALUES(college_roll_no,mp_id,gp_points,gp_points_weighted,divisor,credit_earned,cgpa);
    END IF;
fetch cur1 into college_roll_no, gp_points,gp_points_weighted,divisor,credit_earned,cgpa;
END WHILE;
CLOSE cur1;


RETURN 1;

END$$
DELIMITER ;




DROP FUNCTION IF EXISTS `CALC_GPA_MP`;
DELIMITER $$
CREATE FUNCTION `CALC_GPA_MP`(
	s_id int,
	mp_id int
) RETURNS int(11)
BEGIN
  SELECT
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors, 
    COUNT(*) AS count_weighted_factors,                        
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors, 
    COUNT(*) AS count_unweighted_factors,
   IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned))/ sum(srcg.credit_attempted),
                      IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned)/sum(srcg.credit_attempted),
                         ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))/sum(srcg.credit_attempted)
                        )
      ),
    
    SUM(srcg.weighted_gp*srcg.credit_earned)/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.college_roll_no=s_id
                                                  AND sg.weighted_gp  IS NOT NULL  AND sg.unweighted_gp IS NULL AND sg.course_period_id IS NOT NULL GROUP BY sg.college_roll_no, sg.marking_period_id) ,
    SUM(srcg.unweighted_gp*srcg.credit_earned)/ (select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.college_roll_no=s_id
                                                     AND sg.unweighted_gp  IS NOT NULL  AND sg.weighted_gp IS NULL AND sg.course_period_id IS NOT NULL GROUP BY sg.college_roll_no, sg.marking_period_id) ,
    eg.short_name
  INTO
    @sum_weighted_factors,
    @count_weighted_factors,
    @sum_unweighted_factors,
    @count_unweighted_factors,
    @gpa,
    @weighted_gpa,
    @unweighted_gpa,
    @grade_level_short
  FROM student_report_card_grades srcg
  INNER JOIN colleges s ON s.id=srcg.college_id
INNER JOIN course_periods cp ON cp.course_period_id=srcg.course_period_id
INNER JOIN report_card_grade_scales rcgs ON rcgs.id=cp.grade_scale_id
  LEFT JOIN enroll_grade eg on eg.college_roll_no=srcg.college_roll_no AND eg.syear=srcg.syear AND eg.college_id=srcg.college_id
  WHERE srcg.marking_period_id=mp_id AND srcg.college_roll_no=s_id AND srcg.gp_scale<>0 AND srcg.course_period_id IS NOT NULL AND (rcgs.gpa_cal='Y' OR cp.grade_scale_id IS NULL) AND srcg.marking_period_id NOT LIKE 'E%'
  GROUP BY srcg.college_roll_no,eg.short_name;

  IF EXISTS(SELECT NULL FROM student_mp_stats WHERE marking_period_id=mp_id AND college_roll_no=s_id) THEN
    UPDATE student_mp_stats
    SET
      sum_weighted_factors=@sum_weighted_factors,
      count_weighted_factors=@count_weighted_factors,
      sum_unweighted_factors=@sum_unweighted_factors,
      count_unweighted_factors=@count_unweighted_factors
    WHERE marking_period_id=mp_id AND college_roll_no=s_id;
  ELSE
    INSERT INTO student_mp_stats(college_roll_no,marking_period_id,sum_weighted_factors,count_weighted_factors,
        sum_unweighted_factors,count_unweighted_factors,grade_level_short)
      VALUES(s_id,mp_id,@sum_weighted_factors,@count_weighted_factors,@sum_unweighted_factors,
        @count_unweighted_factors,@grade_level_short);
  END IF;

  UPDATE student_mp_stats g
    INNER JOIN (
	SELECT s.college_roll_no,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN colleges sc ON sc.id=s.college_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE s.course_period_id IS NOT NULL AND p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY college_roll_no) gg ON gg.college_roll_no=g.college_roll_no
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor, g.cum_weighted_factor=gg.cum_weighted_factor
    WHERE g.college_roll_no=s_id;


IF EXISTS(SELECT college_roll_no FROM student_gpa_calculated WHERE marking_period_id=mp_id AND college_roll_no=s_id) THEN
    UPDATE student_gpa_calculated
    SET
      gpa            = @gpa,
      weighted_gpa   =@weighted_gpa,
      unweighted_gpa =@unweighted_gpa

    WHERE marking_period_id=mp_id AND college_roll_no=s_id;
  ELSE
        INSERT INTO student_gpa_calculated(college_roll_no,marking_period_id,mp,gpa,weighted_gpa,unweighted_gpa,grade_level_short)
            VALUES(s_id,mp_id,mp_id,@gpa,@weighted_gpa,@unweighted_gpa,@grade_level_short  );
                   

   END IF;

  RETURN 0;
END$$
DELIMITER ;



DROP FUNCTION IF EXISTS `RE_CALC_GPA_MP`;
DELIMITER $$
CREATE FUNCTION `RE_CALC_GPA_MP`(
	s_id int,
	mp_id int,
        sy int,
        sch_id int
) RETURNS int(11)
BEGIN
  SELECT
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors, 
    COUNT(*) AS count_weighted_factors,                        
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors, 
    COUNT(*) AS count_unweighted_factors,
   IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned))/ sum(srcg.credit_attempted),
                      IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned)/sum(srcg.credit_attempted),
                         ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))/sum(srcg.credit_attempted)
                        )
      ),
    
    SUM(srcg.weighted_gp*srcg.credit_earned)/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.college_roll_no=s_id
                                                  AND sg.weighted_gp  IS NOT NULL  AND sg.unweighted_gp IS NULL GROUP BY sg.college_roll_no, sg.marking_period_id) ,
    SUM(srcg.unweighted_gp*srcg.credit_earned)/ (select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.college_roll_no=s_id
                                                     AND sg.unweighted_gp  IS NOT NULL  AND sg.weighted_gp IS NULL GROUP BY sg.college_roll_no, sg.marking_period_id) ,
    eg.short_name
  INTO
    @sum_weighted_factors,
    @count_weighted_factors,
    @sum_unweighted_factors,
    @count_unweighted_factors,
    @gpa,
    @weighted_gpa,
    @unweighted_gpa,
    @grade_level_short
  FROM student_report_card_grades srcg
  INNER JOIN colleges s ON s.id=srcg.college_id
  LEFT JOIN enroll_grade eg on eg.college_roll_no=srcg.college_roll_no AND eg.syear=srcg.syear AND eg.college_id=srcg.college_id
  WHERE srcg.marking_period_id=mp_id AND srcg.college_roll_no=s_id AND srcg.gp_scale<>0 AND srcg.college_id=sch_id AND srcg.syear=sy AND srcg.marking_period_id NOT LIKE 'E%'
  GROUP BY srcg.college_roll_no,eg.short_name;

  IF EXISTS(SELECT NULL FROM student_mp_stats WHERE marking_period_id=mp_id AND college_roll_no=s_id) THEN
    UPDATE student_mp_stats
    SET
      sum_weighted_factors=@sum_weighted_factors,
      count_weighted_factors=@count_weighted_factors,
      sum_unweighted_factors=@sum_unweighted_factors,
      count_unweighted_factors=@count_unweighted_factors
    WHERE marking_period_id=mp_id AND college_roll_no=s_id;
  ELSE
    INSERT INTO student_mp_stats(college_roll_no,marking_period_id,sum_weighted_factors,count_weighted_factors,
        sum_unweighted_factors,count_unweighted_factors,grade_level_short)
      VALUES(s_id,mp_id,@sum_weighted_factors,@count_weighted_factors,@sum_unweighted_factors,
        @count_unweighted_factors,@grade_level_short);
  END IF;

  UPDATE student_mp_stats g
    INNER JOIN (
	SELECT s.college_roll_no,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN colleges sc ON sc.id=s.college_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY college_roll_no) gg ON gg.college_roll_no=g.college_roll_no
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor, g.cum_weighted_factor=gg.cum_weighted_factor
    WHERE g.college_roll_no=s_id;


IF EXISTS(SELECT college_roll_no FROM student_gpa_calculated WHERE marking_period_id=mp_id AND college_roll_no=s_id) THEN
    UPDATE student_gpa_calculated
    SET
      gpa            = @gpa,
      weighted_gpa   =@weighted_gpa,
      unweighted_gpa =@unweighted_gpa

    WHERE marking_period_id=mp_id AND college_roll_no=s_id;
  ELSE
        INSERT INTO student_gpa_calculated(college_roll_no,marking_period_id,mp,gpa,weighted_gpa,unweighted_gpa,grade_level_short)
            VALUES(s_id,mp_id,mp_id,@gpa,@weighted_gpa,@unweighted_gpa,@grade_level_short  );
                   

   END IF;

  RETURN 0;
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `CREDIT`;
DELIMITER $$
CREATE FUNCTION `CREDIT`(
 	cp_id int,
 	mp_id int
 ) RETURNS decimal(10,3)
BEGIN
  SELECT credits,marking_period_id,mp INTO @credits,@marking_period_id,@mp FROM course_periods WHERE course_period_id=cp_id;
  SELECT mp_type INTO @mp_type FROM marking_periods WHERE marking_period_id=mp_id;
 
  IF @marking_period_id=mp_id THEN
    RETURN @credits;
ELSEIF @mp = 'QTR' AND @mp_type = 'semester' THEN
     RETURN @credits;
   ELSEIF @mp='FY' AND @mp_type='semester' THEN
     SELECT COUNT(*) INTO @val FROM marking_periods WHERE parent_id=@marking_period_id GROUP BY parent_id;
   ELSEIF @mp = 'FY' AND @mp_type = 'quarter' THEN
     SELECT count(*) into @val FROM marking_periods WHERE grandparent_id=@marking_period_id GROUP BY grandparent_id;
   ELSEIF @mp = 'SEM' AND @mp_type = 'quarter' THEN
     SELECT count(*) into @val FROM marking_periods WHERE parent_id=@marking_period_id GROUP BY parent_id;
   ELSE
     RETURN 0;
   END IF;
   IF @val > 0 THEN
     RETURN @credits/@val;
   END IF;
   RETURN 0;
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `STUDENT_DISABLE`;
DELIMITER $$
CREATE FUNCTION `STUDENT_DISABLE`(
stu_id int
) RETURNS int(1)
BEGIN
UPDATE students set is_disable ='Y' where (select end_date from student_enrollment where  college_roll_no=stu_id ORDER BY id DESC LIMIT 1) IS NOT NULL AND (select end_date from student_enrollment where  college_roll_no=stu_id ORDER BY id DESC LIMIT 1)< CURDATE() AND  college_roll_no=stu_id;
RETURN 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `SEAT_COUNT`;
DELIMITER $$
CREATE PROCEDURE `SEAT_COUNT`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats-1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N');
UPDATE schedule SET dropped='Y' WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N';
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `SEAT_FILL`;
DELIMITER $$
CREATE PROCEDURE `SEAT_FILL`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats+1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()));
UPDATE schedule SET dropped='N' WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()) ;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `TEACHER_REASSIGNMENT`;
DELIMITER $$
CREATE PROCEDURE `TEACHER_REASSIGNMENT`()
BEGIN
 UPDATE course_periods cp,teacher_reassignment tr,college_periods sp,marking_periods mp,staff st SET cp.title=CONCAT(sp.title,IF(cp.mp<>'FY',CONCAT(' - ',mp.short_name),''),IF(CHAR_LENGTH(cp.days)<5,CONCAT(' - ',cp.days),''),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)), cp.teacher_id=tr.teacher_id WHERE cp.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=tr.teacher_id and cp.course_period_id=tr.course_period_id AND assign_date <= CURDATE() AND updated='N';
 UPDATE teacher_reassignment SET updated='Y' WHERE assign_date <=CURDATE() AND updated='N';
 END$$
DELIMITER ;


DROP PROCEDURE IF EXISTS `ATTENDANCE_CALC`;
DELIMITER $$
CREATE PROCEDURE `ATTENDANCE_CALC`(IN cp_id INT,IN year INT,IN college INT)
BEGIN
DELETE FROM missing_attendance WHERE COURSE_PERIOD_ID=cp_id;
INSERT INTO missing_attendance(COLLEGE_ID,SYEAR,COLLEGE_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) SELECT s.ID AS COLLEGE_ID,acc.SYEAR,acc.COLLEGE_DATE,cp.COURSE_PERIOD_ID,cp.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.college_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.COLLEGE_ID=acc.COLLEGE_ID AND acc.COLLEGE_DATE BETWEEN mp.START_DATE AND mp.END_DATE INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID AND cp.DOES_ATTENDANCE='Y' AND cp.CALENDAR_ID=acc.CALENDAR_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN college_periods sp ON sp.SYEAR=acc.SYEAR AND sp.COLLEGE_ID=acc.COLLEGE_ID AND sp.PERIOD_ID=cp.PERIOD_ID AND (sp.BLOCK IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.COLLEGE_DATE) FOR 1) IN cp.DAYS)>0 OR sp.BLOCK IS NOT NULL AND acc.BLOCK IS NOT NULL AND sp.BLOCK=acc.BLOCK) INNER JOIN colleges s ON s.ID=acc.COLLEGE_ID INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.COLLEGE_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.COLLEGE_DATE ) AND cp.COURSE_PERIOD_ID= cp_id LEFT JOIN attendance_completed ac ON ac.COLLEGE_DATE=acc.COLLEGE_DATE AND IF(tra.course_period_id=cp.course_period_id AND acc.college_date<=tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id) AND ac.PERIOD_ID=sp.PERIOD_ID WHERE acc.SYEAR=year AND acc.COLLEGE_ID=college AND (acc.MINUTES IS NOT NULL AND acc.MINUTES>0) AND acc.COLLEGE_DATE<CURDATE() AND ac.STAFF_ID IS NULL GROUP BY s.TITLE,acc.COLLEGE_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID;
END$$
DELIMITER ;


DROP PROCEDURE IF EXISTS `ATTENDANCE_CALC_BY_DATE`;
DELIMITER $$
CREATE PROCEDURE `ATTENDANCE_CALC_BY_DATE`(IN sch_dt DATE,IN year INT,IN college INT)
BEGIN
 DELETE FROM missing_attendance WHERE COLLEGE_DATE=sch_dt AND SYEAR=year AND COLLEGE_ID=college;
 INSERT INTO missing_attendance(COLLEGE_ID,SYEAR,COLLEGE_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) SELECT s.ID AS COLLEGE_ID,acc.SYEAR,acc.COLLEGE_DATE,cp.COURSE_PERIOD_ID,cp.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.college_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.COLLEGE_ID=acc.COLLEGE_ID AND acc.COLLEGE_DATE BETWEEN mp.START_DATE AND mp.END_DATE INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID AND cp.DOES_ATTENDANCE='Y' AND cp.CALENDAR_ID=acc.CALENDAR_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN college_periods sp ON sp.SYEAR=acc.SYEAR AND sp.COLLEGE_ID=acc.COLLEGE_ID AND sp.PERIOD_ID=cp.PERIOD_ID AND (sp.BLOCK IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.COLLEGE_DATE) FOR 1) IN cp.DAYS)>0 OR sp.BLOCK IS NOT NULL AND acc.BLOCK IS NOT NULL AND sp.BLOCK=acc.BLOCK) INNER JOIN colleges s ON s.ID=acc.COLLEGE_ID INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.COLLEGE_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.COLLEGE_DATE )  LEFT JOIN attendance_completed ac ON ac.COLLEGE_DATE=acc.COLLEGE_DATE AND IF(tra.course_period_id=cp.course_period_id AND acc.college_date<tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id) AND ac.PERIOD_ID=sp.PERIOD_ID WHERE acc.SYEAR=year AND acc.COLLEGE_ID=college AND (acc.MINUTES IS NOT NULL AND acc.MINUTES>0) AND acc.COLLEGE_DATE=sch_dt AND ac.STAFF_ID IS NULL GROUP BY s.TITLE,acc.COLLEGE_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID;
END$$
DELIMITER ;
