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
DrawBC("Gradebook > " . ProgramTitle());
if (!$_REQUEST['LO_sort']) {
    $_REQUEST['LO_sort'] = "CUM_RANK";
    $_REQUEST['LO_direction'] = 1;
}
if ($_REQUEST['search_modfunc'] == 'list') {
    if (!$_REQUEST['mp'] && GetMP(UserMP(), 'POST_START_DATE'))
        $_REQUEST['mp'] = UserMP();
    elseif (strpos(GetAllMP('QTR', UserMP()), str_replace('E', '', $_REQUEST['mp'])) === false && strpos(GetChildrenMP('PRO', UserMP()), "'" . $_REQUEST['mp'] . "'") === false && GetMP(UserMP(), 'POST_START_DATE'))
        $_REQUEST['mp'] = UserMP();

    if (!$_REQUEST['mp'] && GetMP(GetParentMP('SEM', UserMP()), 'POST_START_DATE'))
        $_REQUEST['mp'] = GetParentMP('SEM', UserMP());

    $sem = GetParentMP('SEM', UserMP());
    if ($sem)
        $fy = GetParentMP('FY', $sem);
    else
        $fy = GetParentMP('FY', UserMP());
//	$pro = GetChildrenMP('PRO',UserMP());
//	$pros = explode(',',str_replace("'",'',$pro));
    $pro_grading = false;
    $pro_select = '';
//	foreach($pros as $pro)
//	{
//		if(GetMP($pro,'POST_START_DATE'))
//		{
//			if(!$_REQUEST['mp'])
//			{
//				$_REQUEST['mp'] = $pro;
//				$current_RET = DBGet(DBQuery('SELECT g.COLLEGE_ROLL_NO,g.REPORT_CARD_GRADE_ID,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_grades g,course_periods cp WHERE cp.COURSE_PERIOD_ID=g.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID='.$course_period_id.' AND g.MARKING_PERIOD_ID=\''.$_REQUEST['mp'].'\''),array(),array('COLLEGE_ROLL_NO'));
//			}
//			$pro_grading = true;
//			$pro_select .= "<OPTION value=".$pro.(($pro==$_REQUEST['mp'])?' SELECTED':'').">".GetMP($pro)."</OPTION><OPTION value=".$sem.(('E'.$sem==$_REQUEST['mp'])?' SELECTED':'').">".GetMP($sem).' Exam</OPTION>';
//		}
//	}
    //bjj keeping search terms
    $PHP_tmp_SELF = PreparePHP_SELF();

    echo "<FORM action=$PHP_tmp_SELF method=POST>";
    $mps_select = "<div class='form-inline'><div class='input-group'><span class='input-group-addon' id='marking_period_id'>Marking Period :</span><SELECT name=mp class='form-control' onChange='this.form.submit();'>";

    if (GetMP(UserMP(), 'POST_START_DATE'))
        $mps_select .= "<OPTION value=" . UserMP() . ">" . GetMP(UserMP()) . "</OPTION>";
    elseif ($_REQUEST['mp'] == UserMP())
        $_REQUEST['mp'] = $sem;

    if (GetMP($sem, 'POST_START_DATE'))
        $mps_select .= "<OPTION value=" . $sem . (($sem == $_REQUEST['mp']) ? ' SELECTED' : '') . ">" . GetMP($sem) . "</OPTION>";
    if (GetMP($fy, 'DOES_GRADES') == 'Y')
        $mps_select .= "<OPTION value=" . $fy . (($fy == $_REQUEST['mp']) ? ' SELECTED' : '') . ">" . GetMP($fy) . "</OPTION>";
    if ($pro_grading)
        $mps_select .= $pro_select;

    $mps_select .= '</SELECT></div></div>';
    echo '<div class="panel">';
    echo '<div class="panel-heading"><h6 class="panel-title">' . $mps_select . '</h6></div>';
    echo '</div>';
}

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
$extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
Widgets('letter_grade');
$extra['search'] .= '</div>'; //.well
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '<div class="col-lg-6">';
Widgets('course');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row



/*
 * Course Selection Modal Start
 */
echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h4 class="modal-title">Choose course</h4>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE COLLEGE_ID='" . UserCollege() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' Subject was' : ' Subjects were') . ' found.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>Subject</th></tr></thead>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</table>';
}
echo '</div>';
echo '<div class="col-md-4" id="course_modal"></div>';
echo '<div class="col-md-4" id="cp_modal"></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body
echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal




if (!$_REQUEST['list_gpa']) {
    $extra['SELECT'] .= ',sgc.gpa,sgc.weighted_gpa, sgc.unweighted_gpa,sgc.class_rank';

    if (strpos($extra['FROM'], 'student_mp_stats sms') === false) {
        $extra['FROM'] .= ',student_gpa_calculated sgc';
        $extra['WHERE'] .= ' AND sgc.COLLEGE_ROLL_NO=ssm.COLLEGE_ROLL_NO AND sgc.MARKING_PERIOD_ID=\'' . $_REQUEST['mp'] . '\'';
    }
}
if (User('PROFILE') == 'parent' || User('PROFILE') == 'student')
     $extra['WHERE'] .= ' AND sgc.COLLEGE_ROLL_NO=\''. UserStudentID().'\'';
$extra['columns_after'] = array('GPA' => 'GPA', 'UNWEIGHTED_GPA' => 'Unweighted GPA', 'WEIGHTED_GPA' => 'Weighted GPA', 'CLASS_RANK' => 'Class Rank');
$extra['link']['FULL_NAME'] = false;
$extra['new'] = true;

if (User('PROFILE') == 'parent' || User('PROFILE') == 'student')
    $_REQUEST['search_modfunc'] = 'list';
$COLLEGE_RET = DBGet(DBQuery('SELECT * from colleges where ID = \'' . UserCollege() . '\''));
Search('college_roll_no', $extra, 'true');

function _roundGPA($gpa, $column) {
    GLOBAL $COLLEGE_RET;
    return round($gpa * $COLLEGE_RET[1]['REPORTING_GP_SCALE'], 3);
}

?>