<?php
include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//$_REQUEST['USERINFO_FIRST_NAME']=;
//        $_REQUEST['USERINFO_LAST_NAME']=;
//        
//        $_REQUEST['USERINFO_EMAIL']=;
//        $_REQUEST['USERINFO_MOBILE']=;
//        $_REQUEST['USERINFO_SADD']=;
//        $_REQUEST['USERINFO_CITY'] =;
//        $_REQUEST['USERINFO_STATE'] =;
//        $_REQUEST['USERINFO_ZIP']=;

//echo $_REQUEST['USERINFO_FIRST_NAME'];
//echo '<br>';
//echo  $_REQUEST['USERINFO_LAST_NAME'];
if ($_REQUEST['USERINFO_FIRST_NAME'] || $_REQUEST['USERINFO_LAST_NAME'] || $_REQUEST['USERINFO_EMAIL'] || $_REQUEST['USERINFO_MOBILE'] || $_REQUEST['USERINFO_SADD'] || $_REQUEST['USERINFO_CITY'] || $_REQUEST['USERINFO_STATE'] || $_REQUEST['USERINFO_ZIP']) {
                        $stf_ids = '';
                        $sql = 'SELECT distinct stf.STAFF_ID AS BUTTON , stf.STAFF_ID,CONCAT(stf.FIRST_NAME," ",stf.LAST_NAME) AS FULLNAME, CONCAT(s.FIRST_NAME," ",s.LAST_NAME) AS STUFULLNAME,stf.PROFILE,stf.EMAIL FROM people stf';
                        $sql_where = 'WHERE stf.PROFILE_ID=4 AND s.COLLEGE_ROLL_NO!=' . UserStudentID() . ' ';
                        if ($_REQUEST['USERINFO_FIRST_NAME'] || $_REQUEST['USERINFO_LAST_NAME'] || $_REQUEST['USERINFO_EMAIL'] || $_REQUEST['USERINFO_MOBILE']) {
                            if ($_REQUEST['USERINFO_FIRST_NAME']!='')
                                $sql_where.= 'AND LOWER(stf.FIRST_NAME) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_FIRST_NAME']))) . '%\' ';
                            if ($_REQUEST['USERINFO_LAST_NAME']!='')
                                $sql_where.= 'AND LOWER(stf.LAST_NAME) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_LAST_NAME']))) . '%\' ';
                            if ($_REQUEST['USERINFO_EMAIL']!='')
                                $sql_where.= 'AND LOWER(stf.EMAIL) = \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_EMAIL']))) . '\' ';
                            if ($_REQUEST['USERINFO_MOBILE']!='')
                                $sql_where.= 'AND stf.CELL_PHONE = \'' . str_replace("'", "''", trim($_REQUEST['USERINFO_MOBILE'])) . '\' ';
                        }
                        if ($_REQUEST['USERINFO_SADD'] || $_REQUEST['USERINFO_CITY'] || $_REQUEST['USERINFO_STATE'] || $_REQUEST['USERINFO_ZIP']) {
                            $sql.=' LEFT OUTER JOIN student_address sa on sa.PEOPLE_ID=stf.STAFF_ID';
                            $sql_where.='  AND sa.TYPE IN (\'Primary\',\'Secondary\',\'Other\') ';
                            if ($_REQUEST['USERINFO_SADD']!='')
                                $sql_where.= ' AND LOWER(STREET_ADDRESS_1) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_SADD']))) . '%\' ';
                            if ($_REQUEST['USERINFO_CITY']!='')
                                $sql_where.= ' AND LOWER(CITY) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_CITY']))) . '%\' ';
                            if ($_REQUEST['USERINFO_STATE']!='')
                                $sql_where.= ' AND LOWER(STATE) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_STATE']))) . '%\' ';
                            if ($_REQUEST['USERINFO_ZIP']!='')
                                $sql_where.= ' AND ZIPCODE = \'' . str_replace("'", "''", trim($_REQUEST['USERINFO_ZIP'])) . '\' ';
                        }

                        $sql.=' Left outer join students_join_people sju on stf.STAFF_ID=sju.PERSON_ID Left outer join students s on s.COLLEGE_ROLL_NO = sju.COLLEGE_ROLL_NO  ';
                        $sql_where.= '  AND LOWER(stf.FIRST_NAME)<>\'\' AND LOWER(stf.LAST_NAME)<>\'\' AND sju.PERSON_ID NOT IN (SELECT PERSON_ID FROM students_join_people WHERE COLLEGE_ROLL_NO=' . UserStudentID() . ') GROUP BY sju.PERSON_ID';

                        $searched_staffs = DBGet(DBQuery($sql . $sql_where), array('BUTTON' => 'makeChooseCheckbox'));
                        foreach ($searched_staffs as $key => $value) {
                            $stf_usrname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $value['STAFF_ID'] . ' AND PROFILE_ID=4'));
                            $searched_staffs[$key]['USERNAME'] = $stf_usrname[1]['USERNAME'];
                        }
                    } else {

                        $sql = 'SELECT stf.STAFF_ID AS BUTTON , stf.STAFF_ID,CONCAT(stf.FIRST_NAME," ",stf.LAST_NAME) AS FULLNAME, CONCAT(s.FIRST_NAME," ",s.LAST_NAME) AS STUFULLNAME,stf.PROFILE,stf.EMAIL FROM people stf left outer join students_join_people sju on stf.STAFF_ID=sju.PERSON_ID left outer join students s on s.COLLEGE_ROLL_NO = sju.COLLEGE_ROLL_NO  WHERE  s.COLLEGE_ROLL_NO!=' . UserStudentID() . '  AND stf.FIRST_NAME<>\'\' AND stf.LAST_NAME<>\'\' AND sju.PERSON_ID NOT IN (SELECT PERSON_ID FROM students_join_people WHERE COLLEGE_ROLL_NO=' . UserStudentID() . ') Group by stf.STAFF_ID';

                        $searched_staffs = DBGet(DBQuery($sql), array('BUTTON' => 'makeChooseCheckbox'));
                        foreach ($searched_staffs as $key => $value) {
                            $stf_usrname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $value['STAFF_ID'] . ' AND PROFILE_ID=4'));
                            $searched_staffs[$key]['USERNAME'] = $stf_usrname[1]['USERNAME'];
                        }
                    }
                
                $singular = 'User';
                $plural = 'Users';
                $options['save'] = false;
                $options['print'] = false;
                $options['search'] = false;

                $columns = array('BUTTON' => 'Select any one', 'FULLNAME' => 'Name', 'USERNAME' => 'Username', 'EMAIL' => 'Email', 'STUFULLNAME' => 'Associated Student\'s Name');
                if ($_REQUEST['add_id'] == 'new')
                    echo '<FORM name=sel_staff id=sel_staff action="ForWindow.php?modname=' . $_REQUEST[modname] . '&modfunc=lookup&type=' . $_REQUEST['type'] . '&func=search&nfunc=status&ajax=' . $_REQUEST['ajax'] . '&add_id=new&address_id=' . $_REQUEST['address_id'] . '" METHOD=POST>';
                else
                    echo '<FORM name=sel_staff id=sel_staff action="ForWindow.php?modname=' . $_REQUEST[modname] . '&modfunc=lookup&type=' . $_REQUEST['type'] . '&func=search&nfunc=status&ajax=' . $_REQUEST['ajax'] . '&add_id=' . $_REQUEST['add_id'] . '&address_id=' . $_REQUEST['address_id'] . '" METHOD=POST>';
                echo '<span id="sel_err" class="text-danger"></span>';
//               print_r($searched_staffs);
                ListOutput($searched_staffs, $columns, $singular, $plural, false, $group = false, $options, 'ForWindow');
                 unset($_REQUEST['func']);
                if(!empty($searched_staffs))
                echo '<div id="select-people-div"><input type="button" value="Select" name="button" onclick="SelectedParent(\''.$_REQUEST['address_id'].'\',\''.$_REQUEST['p_type'].'\',\''.$_REQUEST['other_p_erson_id'].'\')"></div>';
                
                function makeChooseCheckbox($value, $title) {
    global $THIS_RET;
    if ($THIS_RET['BUTTON']) {
        return "<INPUT type=radio name=staff value=" . $THIS_RET['BUTTON'] . ">";
    }
}