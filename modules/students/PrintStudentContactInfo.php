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
if($_REQUEST['modfunc']=='save')
{
	if(count($_REQUEST['st_arr']))
	{
	$st_list = '\''.implode('\',\'',$_REQUEST['st_arr']).'\'';
	$extra['WHERE'] = ' AND s.COLLEGE_ROLL_NO IN ('.$st_list.')';

        
        
        $extra['FROM']=' ,students_join_people sjp,people p,student_address sa';
        $extra['SELECT']=' ,sjp.EMERGENCY_TYPE AS CONTACT_TYPE,sjp.RELATIONSHIP AS RELATION,CONCAT(p.Last_Name," " ,p.First_Name) AS RELATION_NAME,sa.STREET_ADDRESS_2 as STREET,sa.STREET_ADDRESS_1 as ADDRESS,sa.CITY,sa.STATE,sa.ZIPCODE AS ZIP,p.WORK_PHONE,p.HOME_PHONE,p.CELL_PHONE,p.EMAIL AS EMAIL_ID';
        $extra['WHERE'] .=' AND sjp.college_roll_no=ssm.college_roll_no AND sjp.COLLEGE_ROLL_NO=sa.COLLEGE_ROLL_NO AND sjp.PERSON_ID=sa.PEOPLE_ID AND sjp.PERSON_ID=p.STAFF_ID';
        $extra['ORDER'] =' ,sa.ID';

                $RET = GetStuList($extra);
  
	if(count($RET))
	{
                        $column_name=array('COLLEGE_ROLL_NO'=>'College Roll No','ALT_ID'=>'Alternate ID','FULL_NAME'=>'Student','CONTACT_TYPE'=>'Type','RELATION'=>'Relation','RELATION_NAME'=>'Relation\'s Name','STREET'=>'Street','ADDRESS'=>'Address','CITY'=>'City','STATE'=>'State','ZIP'=>'Zip','WORK_PHONE'=>'Work Phone','HOME_PHONE'=>'Home Phone','CELL_PHONE'=>'Cell Phone','EMAIL_ID'=>'Email Address');
                        $singular='Student Contact';
                        $plural='Student Contacts';
                        $options=array('search' => false);

                        ListOutputPrint($RET, $column_name,$singular,$plural,$link=false,$group=false,$options);

	}
	else{
		ShowErrPhp('No Contacts were found.');
                                    for_error();
                        }
	}
	else{
		ShowErrPhp('You must choose at least one student.');
                                    for_error();
                        }
	unset($_SESSION['college_roll_no']);
	
	$_REQUEST['modfunc']=true;
}

if(!$_REQUEST['modfunc'])
{
	DrawBC("Students > ".ProgramTitle());

	if($_REQUEST['search_modfunc']=='list')
	{
		echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_search_all_colleges=$_REQUEST[_search_all_colleges]&_openSIS_PDF=true target=_blank method=POST>";
	

	}

	$extra['link'] = array('FULL_NAME'=>false);
	$extra['SELECT'] = ',s.COLLEGE_ROLL_NO AS CHECKBOX';
	$extra['functions'] = array('CHECKBOX'=>'_makeChooseCheckbox');
	$extra['columns_before'] = array('CHECKBOX'=>'</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'unused\');"><A>');
	$extra['options']['search'] = false;
	$extra['new'] = true;



	Search('college_roll_no',$extra);
	if($_REQUEST['search_modfunc']=='list')
	{
		echo '<div class="text-right p-r-20 p-b-20"><INPUT type=submit class="btn btn-primary" value=\'Print Contact Info for Selected Students\'></div>';
		echo "</FORM>";
	}
}

// GetStuList by default translates the grade_id to the grade title which we don't want here.
// One way to avoid this is to provide a translation function for the grade_id so here we
// provide a passthru function just to avoid the translation.

function _makeChooseCheckbox($value,$title)
{
//	return '<INPUT type=checkbox name=st_arr[] value='.$value.' checked>';
        global $THIS_RET;
    return "<input name=unused[$THIS_RET[COLLEGE_ROLL_NO]] value=" . $THIS_RET[COLLEGE_ROLL_NO] . "  type='checkbox' id=$THIS_RET[COLLEGE_ROLL_NO] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[COLLEGE_ROLL_NO]);' />";
}
?>
