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

function DeletePromptCommon($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class='btn btn-danger' value=OK> &nbsp; <INPUT type=button class='btn btn-primary' name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&category_id=" . strip_tags(trim($_REQUEST['category_id'])) . "&table=" . strip_tags(trim($_REQUEST['table'])) . "&include=" . strip_tags(trim($_REQUEST['include'])) . "&subject_id=" . strip_tags(trim($_REQUEST['subject_id'])) . "&course_id=" . strip_tags(trim($_REQUEST['course_id'])) . "&course_period_id=" . strip_tags(trim($_REQUEST['course_period_id'])) . "\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else {
        return true;
    }
}

function DeletePromptStaffCert($title, $certificate_id, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm ' . (strpos($action, ' ') === false ? ucwords($action) : $action));
        echo "<CENTER><h4>Are you sure you want to $action that " . (strpos($title, ' ') === false ? ucwords($title) : $title) . "?</h4><br><INPUT type=submit class=\"btn btn-primary\" value=OK onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&include=CertificationInfoInc&custom=staff&category_id=4&delete_ok=1&certification_id=$certificate_id\");'>&nbsp;<INPUT type=button class='btn btn-primary' name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&include=CertificationInfoInc&custom=staff&category_id=4\");'></CENTER>";
        PopTable('footer');
        return false;
    } else {
        return true;
    }
}

function DeletePrompt($title, $action = 'delete', $close = 'n') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        if ($close == 'n')
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='javascript:history.go(-1);'></FORM></CENTER>";
        if ($close == 'y')
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.close();'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePrompt_activity($title, $action = 'delete', $close = 'n') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        if ($close == 'n')
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=collegesetup/Periods.php\"'></FORM></CENTER>";
        if ($close == 'y')
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.close();'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePrompt_sys_maintain($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));

        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=collegesetup/SystemPreference.php&page_display=MAINTENANCE\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePromptModRequest($title, $action = 'delete', $close = 'n') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        if ($close == 'n') {
            $req_mod_name = strip_tags(trim($_REQUEST[modname]));
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=$req_mod_name\"'></FORM></CENTER>";
        }if ($close == 'y')
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.close();'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePromptModContacts($title, $action = 'delete', $close = 'n') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        if ($close == 'n') {
            $req_mod_name = strip_tags(trim($_REQUEST[modname]));
            $req_addr_id = strip_tags(trim($_REQUEST[address_id]));
            $req_per_id = strip_tags(trim($_REQUEST[person_id]));
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=$req_mod_name&include=AddressInc&address_id=$req_addr_id&person_id=$req_per_id&con_info=old\"'></FORM></CENTER>";
        }if ($close == 'y')
            echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.close();'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeleteMail($title, $action = 'delete', $location, $isTrash = 0) {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF = str_replace(' ', '+', $PHP_tmp_SELF);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        if (!$isTrash) {
            PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        } else {
            PopTable('header', '' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : '') . ' Forever');
        }
        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=$location\"'></FORM></CENTER>";

        PopTable('footer');
        return false;
    } else
        return true;
}

//TODO:Use this instead of previous
function DeletePromptMod($title, $queryString = '', $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm ' . (strpos($action, ' ') === false ? ucwords($action) : $action));
        echo "<CENTER><h4>Are you sure you want to $action that " . (strpos($title, ' ') === false ? ucwords($title) : $title) . "?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&$queryString\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePromptLog($title, $queryString = '', $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm ' . (strpos($action, ' ') === false ? ucwords($action) : $action));
        echo "<CENTER><h4>Are you sure you want to $action that " . (strpos($title, ' ') === false ? ucwords($title) : $title) . "?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&$queryString\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DuplicateStudent($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);
    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm ' . (strpos($action, ' ') === false ? ucwords($action) : $action));
        echo "<CENTER><h4>Duplicate student found. There is already a student with the same information. Do you want to proceed?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&stu_id=$_REQUEST[college_roll_no]&include_a=$_REQUEST[include]\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DuplicateFile($title, $target_path) {
    $tmp_REQUEST = $_REQUEST;
    $tmp_REQUEST1 = $_FILES;
    $action = "upload";
    unset($tmp_REQUEST['delete_ok']);
    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    $PHP_tmp_SELF1 = PreparePHP_SELF1($tmp_REQUEST1);
    $PHP_tmp_SELF = $PHP_tmp_SELF . $PHP_tmp_SELF1;
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm ' . (strpos($action, ' ') === false ? ucwords($action) : $action));
        echo "<CENTER><h4>Duplicate file found. There is already a file with the same name. Do you want to proceed?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=button class=\"btn btn-danger\" value=OK onclick='load_link(\"Modules.php?modname=students/Student.php&include=FilesInc&category_id=7&stu_id=$_REQUEST[college_roll_no]\");'>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=students/Student.php&include=FilesInc&category_id=7&delete_msg=yes&target_path=$target_path&stu_id=$_REQUEST[college_roll_no]\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePromptAssignment($title, $pid = 0, $action = 'delete') {

    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        if ($pid == 0) {
            echo '<BR>';
            PopTable('header', $title);
            echo "<CENTER><h4>All the grades with respect to this Assignment will be deleted. Do you still wish to delete the assignment?</h4><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<BR><BR><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=grades/Assignments.php\"'></FORM></CENTER>";
            PopTable('footer');
            return false;
        } elseif ($pid != 0) {
            echo '<BR>';
            PopTable('header', $title);
            echo "<CENTER><h4>All the grades with respect to this Assignment will be deleted. Do you still wish to delete the assignment?</h4><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<BR><BR><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=grades/Assignments.php&assignment_type_id=$pid\"'></FORM></CENTER>";
            PopTable('footer');
            return false;
        }
    } else
        return true;
}

function UnableDeletePrompt($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Unable to Delete');
        echo "<CENTER><h4>$title</h4><br><FORM action=Modules.php?modname=$_REQUEST[modname] METHOD=POST><INPUT type=submit class=\"btn btn-danger\" name=delete_cancel value=Ok></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

//TODO:Use this instead of previous
function UnableDeletePromptMod($title, $action = 'delete', $queryString = '') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Unable to Delete');
        echo "<CENTER><h4>$title</h4><br><FORM action=Modules.php?modname=$_REQUEST[modname]&$queryString METHOD=POST><INPUT type=submit class=\"btn btn-danger\" name=delete_cancel value=Ok></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function Prompt($title = 'Confirm', $question = '', $message = '', $pdf = '') {

    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', $title);
        echo "<CENTER><h4>$question</h4><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<BR><BR><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='javascript:history.go(-1);'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function Prompt_Home($title = 'Confirm', $question = '', $message = '', $pdf = '') {

    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        PopTable('header', $title);
        echo "<div class=\"text-center\"><h4>$question</h4><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<BR><BR><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=miscellaneous/Portal.php\"'></FORM></div>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePrompt_Portal($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=collegesetup/PortalNotes.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePrompt_Period($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=collegesetup/Periods.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePrompt_GradeLevel($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=collegesetup/GradeLevels.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePrompt_Sections($title, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm' . (strpos($action, ' ') === false ? ' ' . ucwords($action) : ''));
        echo "<CENTER><h4>Are you sure you want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='window.location=\"Modules.php?modname=collegesetup/Sections.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePromptBigString($title = '', $queryString = '') {

    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>' . $queryString;
        PopTable('header', 'Confirm Delete');
        echo "<CENTER><h4>Are you sure you want to $action that " . (strpos($title, ' ') === false ? ucwords($title) : $title) . "?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&$queryString\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function DeletePromptFilesEncoded($title, $queryString, $action = 'delete') {
    $tmp_REQUEST = $_REQUEST;

    unset($tmp_REQUEST['delete_ok']);

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', 'Confirm ' . (strpos($action, ' ') === false ? ucwords($action) : $action));
        echo "<CENTER><h4>Are you sure you want to $action " . (strpos($title, ' ') === false ? ucwords(base64_decode($title)) : $title) . "?</h4><br><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><INPUT type=submit class=\"btn btn-danger\" value=OK>&nbsp;<INPUT type=button class=\"btn btn-primary\" name=delete_cancel value=Cancel onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&$queryString\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

?>