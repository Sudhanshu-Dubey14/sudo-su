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

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save') {
    if (count($_SESSION['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_SESSION['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STAFF_ID IN (' . $st_list . ')';

        echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
        echo "<tr><td width=105>" . DrawLogo() . "</td><td style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetCollege(UserCollege()) . "<div style=\"font-size:12px;\">User Advanced Report</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />Powered by openSIS</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
        echo "<table >";
        include('modules/miscellaneous/UserExport.php');
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'call') {
    $_SESSION['st_arr'] = $_REQUEST['st_arr'];

    echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&head_html=Staff+Advanced+Report&modfunc=save&search_modfunc=list&_openSIS_PDF=true&_dis_user=$_REQUEST[_dis_user]&_search_all_colleges=$_REQUEST[_search_all_colleges] method=POST target=_blank>";
    echo '<DIV id=fields_div></DIV>';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    include('modules/miscellaneous/UserExport.php');
    echo '</div>';
    echo '<div class="panel-footer text-right"><INPUT type=submit value="Create Report for Selected users" class="btn btn-primary"></div>';
    echo '</div>';
    echo "</FORM>";
}

if (!$_REQUEST['modfunc'] || $_REQUEST['modfunc'] == 'list') {
    DrawBC("users > " . ProgramTitle());

    if ($_REQUEST['modfunc'] == 'list') {

        $extra['columns_after'] = array('LAST_LOGIN' => 'Last Login');
        $extra['functions'] = array('LAST_LOGIN' => 'makeLogin');

        $extra['SELECT'] = ',LAST_LOGIN,CONCAT(\'<INPUT type=checkbox name=st_arr[] value=\',s.STAFF_ID,\' checked>\') AS CHECKBOX';
        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
        $extra['options']['search'] = false;
        
        echo "<FORM action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=call method=POST>";
        echo '<DIV id=fields_div></DIV>';
        
        if ($_REQUEST['_dis_user'])
            echo '<INPUT type=hidden name=_dis_user value=' . strip_tags(trim($_REQUEST['_dis_user'])) . '>';
        if ($_REQUEST['_search_all_colleges'])
            echo '<INPUT type=hidden name=_search_all_colleges value=' . strip_tags(trim($_REQUEST['_search_all_colleges'])) . '>';
        
        SearchStaff('staff_id', $extra);
        
        if ($_SESSION['count_stf'] != '0') {
            unset($_SESSION['count_stf']);
            echo '<div class="text-right"><INPUT type=submit value="Create Report for Selected users" class="btn btn-primary"></div>';
        }
        
        echo "</FORM>";
    } else {
        
        unset($_SESSION['staff_id']);
        SearchStaff('staff_id', $extra);
        
    }
}
?>
