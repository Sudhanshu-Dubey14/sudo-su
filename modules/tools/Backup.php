<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  colleges from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************

include('../../RedirectModulesInc.php');
require_once("Data.php");
$print_form = 1;
$output_messages = array();

ini_set('memory_limit', '9000M');

ini_set('max_execution_time', '50000');
ini_set('max_input_time', '50000');

$host = $DatabaseServer;
$name = $DatabaseName;
$user = $DatabaseUsername;
$pass = $DatabasePassword;
$port = $DatabasePort;
if (('Backup' == $_REQUEST['action']) || ($_REQUEST['action'] == 'backup')) {

    $print_form = 0;
    $date_time = date("m-d-Y");
    $Export_FileName = $name . 'Backup' . $date_time . '.sql';
    $dbconn = new mysqli($host, $user, $pass, $name, $port);
    if ($dbconn->connect_errno != 0)
        exit($dbconn->error);
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $result = $dbconn->query("SHOW VARIABLES LIKE 'basedir'");
        $row = $result->fetch_assoc();
        $mysql_dir1 = substr($row['Value'], 0, 2);
        $sql_path_arr=explode("\\",$_SERVER['MYSQL_HOME']);
        $sql_path="\\".$sql_path_arr[1].'\\'.$sql_path_arr[2].'\\'.$sql_path_arr[3];
        $mysql_dir = str_replace('\\', '\\\\', $mysql_dir1.$_SERVER['MYSQL_HOME']);
//        $mysql_dir = str_replace('\\', '\\\\', $mysql_dir1.$sql_path);
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        if ($pass == '')
            exec("$mysql_dir\\mysqldump -n -c --skip-add-locks --skip-disable-keys --routines --triggers --user $user  $name > $Export_FileName");
        else
            exec("$mysql_dir\\mysqldump -n -c --skip-add-locks --skip-disable-keys --routines --triggers --user $user --password='$pass' $name > $Export_FileName");
    }
    else {
        exec("mysqldump -n -c --skip-add-locks --skip-disable-keys --routines --triggers --user $user --password='$pass' $name > $Export_FileName");
    }
    $content = file_get_contents($Export_FileName);
    $fname = $Export_FileName;
    unlink($Export_FileName);
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $fname . "\"");
    //$content= file_get_contents($Export_FileName);
    echo $content;
    exit;
}
if ($print_form > 0 && !$_REQUEST['modfunc'] == 'cancel') {
    ?>
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <form id="dataForm" name="dataForm" method="post" action="ForExport.php?modname=tools/Backup.php&action=backup&_openSIS_PDF=true" target=_blank>
                <?php
                PopTable('header', 'Backup');
                echo '<h4 class="text-danger">Note:</h4><p>This backup utility will create a backup of the database along with the database structure. You will be able to use this backup file to restore the database. However, in order to restore, you  will need to have access to MySQL administration application like phpMyAdmin and the root user id and password to MySQL.</p>';

                $btn = '<input type="submit" name="action"  value="Backup" class="btn btn-primary"> &nbsp; ';
                $modname = 'tools/Backup.php';
                $btn .= '<a href=javascript:void(0); onClick="check_content(\'Ajax.php?modname=miscellaneous/Portal.php\');" STYLE="TEXT-DECORATION: NONE"> <INPUT type=button class="btn btn-default" name=Cancel value=Cancel></a>';
                
                PopTable('footer', $btn);
                ?>
            </form>
        </div>
    </div>
    <?php
}

function EXPORT_TABLES($host, $user, $pass, $name, $tables = false, $backup_name = false) {

    // $backup_name=$name;
//    $backup_name=$name."(".date("H:i:s d-m-Y").").sql";
    if (strpos($name, 'opensis') >= 0)
        $backup_name = $name . "_" . str_replace("-", '_', date("m-d-Y")) . ".sql";
    else
        $backup_name = 'opensis1' . $name . "_" . str_replace("-", '_', date("m-d-Y")) . ".sql";
    set_time_limit(3000);
    $mysqli = new mysqli($host, $user, $pass, $name, $port);
    $mysqli->query("SET NAMES 'utf8'");
    $queryTables = $mysqli->query("SHOW TABLES");
    while ($row = $queryTables->fetch_row()) {
        $target_tables[] = $row[0];
    } if ($tables !== false) {
        $target_tables = array_intersect($target_tables, $tables);
    }
    $content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `" . $name . "`\r\n--\r\n\r\n\r\n";

    $arr = array('marking_periods', 'enroll_grade', 'transcript_grades', 'course_details');
    foreach ($target_tables as $table) {

        if (!in_array($table, $arr)) {
            if (empty($table)) {
                continue;
            }
            $result = $mysqli->query('SELECT * FROM `' . $table . '`');
            $fields_amount = $result->field_count;
            $rows_num = $mysqli->affected_rows;
            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $TableMLine = $res->fetch_row();
            $content .= "\n\n" . $TableMLine[1] . ";\n\n";
            for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
                while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
                    if ($st_counter % 100 == 0 || $st_counter == 0) {
                        $content .= "\nINSERT INTO " . $table . " VALUES";
                    }
                    $content .= "\n(";
                    for ($j = 0; $j < $fields_amount; $j++) {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j]) && $row[$j] != '') {
                            $content .= '"' . $row[$j] . '"';
                        } else {
                            $content .= 'NULL';
                        } if ($j < ($fields_amount - 1)) {
                            $content.= ',';
                        }
                    } $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                        $content .= ";";
                    } else {
                        $content .= ",";
                    } $st_counter = $st_counter + 1;
                }
            } $content .="\n\n\n";
        }
    }

    $content.= "--
              --
              --

                    CREATE VIEW marking_periods AS
    SELECT q.marking_period_id, 'openSIS' AS mp_source, q.syear,
	q.college_id, 'quarter' AS mp_type, q.title, q.short_name,
	q.sort_order, q.semester_id AS parent_id,
	s.year_id AS grandparent_id, q.start_date,
	q.end_date, q.post_start_date,
	q.post_end_date, q.does_grades,
	q.does_exam, q.does_comments
    FROM college_quarters q
    JOIN college_semesters s ON q.semester_id = s.marking_period_id
UNION
    SELECT marking_period_id, 'openSIS' AS mp_source, syear,
	college_id, 'semester' AS mp_type, title, short_name,
	sort_order, year_id AS parent_id,
	-1 AS grandparent_id, start_date,
	end_date, post_start_date,
	post_end_date, does_grades,
	does_exam, does_comments
    FROM college_semesters
UNION
    SELECT marking_period_id, 'openSIS' AS mp_source, syear,
	college_id, 'year' AS mp_type, title, short_name,
	sort_order, -1 AS parent_id,
	-1 AS grandparent_id, start_date,
	end_date, post_start_date,
	post_end_date, does_grades,
	does_exam, does_comments
    FROM college_years
UNION
    SELECT marking_period_id, 'History' AS mp_source, syear,
	college_id, mp_type, name AS title, NULL AS short_name,
	NULL AS sort_order, parent_id,
	-1 AS grandparent_id, NULL AS start_date,
	post_end_date AS end_date, NULL AS post_start_date,
	post_end_date, 'Y' AS does_grades,
	NULL AS does_exam, NULL AS does_comments
    FROM history_marking_periods;\n

          

             CREATE VIEW course_details AS
  SELECT cp.college_id, cp.syear, cp.marking_period_id, c.subject_id,
	  cp.course_id, cp.course_period_id, cp.teacher_id,cp. secondary_teacher_id, c.title AS course_title,
	  cp.title AS cp_title, cp.grade_scale_id, cp.mp, cp.credits,cp.begin_date,cp.end_date
  FROM course_periods cp, courses c WHERE (cp.course_id = c.course_id);\n

CREATE VIEW enroll_grade AS
  SELECT e.id, e.syear, e.college_id, e.college_roll_no, e.start_date, e.end_date, sg.short_name, sg.title
  FROM student_enrollment e, college_gradelevels sg WHERE (e.grade_id = sg.id);\n

CREATE VIEW transcript_grades AS
    SELECT s.id AS college_id, IF(mp.mp_source='history',(SELECT college_name FROM history_college WHERE college_roll_no=rcg.college_roll_no and marking_period_id=mp.marking_period_id),s.title) AS college_name,mp_source, mp.marking_period_id AS mp_id,
 mp.title AS mp_name, mp.syear, mp.end_date AS posted, rcg.college_roll_no,
 sgc.grade_level_short AS gradelevel, rcg.grade_letter, rcg.unweighted_gp AS gp_value,
 rcg.weighted_gp AS weighting, rcg.gp_scale, rcg.credit_attempted, rcg.credit_earned,
 rcg.credit_category,rcg.course_period_id AS course_period_id, rcg.course_title AS course_name,
        (SELECT courses.short_name FROM course_periods,courses  WHERE course_periods.course_id=courses.course_id and course_periods.course_period_id=rcg.course_period_id) AS course_short_name,rcg.gpa_cal AS gpa_cal,
 sgc.weighted_gpa,
 sgc.unweighted_gpa,
                  sgc.gpa,
 sgc.class_rank,mp.sort_order
    FROM student_report_card_grades rcg
    INNER JOIN marking_periods mp ON mp.marking_period_id = rcg.marking_period_id AND mp.mp_type IN ('year','semester','quarter')
    INNER JOIN student_gpa_calculated sgc ON sgc.college_roll_no = rcg.college_roll_no AND sgc.marking_period_id = rcg.marking_period_id
    INNER JOIN colleges s ON s.id = mp.college_id;\n
            ";
    $content.="DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `ATTENDANCE_CALC`(IN cp_id INT)
BEGIN
DELETE FROM missing_attendance WHERE COURSE_PERIOD_ID=cp_id;
INSERT INTO missing_attendance(COLLEGE_ID,SYEAR,COLLEGE_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) 
        SELECT s.ID AS COLLEGE_ID,acc.SYEAR,acc.COLLEGE_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.college_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,
        cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN course_periods cp ON cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID 
        AND (cpv.COURSE_PERIOD_DATE IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.COLLEGE_DATE) FOR 1) IN cpv.DAYS)>0 OR cpv.COURSE_PERIOD_DATE IS NOT NULL AND cpv.COURSE_PERIOD_DATE=acc.COLLEGE_DATE) 
        INNER JOIN colleges s ON s.ID=acc.COLLEGE_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID 
        AND sch.college_roll_no IN(SELECT college_roll_no FROM student_enrollment se WHERE sch.college_id=se.college_id AND sch.syear=se.syear AND start_date<=acc.college_date AND (end_date IS NULL OR end_date>=acc.college_date))
        AND (cp.MARKING_PERIOD_ID IS NOT NULL AND cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM college_years WHERE COLLEGE_ID=acc.COLLEGE_ID AND acc.COLLEGE_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM college_semesters WHERE COLLEGE_ID=acc.COLLEGE_ID AND acc.COLLEGE_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM college_quarters WHERE COLLEGE_ID=acc.COLLEGE_ID AND acc.COLLEGE_DATE BETWEEN START_DATE AND END_DATE) OR (cp.MARKING_PERIOD_ID IS NULL AND acc.college_date BETWEEN cp.begin_date AND cp.end_date))
        AND sch.START_DATE<=acc.COLLEGE_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.COLLEGE_DATE ) AND cpv.DOES_ATTENDANCE='Y' AND acc.COLLEGE_DATE<CURDATE() AND cp.course_period_id=cp_id 
        AND NOT EXISTS (SELECT '' FROM  attendance_completed ac WHERE ac.COLLEGE_DATE=acc.COLLEGE_DATE AND ac.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ac.PERIOD_ID=cpv.PERIOD_ID 
        AND IF(tra.course_period_id=cp.course_period_id AND acc.college_date<=tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id)) 
        GROUP BY acc.COLLEGE_DATE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID,cpv.PERIOD_ID;
END$$

CREATE PROCEDURE `ATTENDANCE_CALC_BY_DATE`(IN sch_dt DATE,IN year INT,IN college INT)
BEGIN
 DELETE FROM missing_attendance WHERE COLLEGE_DATE=sch_dt AND SYEAR=year AND COLLEGE_ID=college;
 INSERT INTO missing_attendance(COLLEGE_ID,SYEAR,COLLEGE_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) SELECT s.ID AS COLLEGE_ID,acc.SYEAR,acc.COLLEGE_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.college_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.COLLEGE_ID=acc.COLLEGE_ID AND acc.COLLEGE_DATE BETWEEN mp.START_DATE AND mp.END_DATE INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID  AND cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE='Y' LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN college_periods sp ON sp.SYEAR=acc.SYEAR AND sp.COLLEGE_ID=acc.COLLEGE_ID AND sp.PERIOD_ID=cpv.PERIOD_ID AND (sp.BLOCK IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.COLLEGE_DATE) FOR 1) IN cpv.DAYS)>0 OR sp.BLOCK IS NOT NULL AND acc.BLOCK IS NOT NULL AND sp.BLOCK=acc.BLOCK) INNER JOIN colleges s ON s.ID=acc.COLLEGE_ID INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.COLLEGE_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.COLLEGE_DATE )  LEFT JOIN attendance_completed ac ON ac.COLLEGE_DATE=acc.COLLEGE_DATE AND IF(tra.course_period_id=cp.course_period_id AND acc.college_date<tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id) AND ac.PERIOD_ID=sp.PERIOD_ID WHERE acc.SYEAR=year AND acc.COLLEGE_ID=college AND (acc.MINUTES IS NOT NULL AND acc.MINUTES>0) AND acc.COLLEGE_DATE=sch_dt AND ac.STAFF_ID IS NULL GROUP BY s.TITLE,acc.COLLEGE_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID;
END$$

CREATE PROCEDURE `SEAT_COUNT`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats-1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N');
UPDATE schedule SET dropped='Y' WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N';
END$$

CREATE PROCEDURE `SEAT_FILL`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats+1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()));
UPDATE schedule SET dropped='N' WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()) ;
END$$

CREATE PROCEDURE `TEACHER_REASSIGNMENT`()
BEGIN
UPDATE course_periods cp,course_period_var cpv,teacher_reassignment tr,college_periods sp,marking_periods mp,staff st SET cp.title=CONCAT(sp.title,IF(cp.mp<>'FY',CONCAT(' - ',mp.short_name),''),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),''),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)), cp.teacher_id=tr.teacher_id WHERE cpv.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=tr.teacher_id and cp.course_period_id=tr.course_period_id AND assign_date <= CURDATE() AND updated='N' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID; 
 UPDATE teacher_reassignment SET updated='Y' WHERE assign_date <=CURDATE() AND updated='N';
 END$$

--
-- functions
--
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

  INSERT INTO student_gpa_calculated (college_roll_no,marking_period_id)
  SELECT
      t.college_roll_no,
      mp_id
    FROM tmp t
    LEFT JOIN student_gpa_calculated sms ON sms.college_roll_no=t.college_roll_no AND sms.marking_period_id=mp_id
    WHERE sms.college_roll_no IS NULL;

  UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.college_roll_no,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN colleges sc ON sc.id=s.college_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY college_roll_no) gg ON gg.college_roll_no=g.college_roll_no
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor;


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
    IF EXISTS(SELECT college_roll_no FROM student_gpa_calculated WHERE  student_gpa_calculated.college_roll_no=college_roll_no) THEN
    UPDATE student_gpa_calculated gc
               SET gc.cgpa=cgpa where gc.college_roll_no=college_roll_no and gc.marking_period_id=mp_id;
    ELSE
        INSERT INTO student_gpa_running(college_roll_no,marking_period_id,mp,cgpa)
          VALUES(college_roll_no,mp_id,mp_id,cgpa);
    END IF;
fetch cur1 into college_roll_no, gp_points,gp_points_weighted,divisor,credit_earned,cgpa;
END WHILE;
CLOSE cur1;


RETURN 1;

END$$

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
  AND (eg.START_DATE IS NULL OR eg.START_DATE='0000-00-00'  OR eg.START_DATE<=CURDATE()) AND (eg.END_DATE IS NULL OR eg.END_DATE='0000-00-00'  OR eg.END_DATE>=CURDATE())  
  GROUP BY srcg.college_roll_no,eg.short_name;
  
IF NOT EXISTS(SELECT NULL FROM student_gpa_calculated WHERE marking_period_id=mp_id AND college_roll_no=s_id) THEN
    INSERT INTO student_gpa_calculated (college_roll_no,marking_period_id)
      VALUES(s_id,mp_id);
  END IF;

  UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.college_roll_no,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN colleges sc ON sc.id=s.college_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE s.course_period_id IS NOT NULL AND p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY college_roll_no) gg ON gg.college_roll_no=g.college_roll_no
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor
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

CREATE FUNCTION `CREDIT`(
 	cp_id int,
 	mp_id int
 ) RETURNS decimal(10,3)
BEGIN
  SELECT credits,IF(ISNULL(marking_period_id),'Y',marking_period_id),mp INTO @credits,@marking_period_id,@mp FROM course_periods WHERE course_period_id=cp_id;
   SELECT mp_type INTO @mp_type FROM marking_periods WHERE marking_period_id=mp_id;
  
IF @marking_period_id='Y' THEN 
RETURN @credits;
   ELSEIF   @marking_period_id=mp_id THEN
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

CREATE FUNCTION fn_marking_period_seq () RETURNS INT
BEGIN
  INSERT INTO marking_period_id_generator VALUES(NULL);
RETURN LAST_INSERT_ID();
END$$

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

CREATE FUNCTION `STUDENT_DISABLE`(
stu_id int
) RETURNS int(1)
BEGIN
UPDATE students set is_disable ='Y' where (select end_date from student_enrollment where  college_roll_no=stu_id ORDER BY id DESC LIMIT 1) IS NOT NULL AND (select end_date from student_enrollment where  college_roll_no=stu_id ORDER BY id DESC LIMIT 1)< CURDATE() AND  college_roll_no=stu_id;
RETURN 1;
END$$

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
AND (eg.START_DATE IS NULL OR eg.START_DATE='0000-00-00'  OR eg.START_DATE<=CURDATE()) AND (eg.END_DATE IS NULL OR eg.END_DATE='0000-00-00'  OR eg.END_DATE>=CURDATE())
  GROUP BY srcg.college_roll_no,eg.short_name;
  
IF NOT EXISTS(SELECT NULL FROM student_gpa_calculated WHERE marking_period_id=mp_id AND college_roll_no=s_id) THEN
    INSERT INTO student_mp_stats(college_roll_no,marking_period_id)
      VALUES(s_id,mp_id);
  END IF;

  UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.college_roll_no,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN colleges sc ON sc.id=s.college_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY college_roll_no) gg ON gg.college_roll_no=g.college_roll_no
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor
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
-- --------------------------------------------------------\n
";
    $content.= "--
-- Triggers `STUDENT_REPORT_CARD_GRADES`
--
DROP TRIGGER IF EXISTS `td_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `td_student_report_card_grades`
    AFTER DELETE ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(OLD.college_roll_no, OLD.marking_period_id) INTO @return$$
DELIMITER ;

DROP TRIGGER IF EXISTS `ti_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `ti_student_report_card_grades`
    AFTER INSERT ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(NEW.college_roll_no, NEW.marking_period_id) INTO @return$$
DELIMITER ;

DROP TRIGGER IF EXISTS `tu_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `tu_student_report_card_grades`
    AFTER UPDATE ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(NEW.college_roll_no, NEW.marking_period_id) INTO @return$$
DELIMITER ;



DROP TRIGGER IF EXISTS tu_periods;
CREATE TRIGGER tu_periods
    AFTER UPDATE ON college_periods
    FOR EACH ROW
        UPDATE course_period_var SET start_time=NEW.start_time,end_time=NEW.end_time WHERE period_id=NEW.period_id;

DROP TRIGGER IF EXISTS tu_college_years;
CREATE TRIGGER tu_college_years
    AFTER UPDATE ON college_years
    FOR EACH ROW
        UPDATE course_periods SET begin_date=NEW.start_date,end_date=NEW.end_date WHERE marking_period_id=NEW.marking_period_id;

DROP TRIGGER IF EXISTS tu_college_semesters;
CREATE TRIGGER tu_college_semesters
    AFTER UPDATE ON college_semesters
    FOR EACH ROW
        UPDATE course_periods SET begin_date=NEW.start_date,end_date=NEW.end_date WHERE marking_period_id=NEW.marking_period_id;

DROP TRIGGER IF EXISTS tu_college_quarters;
CREATE TRIGGER tu_college_quarters
    AFTER UPDATE ON college_quarters
    FOR EACH ROW
        UPDATE course_periods SET begin_date=NEW.start_date,end_date=NEW.end_date WHERE marking_period_id=NEW.marking_period_id;

DROP TRIGGER IF EXISTS ti_course_period_var;
CREATE TRIGGER ti_course_period_var
    AFTER INSERT ON course_period_var
    FOR EACH ROW
	CALL ATTENDANCE_CALC(NEW.course_period_id);

DROP TRIGGER IF EXISTS tu_course_period_var;
CREATE TRIGGER tu_course_period_var
    AFTER UPDATE ON course_period_var
    FOR EACH ROW
	CALL ATTENDANCE_CALC(NEW.course_period_id);

DROP TRIGGER IF EXISTS td_course_period_var;
CREATE TRIGGER td_course_period_var
    AFTER DELETE ON course_period_var
    FOR EACH ROW
	CALL ATTENDANCE_CALC(OLD.course_period_id);

DROP TRIGGER IF EXISTS tu_course_periods;
DELIMITER $$
CREATE TRIGGER tu_course_periods
    AFTER UPDATE ON course_periods
    FOR EACH ROW
    BEGIN
	CALL ATTENDANCE_CALC(NEW.course_period_id);
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS td_course_periods;
DELIMITER $$
CREATE TRIGGER td_course_periods
    AFTER DELETE ON course_periods
    FOR EACH ROW
    BEGIN
	DELETE FROM course_period_var WHERE course_period_id=OLD.course_period_id;
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS ti_schdule;
DELIMITER $$
CREATE TRIGGER ti_schdule
    AFTER INSERT ON schedule
    FOR EACH ROW
    BEGIN
        UPDATE course_periods SET filled_seats=filled_seats+1 WHERE course_period_id=NEW.course_period_id;
	CALL ATTENDANCE_CALC(NEW.course_period_id);
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS tu_schedule;
CREATE TRIGGER tu_schedule
    AFTER UPDATE ON schedule
    FOR EACH ROW
	CALL ATTENDANCE_CALC(NEW.course_period_id);

DROP TRIGGER IF EXISTS td_schedule;
DELIMITER $$
CREATE TRIGGER td_schedule
    AFTER DELETE ON schedule
    FOR EACH ROW
    BEGIN
        UPDATE course_periods SET filled_seats=filled_seats-1 WHERE course_period_id=OLD.course_period_id AND OLD.dropped='N';
	CALL ATTENDANCE_CALC(OLD.course_period_id);
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `ti_cal_missing_attendance`;
DELIMITER $$
CREATE TRIGGER `ti_cal_missing_attendance`
    AFTER INSERT ON attendance_calendar
    FOR EACH ROW
    BEGIN
    DECLARE associations INT;
    SET associations = (SELECT COUNT(course_period_id) FROM `course_periods` WHERE calendar_id=NEW.calendar_id);
    IF associations>0 THEN
	CALL ATTENDANCE_CALC_BY_DATE(NEW.college_date, NEW.syear,NEW.college_id);
    END IF;
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `td_cal_missing_attendance`;
CREATE TRIGGER `td_cal_missing_attendance`
    AFTER DELETE ON attendance_calendar
    FOR EACH ROW
	DELETE mi.* FROM missing_attendance mi,course_periods cp WHERE mi.course_period_id=cp.course_period_id and cp.calendar_id=OLD.calendar_id AND mi.COLLEGE_DATE=OLD.college_date;";
    $content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

//    $backup_name = $backup_name ? $backup_name."_(".date('H:i:s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql" : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
//    
    //$backup_name = $backup_name ? $backup_name."_(".date('H:i:s')."_".date('d-m-Y').").sql" : $name."_(".date('H:i:s')."_".date('d-m-Y').").sql";

    ob_get_clean();
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
    //$a= file_get_contents($content);
    echo $content;
    exit;
}
?>