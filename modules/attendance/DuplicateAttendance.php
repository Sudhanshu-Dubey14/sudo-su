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
if (count($_REQUEST['mp_arr'])) {
    foreach ($_REQUEST['mp_arr'] as $mp)
        $mp_list .= ",'$mp'";
    $mp_list = substr($mp_list, 1);
    $last_mp = $mp;
}
$delete_message = " ";
if (optional_param('modfunc', '', PARAM_NOTAGS) != 'gradelist')
    $x = "x";
else
    $extra['action'] .= '&modfunc=gradelist';

$extra['force_search'] = true;


if (optional_param('delete', '', PARAM_ALPHA) == 'true') {

    if (DeletePrompt('Duplicate Attendance Record')) {
        $i = 0;
        $ii = 0;
        $iii = 0;

        $sid = optional_param('studentidx', '', PARAM_ALPHANUM);
        $cnt = optional_param('deletecheck', '', PARAM_INT);
        $pid = optional_param('periodidx', '', PARAM_SPCL);
        $sdt = $_REQUEST['collegedatex'];




        foreach ($cnt as $a => $val_dchck) {
            $val1 = $val_dchck;
            if ($val1 >= 0) {

                foreach ($sid as $b => $val_sid) {
                    $val2 = $val_sid;
                    if ($val1 == $i) {

                        foreach ($pid as $c => $val_pid) {
                            $val3 = $val_pid;
                            if ($val1 == $ii) {

                                foreach ($sdt as $d => $val_sdt) {
                                    $val4 = $val_sdt;
                                    if ($val1 == $iii) {


                                        $sch_typ = DBGet(DBQuery('SELECT PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID=' . $val3 . ' '));
                                        if ($sch_typ[1]['PERIOD_ID'] != '' && $val2 != '' && $val4 != '' && $val3 != '')
                                            $count_dup = DBGet(DBQuery('SELECT * FROM attendance_period WHERE COLLEGE_ROLL_NO=\'' . $val2 . '\' AND COLLEGE_DATE=\'' . $val4 . '\' AND COURSE_PERIOD_ID=\'' . $val3 . '\' AND PERIOD_ID=' . $sch_typ[1]['PERIOD_ID'] . ' '));
                                        if (count($count_dup) > 1)
                                            DBQuery('DELETE FROM attendance_period WHERE COLLEGE_ROLL_NO=\'' . $val2 . '\' AND COLLEGE_DATE=\'' . $val4 . '\' AND COURSE_PERIOD_ID=\'' . $val3 . '\' LIMIT ' . (count($count_dup) - 1));
                                    }
                                    $iii++;
                                }
                                $iii = 0;
                            }
                            $ii++;
                        }
                        $ii = 0;
                    }
                    $i++;
                }
                $i = 0;
            }
        }

        DrawBC("Attendance > " . ProgramTitle());
        echo "<TABLE width=100% border=0 cellpadding=0 cellspacing=0><TR>";
        echo "<TD bgcolor=#FFFFFF style=border:1;border-style: none none solid none; align=left> &nbsp;";
        echo "<FONT size=-1><IMG SRC=assets/check.gif>";
        echo "The duplicate record(s) has been deleted.";
        echo "</font></TD></TR></TABLE><BR>";
    }
}

if ((!$_REQUEST['search_modfunc'] || $_openSIS['modules_search']) && $_REQUEST['delete'] != 'true') {
    DrawBC("Attendance > " . ProgramTitle());

    $extra['new'] = true;
    Search('college_roll_no', $extra);
} elseif ($_REQUEST['delete'] != 'true') {
    $RET = GetStuList($extra);

    if (isset($_REQUEST['page'])) {
        $urlpage = $_REQUEST['page'];
    } else {
        $urlpage = 1;
    }

    $firstrow = 1;
    $rows_per_page = 25;
    $endrow = $urlpage * $rows_per_page;
    $startrow = $endrow - $rows_per_page;

    if (count($RET)) {

        unset($extra);
        $extra['SELECT_ONLY'] .= 'ap.COURSE_PERIOD_ID, s.COLLEGE_ROLL_NO, s.FIRST_NAME, s.LAST_NAME, ap.COLLEGE_DATE, cp.TITLE, ap.PERIOD_ID, sc.START_DATE, sc.END_DATE ';
        $extra['FROM'] .= ' ,attendance_period ap, course_periods cp, schedule sc ';
        $extra['WHERE'] .= ' AND ap.COLLEGE_ROLL_NO=s.COLLEGE_ROLL_NO AND sc.COLLEGE_ROLL_NO=s.COLLEGE_ROLL_NO AND ap.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND ap.COURSE_PERIOD_ID = sc.COURSE_PERIOD_ID AND (sc.END_DATE > \'' . date('Y-m-d') . ' \' OR sc.END_DATE IS NULL OR sc.END_DATE=\'0000-00-00\' ) ';
        $extra['ORDER_BY'] = ' COLLEGE_ROLL_NO, COURSE_PERIOD_ID, COLLEGE_DATE';
        Widgets('course');
        Widgets('gpa');
        Widgets('class_rank');
        Widgets('letter_grade');
        $pageresult1 = GetStuList($extra);

        $totalrows = 0;
        foreach ($pageresult1 as $rr) {
            $afterr = "N";

            $studentidr = $rr['COLLEGE_ROLL_NO'];
            $courseidr = $rr['COURSE_PERIOD_ID'];
            $periodidr = $rr['PERIOD_ID'];
            $firstr = $rr['FIRST_NAME'];
            $lastr = $rr['LAST_NAME'];
            $collegedater = $rr['COLLEGE_DATE'];
            $titler = $rr['TITLE'];
            $startr = $rr['START_DATE'];
            $endr = $rr['END_DATE'];

            if ($collegedater > $endr) {
                $afterr = "Y";
            }

            if (($studentidr == $studentid2) && ($courseidr == $courseid2) && ($collegedater == $collegedate2) && ($startr == $start2)) {
                $totalrows++;
            } else if (($collegedater > $endr) && ($endr != NULL) && ($startr == $start2)) {
                $totalrows++;
            } else {
                //Do nothing
            }

            $studentid2 = $studentidr;
            $courseid2 = $courseidr;
            $periodid2 = $periodidr;
            $collegedate2 = $collegedater;
            $first2 = $firstr;
            $last2 = $lastr;
            $title2 = $titler;
            $start2 = $startr;
            $end2 = $endr;
        }


        unset($extra);
        $extra['SELECT_ONLY'] .= 'ap.COURSE_PERIOD_ID, s.COLLEGE_ROLL_NO, s.FIRST_NAME, s.LAST_NAME, ap.COLLEGE_DATE, cp.TITLE, cp.SHORT_NAME, ap.PERIOD_ID, sc.START_DATE, sc.END_DATE ';
        $extra['FROM'] .= ' ,attendance_period ap, course_periods cp, schedule sc ';
        $extra['WHERE'] .= ' AND ap.COLLEGE_ROLL_NO=s.COLLEGE_ROLL_NO AND sc.COLLEGE_ROLL_NO=s.COLLEGE_ROLL_NO AND ap.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND ap.COURSE_PERIOD_ID = sc.COURSE_PERIOD_ID AND (sc.END_DATE > \'' . date('Y-m-d') . ' \' OR sc.END_DATE IS NULL OR sc.END_DATE=\'0000-00-00\' ) ';
        $extra['ORDER_BY'] = ' COLLEGE_ROLL_NO, COURSE_PERIOD_ID, COLLEGE_DATE';
        Widgets('course');
        Widgets('gpa');
        Widgets('class_rank');
        Widgets('letter_grade');
        $result1 = GetStuList($extra);

        DrawBC("Attendance > " . ProgramTitle());
        echo "$delete_message";

        echo "<form action=Modules.php?modname=attendance/DuplicateAttendance.php&modfunc=&search_modfunc=list&next_modname=attendance/DuplicateAttendance.php&delete=true method=POST>";

        $num_rows = $totalrows;

        if ($num_rows > $rows_per_page) {

            $totalpages = $num_rows / $rows_per_page;
            $totalpages = ceil($totalpages);

            echo "<center><small>Page:</small> ";
            $first = 0;
            $ii = 1;
            for ($i = 0; $i < $totalpages; $i++) {

                if ($urlpage == $ii) {
                    echo "<b>$ii</b> &nbsp;";
                } else {
                    echo "<a href=Modules.php?modname=attendance/DuplicateAttendance.php&modfunc=&search_modfunc=list&next_modname=attendance/DuplicateAttendance.php&delete=false&page=$ii>$ii</a> &nbsp;";
                }

                $first = $first + $rows_per_page;
                $ii++;
            }
            echo "<small>of $totalpages pages</small>";
        }

        echo '<div class="panel">';
        echo '<div class="tabbable">';
        echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active" id="tab[]"><a href="javascript:void(0);">Student\'s List</a></li></ul>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-striped"><thead>';
        echo "<tr><th><INPUT type=checkbox value=Y name=controller onclick=checkAll(this.form,this.form.controller.checked,'deletecheck');></th>";
        echo "<th>Student (College Roll No)</th>";
        echo "<th>Course (Course Period ID)</th>";
        echo "<th>Course Start Date</th>";
        echo "<th>Course End Date</th>";
        echo "<th>Attendance Date</th></tr></thead><tbody>";

        $URIcount = 0;
        $count = 0;
        $yellow = 1;
        $after = "N";

        foreach ($result1 as $r) {
            $after = "N";

            $studentid = $r['COLLEGE_ROLL_NO'];
            $courseid = $r['COURSE_PERIOD_ID'];
            $periodid = $r['PERIOD_ID'];
            $first = $r['FIRST_NAME'];
            $last = $r['LAST_NAME'];
            $collegedate = $r['COLLEGE_DATE'];
            $title = $r['TITLE'];
            $short_name = $r['SHORT_NAME'];
            $start = $r['START_DATE'];
            $end = $r['END_DATE'];

            if ($collegedate > $end) {
                $after = "Y";
            }

            if (($studentid == $studentid2) && ($courseid == $courseid2) && ($collegedate == $collegedate2) && ($start == $start2) && ($periodid == $periodid2)) {

                $URIcount++;

                if ($URIcount > $startrow && $URIcount < $endrow) {

                    echo "<input type=hidden name=delete value=true>";
                    echo "<input type=hidden name=studentidx[$count] value=$studentid>";
                    echo "<input type=hidden name=periodidx[$count] value=$courseid>";
                    echo "<input type=hidden name=collegedatex[$count] value=$collegedate>";

                    if ($yellow == 0) {
                        $color = 'F8F8F9';
                        $yellow++;
                    } else {
                        $color = Preferences('COLOR');
                        $yellow = 0;
                    }
                    echo "<tr class=odd><td ><input type=checkbox name=deletecheck[$count] value=$count></td><td bgcolor=#$color><font color=#000000><FONT size=-1>$first $last ($studentid)</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$short_name ($courseid)</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$start &nbsp</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$end &nbsp</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$collegedate</td></tr>";

                    $count++;
                }
            } else if (($collegedate > $end) && ($end != NULL) && ($start == $start2)) {

                $URIcount++;

                if ($URIcount > $startrow && $URIcount < $endrow) {

                    echo "<input type=hidden name=delete value=true>";
                    echo "<input type=hidden name=studentidx[$count] value=$studentid>";
                    echo "<input type=hidden name=periodidx[$count] value=$courseid>";
                    echo "<input type=hidden name=collegedatex[$count] value=$collegedate>";

                    if ($yellow == 0) {
                        $color = 'F8F8F9';
                        $yellow++;
                    } else {
                        $color = Preferences('COLOR');
                        $yellow = 0;
                    }
                    echo "<tr class=even><td ><input type=checkbox name=deletecheck[$count] value=$count></td><td bgcolor=#$color><font color=#000000><FONT size=-1>$first $last ($studentid)</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$short_name ($courseid)</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$start &nbsp</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$end &nbsp</td><td bgcolor=#$color><font color=#000000><FONT size=-1>$collegedate</td></tr>";

                    $count++;
                }
            } else {
                $duplicate = 0;
            }

            $studentid2 = $studentid;
            $courseid2 = $courseid;
            $periodid2 = $periodid;
            $collegedate2 = $collegedate;
            $first2 = $first;
            $last2 = $last;
            $title2 = $title;
            $start2 = $start;
            $end2 = $end;
        }
        if ($count == 0) {
            echo '<tr class=odd><td colspan=6><span class="text-alert">No Duplicates Found</span></td></tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>'; //.table-responsive
            echo '</div>'; //.tabbable
            echo '</div>'; //.panel
        } else {
            echo '</tbody>';
            echo '</table>';
            echo '</div>'; //.table-responsive
            echo '</div>'; //.tabbable
            echo '</div>'; //.panel
            echo '<br><input type=submit class="btn btn-primary" name=submit value=Delete>';
        }

        echo "</form>";
        $RET = " ";
    } else
        BackPrompt('No Students were found.');
}

function _makeTeacher($teacher, $column) {
    return substr($teacher, strrpos(str_replace(' - ', ' ^ ', $teacher), '^') + 2);
}

?>