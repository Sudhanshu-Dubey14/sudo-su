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
if($_REQUEST['day_values'] && ($_POST['day_values'] || $_REQUEST['ajax']))
{
	foreach($_REQUEST['day_values'] as $id=>$values)
	{
		if($_REQUEST['day_values'][$id]['START_DATE'] && $_REQUEST['month_values'][$id]['START_DATE'] && $_REQUEST['year_values'][$id]['START_DATE'])
			$_REQUEST['values'][$id]['START_DATE'] = date('Y-m-d',strtotime($_REQUEST['day_values'][$id]['START_DATE'].'-'.$_REQUEST['month_values'][$id]['START_DATE'].'-'.$_REQUEST['year_values'][$id]['START_DATE']));
		elseif(isset($_REQUEST['day_values'][$id]['START_DATE']) && isset($_REQUEST['month_values'][$id]['START_DATE']) && isset($_REQUEST['year_values'][$id]['START_DATE']))
			$_REQUEST['values'][$id]['START_DATE'] = '';

		if($_REQUEST['day_values'][$id]['END_DATE'] && $_REQUEST['month_values'][$id]['END_DATE'] && $_REQUEST['year_values'][$id]['END_DATE'])
			$_REQUEST['values'][$id]['END_DATE'] = date('Y-m-d',strtotime($_REQUEST['day_values'][$id]['END_DATE'].'-'.$_REQUEST['month_values'][$id]['END_DATE'].'-'.$_REQUEST['year_values'][$id]['END_DATE']));
		elseif(isset($_REQUEST['day_values'][$id]['END_DATE']) && isset($_REQUEST['month_values'][$id]['END_DATE']) && isset($_REQUEST['year_values'][$id]['END_DATE']))
			$_REQUEST['values'][$id]['END_DATE'] = '';
	}
	if(!$_POST['values'])
		$_POST['values'] = $_REQUEST['values'];
}

$profiles_RET = DBGet(DBQuery("SELECT ID,TITLE FROM user_profiles ORDER BY ID"));
if((($_REQUEST['profiles'] && ($_POST['profiles']  || $_REQUEST['ajax'])) || ($_REQUEST['values'] && ($_POST['values'] || $_REQUEST['ajax']))) && AllowEdit())
{
	$notes_RET = DBGet(DBQuery('SELECT ID FROM notice_board WHERE (COLLEGE_ID=\''.UserCollege().'\' OR PUBLISHED_PROFILES LIKE \'%all%\') AND SYEAR=\''.UserSyear().'\''));

	foreach($notes_RET as $note_id)
	{
		 $_REQUEST['profiles'][$note_id]['all'];
            $note_id = $note_id['ID'];
                if($_REQUEST['profiles'][$note_id]['all']=='Y')
                    $allcollege='Y';
                else
                    $allcollege='';
		$_REQUEST['values'][$note_id]['PUBLISHED_PROFILES'] = '';
		foreach(array('all','admin','teacher','parent') as $profile_id)
			if($_REQUEST['profiles'][$note_id][$profile_id])
				$_REQUEST['values'][$note_id]['PUBLISHED_PROFILES'] .= ','.$profile_id;
		if(count($_REQUEST['profiles'][$note_id]))
		{
			foreach($profiles_RET as $profile)
			{
				$profile_id = $profile['ID'];

				if($_REQUEST['profiles'][$note_id][$profile_id])
					$_REQUEST['values'][$note_id]['PUBLISHED_PROFILES'] .= ','.$profile_id;
			}
		}
		if($_REQUEST['values'][$note_id]['PUBLISHED_PROFILES'])
			$_REQUEST['values'][$note_id]['PUBLISHED_PROFILES'] .= ',';
	}
}

if(clean_param($_REQUEST['values'],PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax']) && AllowEdit() && !$_REQUEST['portal_search'])
{
	foreach($_REQUEST['values'] as $id=>$columns)
	{ 
                        if(!(isset($columns['TITLE']) && trim($columns['TITLE'])==''))
                        {
		if($id!='new')
		{
                                                    $portal_RET=DBGet(DBQuery('SELECT START_DATE,END_DATE FROM notice_board WHERE ID=\''.$id.'\''));
                                                    $portal_RET=$portal_RET[1];
                                                    $syear_RET=DBGet(DBQuery('SELECT START_DATE,END_DATE FROm college_years WHERE COLLEGE_ID='.UserCollege().' AND SYEAR='.UserSyear()));
                                                    $syear_RET=$syear_RET[1];
//                                                    if((strtotime($columns['START_DATE'])>strtotime($columns['END_DATE']) && $columns['END_DATE']!='') || (strtotime($columns['START_DATE'])>strtotime($portal_RET['END_DATE']) && $portal_RET['END_DATE']!='') || (strtotime($portal_RET['START_DATE'])>strtotime($columns['END_DATE']) && $columns['END_DATE']!='')|| (isset ($columns['START_DATE']) && $columns['START_DATE']=='' && $columns['END_DATE']!='') || ($columns['START_DATE']!='' && strtotime($columns['START_DATE'])<strtotime($syear_RET['START_DATE'])) || ($columns['END_DATE']!='' && strtotime($columns['END_DATE'])>strtotime($syear_RET['END_DATE'])))
                                                    if((strtotime($columns['START_DATE'])>strtotime($columns['END_DATE']) && $columns['END_DATE']!='') || (strtotime($columns['START_DATE'])>strtotime($portal_RET['END_DATE']) && $portal_RET['END_DATE']!='') || (strtotime($portal_RET['START_DATE'])>strtotime($columns['END_DATE']) && $columns['END_DATE']!='')|| (isset ($columns['START_DATE']) && $columns['START_DATE']=='' && $columns['END_DATE']!='') )
                                                    {
                                                        ShowErrPhp('<b>Data not saved because  date range is not valid.</b>');
                                                    }
                                                    else
                                                    {
			$sql = 'UPDATE notice_board SET ';
                           $sql.='COLLEGE_ID='.UserCollege().', ';
                            
//                        }
#################### code differ for windows and Linux machine ########################
                                                    foreach($columns as $column=>$value)
                                                    {

                                                        $value=paramlib_validation($column,$value);

                                                            $value= singleQuoteReplace("'","\'",$value);


                                                       
                                                        $sql .= $column."='".trim($value)."',";    					// for Windows Machine 
 
                                                    }
			$sql = substr($sql,0,-1) . ' WHERE ID=\''.$id.'\'';
			$sql = str_replace('&amp;', "", $sql);
			$sql = str_replace('&quot', "", $sql);
			$sql = str_replace('&#039;', "", $sql);
			$sql = str_replace('&lt;', "", $sql);
			$sql = str_replace('&gt;', "", $sql);

			DBQuery($sql);
			
			
			# ----------------------- Start Date & End Date Fix Start During Update --------------------------------- #
			
			$sql_start_date_fix = 'UPDATE notice_board set start_date=NULL WHERE `start_date`=\'0000-00-00\'';
			DBQuery($sql_start_date_fix);
			
			$sql_end_date_fix = 'UPDATE notice_board set end_date=NULL WHERE `end_date`=\'0000-00-00\'';
			DBQuery($sql_end_date_fix);
			
			# ------------------------ Start Date & End Date Fix End During Update ---------------------------------- #
                                            }
			
		}
		else
		{
                        $syear_RET=DBGet(DBQuery('SELECT START_DATE,END_DATE FROM college_years WHERE COLLEGE_ID='.UserCollege().' AND SYEAR='.UserSyear()));
                        $syear_RET=$syear_RET[1];
                        if($columns['START_DATE']=='' ||  $columns['END_DATE']=='')
                        {
                            ShowErrPhp('<b>Date can not be blank.</b>');
                        }
//                        elseif((strtotime($columns['START_DATE'])<strtotime($syear_RET['START_DATE'])) || (strtotime($columns['END_DATE'])>strtotime($syear_RET['END_DATE'])))
                        elseif(strtotime($columns['START_DATE'])>strtotime($columns['END_DATE']))
                        {
                            ShowErrPhp('<b>Data not saved because  date range is not valid.</b>');
                        }
                        else
                        {
                       
			if(count($_REQUEST['profiles']['new']))
			{
                            if($_REQUEST['profiles']['new']['all']=='Y')
                                $allcollege='Y';
                            else
                                $allcollege='';
				foreach(array('all','admin','teacher','parent') as $profile_id)
				{
					if($_REQUEST['profiles']['new'][$profile_id])
						$_REQUEST['values']['new']['PUBLISHED_PROFILES'] .= $profile_id.',';
					$columns['PUBLISHED_PROFILES'] = ','.$_REQUEST['values']['new']['PUBLISHED_PROFILES'];
				}
				foreach($profiles_RET as $profile)
				{
					$profile_id = $profile['ID'];

					if($_REQUEST['profiles']['new'][$profile_id])
						$_REQUEST['values']['new']['PUBLISHED_PROFILES'] .= $profile_id.',';
					$columns['PUBLISHED_PROFILES'] = ','.$_REQUEST['values']['new']['PUBLISHED_PROFILES'];
				}
			}
			else
                        {
				$_REQUEST['values']['new']['PUBLISHED_PROFILES'] = '';
                                $allcollege='';
                        }
			$sql = 'INSERT INTO notice_board ';

			
			
			$fields = 'COLLEGE_ID,SYEAR,last_updated,PUBLISHED_USER,';
                        if($allcollege=='Y')
                            $values ='NULL,\''.UserSyear().'\',CURRENT_TIMESTAMP,\''.User('STAFF_ID').'\',';
                        else
                            $values = UserCollege().',\''.UserSyear().'\',CURRENT_TIMESTAMP,\''.User('STAFF_ID').'\',';

			$go = 0;
                                                        foreach($columns as $column=>$value)
                                                        {
                                                                if(trim($value))
                                                                {
                                                                    $value=paramlib_validation($column,$value);
                                                                    $fields .= $column.',';
                                                                   
                                                                        $value=  singleQuoteReplace("","",$value);
                                                       
                                                                      // for linux machine 
                                                                    $values .= "'".trim($value)."',";      					// for windows machine
                                                                    $go = true;
                                                                }
                                                        }
                                                        $sql .= '(' . substr($fields,0,-1) . ') values(' . substr($values,0,-1) . ')';
                                                    if($go){
                                                        $sql = str_replace('&amp;', "", $sql);
                                                        $sql = str_replace('&quot', "", $sql);
                                                        $sql = str_replace('&#039;', "", $sql);
                                                        $sql = str_replace('&lt;', "", $sql);
                                                        $sql = str_replace('&gt;', "", $sql);
                                                        DBQuery($sql);
                                                    }
                        }
                }
                        }  // Title validation ends to show error message add else after this line
	}
	unset($_REQUEST['values']);
	unset($_SESSION['_REQUEST_vars']['values']);
	unset($_REQUEST['profiles']);
	unset($_SESSION['_REQUEST_vars']['profiles']);
}

DrawBC("College Setup > ".ProgramTitle());

if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='remove' && AllowEdit())
{
	if(DeletePrompt_Portal('message'))
	{
           
		DBQuery('DELETE FROM notice_board WHERE ID=\''.paramlib_validation($column=SORT_ORDER,$_REQUEST[id]).'\'');
		unset($_REQUEST['modfunc']);
	}
}

if($_REQUEST['modfunc']!='remove')
{

      $sql = 'SELECT ID,SORT_ORDER,TITLE,CONTENT,START_DATE,END_DATE,PUBLISHED_PROFILES,CASE WHEN END_DATE IS NOT NULL AND END_DATE < CURRENT_DATE THEN \'Y\' ELSE NULL END AS EXPIRED FROM notice_board WHERE (COLLEGE_ID=\''.UserCollege().'\' OR PUBLISHED_PROFILES LIKE \'%all%\') AND SYEAR=\''.UserSyear().'\' ORDER BY EXPIRED DESC,SORT_ORDER,last_updated DESC';
	$QI = DBQuery($sql);
        $LO = DBGet(DBQuery($sql));
$portal_id_arr=array();
foreach($LO as $ti => $td)
{
    array_push($portal_id_arr,$td[ID]);
}
$portal_id=implode(',',$portal_id_arr);
	$notes_RET = DBGet($QI,array('TITLE'=>'_makeTextInput','CONTENT'=>'_makeContentInput','SORT_ORDER'=>'_makeTextInput','START_DATE'=>'_makePublishing'));

	$columns = array('TITLE'=>'Title','CONTENT'=>'Note','SORT_ORDER'=>'Sort Order','START_DATE'=>'Publishing Options');
	
	$link['add']['html'] = array('TITLE'=>_makeTextInput('','TITLE','placeholder="Title"'),'CONTENT'=>_makeContentInput('','CONTENT','placeholder="Note"'),'SHORT_NAME'=>_makeTextInput('','SHORT_NAME'),'SORT_ORDER'=>_makeTextInput('','SORT_ORDER','placeholder="Sort Order"'),'START_DATE'=>_makePublishing('','START_DATE'));
	$link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";
	$link['remove']['variables'] = array('id'=>'ID');

	echo "<FORM name=F2 id=F2 action=Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."&modfunc=update method=POST>";
	
        echo '<input type="hidden" name="h1" id="h1" value="'.$portal_id.'">';
        echo '<div id="students" class="panel panel-white">';
	ListOutput($notes_RET,$columns,'Note','Notes',$link);
        $count_note=count($notes_RET);

	echo '<div class="panel-footer"><div class="col-md-12 text-right">'.SubmitButton('Save','','class="btn btn-primary" onclick="formcheck_college_setup_portalnotes();"').'</div></div>';
	echo '</div></FORM>';
}

function _makeTextInput($value,$name, $options = '')
{
        global $THIS_RET;
        if($THIS_RET['ID'])
            $id = $THIS_RET['ID'];
        else
            $id = 'new';

        if($name!='TITLE')
            $extra = 'size=5 maxlength=10 class=form-control ';
        else 

            $extra = 'class=form-control ';
        if($name=='SORT_ORDER')
        {
            if($name=='SORT_ORDER')
            {
                if($id == 'new' || $THIS_RET['SORT_ORDER']=='')
                    $extra .= ' onkeydown="return numberOnly(event);" ';
                else
                    $extra .= ' onkeydown="return numberOnly(event);" ';
            }
        }
        $extra .= ' '.$options;        
        return TextInput($name=='TITLE' && $THIS_RET['EXPIRED']?array($value,'<FONT class=red>'.$value.'</FONT>'):$value,"values[$id][$name]",'',$extra);
}

function _makeContentInput($value,$name, $options = '')
{	global $THIS_RET;

	if($THIS_RET['ID'])
		$id = $THIS_RET['ID'];
	else
		$id = 'new';

	return TextareaInput($value,"values[$id][$name]",'','rows=16 cols=55 '.$options);
}

function _makePublishing($value,$name)
{	global $THIS_RET,$profiles_RET;

	if($THIS_RET['ID'])
		$id = $THIS_RET['ID'];
	else
		$id = 'new';

	$return = '<TABLE border=0 cellspacing=0 cellpadding=0 width=200 class=LO_field><tr></td><h4>Visible Between:</h4>';
     
        if($id!='new')
        {
        $return .= DateInputAY($value,"values[$id][$name]",$id+rand()).'<p class="text-center p-t-10 text-bold">&</p>';
        $return .= DateInputAY($THIS_RET['END_DATE'],"values[$id][END_DATE]",$id+1+rand());
        }
        else 
        {
            $return .= DateInputAY($value,"values[$id][$name]",0).'<p class="text-center p-t-10 text-bold">&</p>';
            $return .= DateInputAY($THIS_RET['END_DATE'],"values[$id][END_DATE]",-1);
        
        }
	$return .= '';

	if(!$profiles_RET)
		$profiles_RET = DBGet(DBQuery("SELECT ID,TITLE FROM user_profiles ORDER BY ID"));

	$return .= '<h4 class="p-t-15">Visible To: </h4>';
	foreach(array('all'=>'All College','admin'=>'Administrator w/Custom','teacher'=>'Teacher w/Custom','parent'=>'Parent w/Custom') as $profile_id=>$profile)
		$return .= "<div class=\"checkbox checkbox-switch switch-success switch-xs \"><label><INPUT type=checkbox name=profiles[$id][$profile_id] value=Y".(strpos($THIS_RET['PUBLISHED_PROFILES'],",$profile_id,")!==false?' CHECKED':'')."><span></span>$profile</label></div>";
	$i = 3;
	foreach($profiles_RET as $profile)
	{
		$i++;
		$return .= '<div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=profiles['.$id.']['.$profile['ID'].'] value=Y'.(strpos($THIS_RET['PUBLISHED_PROFILES'],",$profile[ID],")!==false?' CHECKED':'')."><span></span>$profile[TITLE]</label></div>";
//		if($i%4==0 && $i!=count($profile))
//			$return .= '<TR>';
	}
//	for(;$i%4!=0;$i++)
//		$return .= '<TD></TD>';
//	$return .= '</TR>';

//	$return .= '</TABLE>';
	$return .= '</TD></TR></TABLE>';
	return $return;
}



?>
