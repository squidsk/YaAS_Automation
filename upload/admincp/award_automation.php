<?php

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'award_version_info.php');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/class_bbcode.php');
$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

$this_script = 'award_version_info';

global $vbulletin;

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminusers'))
{
	print_cp_no_permission();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
print_cp_header($vbphrase['award_automation_version']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'manage';
}

if ($_REQUEST['do'] == 'manage')
{
	echo "<center>[ <a href='award_automation.php?do=add'>Add New Automation Task</a> ]</center>";
	
	// Grab List of Automation Tasks
	$autoTasks = $db->query_read("
	SELECT *
	FROM " . TABLE_PREFIX . "award_automation
	");
	
	// Construct Table Header
	print_form_header('', '');
	print_table_header($vbphrase['award_manager'], 6);
	print_cells_row(array(
			$vbphrase['award_automation_name'],
			$vbphrase['award_reason'],
			$vbphrase['award_automation_type'],
			$vbphrase['award_automation_criteria'],
			$vbphrase['award_automation_awardid'],
			$vbphrase['controls']
			), 1, '', -1);
			
		while ($celldata = $db->fetch_array($autoTasks))
		{
		
		$cell = array();
		
		$cell[] = $celldata[auto_name];
		$cell[] = $celldata[auto_issuereason];
		$cell[] = $celldata[auto_type];
		$cell[] = $celldata[auto_criteria];
		$cell[] = $celldata[auto_awardid];
		$cell[] = "[ <a href='award_automation.php?do=delete&taskid=$celldata[auto_awardid]'>Delete</a> ]";
		
	print_cells_row($cell, 0, '', 1);
	}
	 print_table_footer(6, '', '', 0);  
}

if ($_REQUEST['do'] == 'add')
{
	print_form_header('award_automation', 'insert');
	print_table_header($vbphrase['add_new_award']);
	
	print_input_row($vbphrase['award_automation_name'], 'aa_name');
	print_input_row($vbphrase['award_automation_awardid'], 'aa_awardid');
	print_select_row($vbphrase['award_automation_type'], 'aa_criteriatype', 
		array(
		'postcount' => $vbphrase['award_automation_criteria_postcount'], 
		'usergroup' => $vbphrase['award_automation_criteria_usergroup'],
		'daysasmember' => $vbphrase['award_automation_criteria_daysasmember']
		));  
	print_input_row($vbphrase['award_automation_criteria'], 'aa_criteria');
	print_textarea_row($vbphrase['award_reason'], 'aa_reason');
	print_yes_no_row($vbphrase['award_automation_active_task'], 'aa_active');
	
	print_submit_row($vbphrase['save']);
}

if ($_POST['do'] == 'insert')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'aa_name' => TYPE_STR,
		'aa_awardid' => TYPE_INT,
		'aa_criteriatype' => TYPE_STR,
		'aa_criteria' => TYPE_INT,
		'aa_reason' => TYPE_STR,
		'aa_active' => TYPE_INT,
	));
	
	if (empty($vbulletin->GPC['aa_awardid']))
	{
		print_stop_message('invalid_award_name_specified');
	}
	
	$db->query_write("
	INSERT INTO " . TABLE_PREFIX . "award_automation
		(auto_active, auto_name, auto_type, auto_criteria, auto_issuereason, auto_awardid)
	VALUES
		(
		'" . intval($vbulletin->GPC['aa_active']) . "',
		'" . addslashes($vbulletin->GPC['aa_name']) . "',
		'" . addslashes($vbulletin->GPC['aa_criteriatype']) . "',
		'" . intval($vbulletin->GPC['aa_criteria']) . "',
		'" . addslashes($vbulletin->GPC['aa_reason']) . "',
		'" . intval($vbulletin->GPC['aa_awardid']) . "'
		)");
		
	define('CP_REDIRECT', 'award_automation.php?do=manage');
	print_stop_message('saved_award_automation_successfully');
}

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "award_automation WHERE (auto_awardid = '$_GET[taskid]')");
	define('CP_REDIRECT', 'award_automation.php?do=manage');
	print_stop_message('award_automation_task_deleted');
}

// #############################################################################

print_cp_footer();

?>