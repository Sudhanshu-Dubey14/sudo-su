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
include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';

if ($_REQUEST['table_name'] != '' && $_REQUEST['table_name'] == 'course_periods') {

    $sql = "SELECT * FROM course_periods WHERE COURSE_ID='$_REQUEST[id]'AND (marking_period_id IS NOT NULL AND marking_period_id IN(" . GetAllMP(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ") OR marking_period_id IS NULL AND '" . date('Y-m-d') . "' <= end_date) ORDER BY TITLE";
    $QI = DBQuery($sql);

    $coursePeriods_RET = DBGet($QI);
    $html = 'cp_modal||';
    $html.='<h6>' . count($coursePeriods_RET) . ((count($coursePeriods_RET) == 1) ? ' Period was' : ' Periods were') . ' found.</h6>';
    if (count($coursePeriods_RET) > 0) {
        $html.='<table class="table table-bordered"><thead><tr class="alpha-grey"><th>Course Periods</th></tr></thead>';
        $html.='<tbody>';

        foreach ($coursePeriods_RET as $val) {
            $subject_id = DBGet(DBQuery('SELECT SUBJECT_ID FROM courses WHERE COURSE_ID=' . $val['COURSE_ID']));
//        $html.= '<tr><td><a href=javascript:void(0); onclick="selectCpModal(\'course_div\',\''.$val['TITLE'].'\');">'.$val['TITLE'].'</a></td></tr>';
//        $html.= '<tr><td><a href="Modules.php?modname=scheduling/MassSchedule.php&subject_id='.$subject_id[1]['SUBJECT_ID'].'&course_id='.$val['COURSE_ID'].'&modfunc=choose_course&course_period_id='.$val['COURSE_PERIOD_ID'].'" >'.$val['TITLE'].'</a></td></tr>';
            $html.= '<tr><td><a href=javascript:void(0); onclick="MassScheduleSessionSet(\'' . $val['TITLE'] . '\',\'' . $subject_id[1]['SUBJECT_ID'] . '\',\'' . $val['COURSE_ID'] . '\',\'' . $val['COURSE_PERIOD_ID'] . '\');">' . $val['TITLE'].' - '.$val['SHORT_NAME'] . '</a></td></tr>';
        }
        $html.='</tbody>';
        $html.='</table>';
    }
}

if ($_REQUEST['table_name'] != '' && $_REQUEST['table_name'] == 'courses') {

    $sql = "SELECT COURSE_ID,c.TITLE, CONCAT_WS(' - ',c.short_name,c.title) AS GRADE_COURSE FROM courses c LEFT JOIN college_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='$_REQUEST[id]' ORDER BY c.TITLE";
    $QI = DBQuery($sql);
    $courses_RET = DBGet($QI);
    $html = 'course_modal||';
    $html.= '<h6>' . count($courses_RET) . ((count($courses_RET) == 1) ? ' Course was' : ' Courses were') . ' found.</h6>';
    if (count($courses_RET) > 0) {
        $html.='<table  class="table table-bordered"><thead><tr class="alpha-grey"><th>Course</th></tr></thead>';
        $html.='<tbody>';
        foreach ($courses_RET as $val) {

            $html.= '<tr><td><a href=javascript:void(0); onclick="MassScheduleModal(' . $val['COURSE_ID'] . ',\'course_periods\')">' . $val['GRADE_COURSE'] . '</a></td></tr>';
        }
        $html.='</tbody>';
        $html.='</table>';
    }
}

echo $html;
?>
