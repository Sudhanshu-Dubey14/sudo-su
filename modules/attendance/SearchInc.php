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
if ($_openSIS['modules_search'] && $extra['force_search'])
    $_REQUEST['search_modfunc'] = '';
if (Preferences('SEARCH') != 'Y' && !$extra['force_search'])
    $_REQUEST['search_modfunc'] = 'list';
if ($extra['skip_search'] == 'Y')
    $_REQUEST['search_modfunc'] = 'list';
if ($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) {
    unset($_SESSION['new_sql']);
    unset($_SESSION['newsql']);
    unset($_SESSION['newsql1']);
    if ($_SESSION['college_roll_no'] && User('PROFILE') == 'admin' && $_REQUEST['college_roll_no'] == 'new') {
        unset($_SESSION['college_roll_no']);
        //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
    }
    switch (User('PROFILE')) {
        case 'admin':
        case 'teacher':
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['smc']);
                unset($_SESSION['g']);
                unset($_SESSION['p']);
                unset($_SESSION['smn']);
                unset($_SESSION['sm']);
                unset($_SESSION['sma']);
                unset($_SESSION['smv']);
                unset($_SESSION['s']);
            }
            
            $_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['stu_search']);
            } else if ($search_from_grade == 'true') {
                $_SESSION['stu_search']['search_from_grade'] = 'true';
            }
            PopTable('header', 'Find a Student');
            if ($extra['pdf'] != true)
                echo "<FORM class='form-horizontal' name=search id=search action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM class='form-horizontal' name=search id=search action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";

            Search_absence_summary('general_info');
            if ($extra['search'])
                echo $extra['search'];
            Search_absence_summary('student_fields');
            # ---   Advanced Search Start ---------------------------------------------------------- #
            echo '<div style="height:10px;"></div>';
            echo '<input type=hidden name=sql_save_session value=true />';
            echo '<div id="addiv" class="pt-20">';
            echo '</div>';

            echo '<div id="searchdiv" class="pt-20 mt-20 well" style="display:none;">';
            echo '<div><a href="javascript:void(0);" onclick="hide_search_div();" class="text-pink"><i class="icon-square-left"></i> Back to Basic Search</a></div>';

            echo '<div class="row">';
            echo '<div class="col-lg-12">';
            echo '<div class="form-group pt-15"><label class="control-label col-lg-2 text-right">Comments</label><div class="col-lg-10"><input type=text name="mp_comment" size=30 placeholder="Comments" class="form-control"></div></div>';
            echo '</div>'; //.col-lg-12
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Birthday</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">From</label><div class="col-lg-8">' . SearchDateInput('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">To</label><div class="col-lg-8">' . SearchDateInput('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Goal and Progress</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Title</label><div class="col-lg-8"><input type=text name="goal_title" placeholder="Goal Title" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Description</label><div class="col-lg-8"><input type=text name="goal_description" placeholder="Goal Description" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Period</label><div class="col-lg-8"><input type=text name="progress_name" placeholder="Progress Period" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Assessment</label><div class="col-lg-8"><input type=text name="progress_description" placeholder="Progress Assessment" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Doctor\'s Note</label><div class="col-lg-8"><input type=text name="doctors_note_comments" placeholder="Doctor\'s Note" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Immunization</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Type</label><div class="col-lg-8"><input type=text name="type" size=30 placeholder="Type" class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments</label><div class="col-lg-8"><input type=text name="imm_comments" placeholder="Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical Alert</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Alert</label><div class="col-lg-8"><input type=text name="med_alrt_title" placeholder="Alert" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Nurse Visit</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Reason</label><div class="col-lg-8"><input type=text name="reason" size=30 placeholder="Reason" class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Result</label><div class="col-lg-8"><input type=text name="result" size=30 placeholder="Result" class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments</label><div class="col-lg-8"><input type=text name="med_vist_comments" placeholder="Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row


            echo '</div>';



            # ---   Advanced Search End ----------------------------------------------------------- #




            echo '<div class="row">';
            echo '<div class="col-md-12">';
            if (User('PROFILE') == 'admin') {
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=address_group value=Y' . (Preferences('DEFAULT_FAMILIES') == 'Y' ? ' CHECKED' : '') . '> Group by Family</label>';
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=_search_all_colleges value=Y' . (Preferences('DEFAULT_ALL_COLLEGES') == 'Y' ? ' CHECKED' : '') . '> Search All Colleges</label>';
            }
            echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=include_inactive value=Y>Include Inactive students</label>';
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row
            echo '<hr/>';
            if ($extra['pdf'] != true)
                echo "<INPUT type=SUBMIT class=\"btn btn-primary\" value='Submit' onclick='return formcheck_student_advnc_srch();formload_ajax(\"search\");'> &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='Reset'>";
            else
                echo "<INPUT type=SUBMIT class=\"btn btn-primary\" value='Submit' onclick='return formcheck_student_advnc_srch();'> &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='Reset'>";
            echo '<a href="javascript:void(0);" onclick="show_search_div();" class="text-pink m-l-10"><i class="icon-cog"></i> Advanced Search</a>';
            echo '</FORM>';
            // set focus to last name text box
            echo '<script type="text/javascript"><!--
				document.search.last.focus();
				--></script>';
            PopTable('footer');
            break;

        case 'parent':
        case 'student':
            PopTable('header', 'Search');
            if ($extra['pdf'] != true)
                echo "<FORM action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";

            if ($extra['search'])
                echo $extra['search'];

            echo Buttons('Submit', 'Reset');
            echo '</FORM>';
            PopTable('footer');
            break;
    }
}
else {
    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'students/Student.php';

    if ($_REQUEST['address_group']) {
        $extra['SELECT'] .= ',sam.ID AS ADDRESS_ID';
        if (!($_REQUEST['expanded_view'] == 'true' || $_REQUEST['addr'] || $extra['addr']))
            $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (sam.COLLEGE_ROLL_NO=ssm.COLLEGE_ROLL_NO AND sam.TYPE=\'Home Address\')' . $extra['FROM'];
        $extra['group'] = array('ADDRESS_ID');
    }
    $students_RET = GetStuList_Absence_Summary($extra);
    if ($_REQUEST['address_group']) {
        // if address_group specified but only one address returned then convert to ungrouped
        if (count($students_RET) == 1) {
            $students_RET = $students_RET[key($students_RET)];
            unset($_REQUEST['address_group']);
        } else
            $extra['LO_group'] = array('ADDRESS_ID');
    }
    if ($extra['array_function'] && function_exists($extra['array_function']))
        if ($_REQUEST['address_group'])
            foreach ($students_RET as $id => $student_RET)
                $students_RET[$id] = $extra['array_function']($student_RET);
        else
            $students_RET = $extra['array_function']($students_RET);

    $LO_columns = array('FULL_NAME' => 'Student', 'COLLEGE_ROLL_NO' => 'College Roll No', 'ALT_ID' => 'Alternate ID', 'GRADE_ID' => 'Grade', 'PHONE' => 'Phone');
    $name_link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]";
    $name_link['FULL_NAME']['variables'] = array('college_roll_no' => 'COLLEGE_ROLL_NO');
    if ($_REQUEST['_search_all_colleges'])
        $name_link['FULL_NAME']['variables'] += array('college_id' => 'COLLEGE_ID');

    if (is_array($extra['link']))
        $link = $extra['link'] + $name_link;
    else
        $link = $name_link;
    if (is_array($extra['columns_before'])) {
        $columns = $extra['columns_before'] + $LO_columns;
        $LO_columns = $columns;
    }

    if (is_array($extra['columns_after']))
        $columns = $LO_columns + $extra['columns_after'];
    if (!$extra['columns_before'] && !$extra['columns_after'])
        $columns = $LO_columns;

    if (count($students_RET) > 1 || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt'] == false && count($students_RET) == 0) || ($extra['Redirect'] === false && count($students_RET) == 1)) {
        echo '<div class="panel panel-default">';
        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['expanded_view']);
        if ($_REQUEST['expanded_view'] != 'true' && !UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true class=big_font ><i class=\"icon-square-down-right\"></i> Expanded View</A>", $extra['header_right']);
            DrawHeader(str_replace('<BR>', '<BR> &nbsp;', substr($_openSIS['SearchTerms'], 0, -4)));
        } elseif (!UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false class=big_font><i class=\"icon-square-up-left\"></i> Original View</A>", $extra['header_right']);
            DrawHeader(str_replace('<BR>', '<BR> &nbsp;', substr($_openSIS['Search'], 0, -4)));
        }
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }
        if (!$extra['singular'] || !$extra['plural'])
            if ($_REQUEST['address_group']) {
                $extra['singular'] = 'Family';
                $extra['plural'] = 'Families';
            } else {
                $extra['singular'] = 'Student';
                $extra['plural'] = 'students';
            }

        echo '<div class="panel-body">';
        echo "<div id='students'>";
        ListOutput($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
        echo "</div>"; //#students
        echo "</div>"; //.panel-body
        echo "</div>"; //.panel
    } elseif (count($students_RET) == 1) {
        if (count($link['FULL_NAME']['variables'])) {
            foreach ($link['FULL_NAME']['variables'] as $var => $val)
                $_REQUEST[$var] = $students_RET['1'][$val];
        }
        if (!is_array($students_RET[1]['COLLEGE_ROLL_NO'])) {
            $_SESSION['college_roll_no'] = $students_RET[1]['COLLEGE_ROLL_NO'];
            if (User('PROFILE') == 'admin')
                $_SESSION['UserCollege'] = $students_RET[1]['LIST_COLLEGE_ID'];
            if (User('PROFILE') == 'teacher')
                $_SESSION['UserCollege'] = $students_RET[1]['COLLEGE_ID'];

            //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            unset($_REQUEST['search_modfunc']);
        }
        if ($_REQUEST['modname'] != $_REQUEST['next_modname']) {
            $modname = $_REQUEST['next_modname'];
            if (strpos($modname, '?'))
                $modname = substr($_REQUEST['next_modname'], 0, strpos($_REQUEST['next_modname'], '?'));
            if (strpos($modname, '&'))
                $modname = substr($_REQUEST['next_modname'], 0, strpos($_REQUEST['next_modname'], '&'));
            if ($_REQUEST['modname'])
                $_REQUEST['modname'] = $modname;
            include('modules/' . $modname);
        }
    } else
        BackPrompt('No students were found.');
}
?>