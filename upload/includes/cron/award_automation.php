<?php
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}




// POST COUNT AWARDS

// Get Maximum Award for UserIDs
$userMaxAwardCriteria = $vbulletin->db->query_read("
	SELECT vbuser.userid AS UserID, MAX(award.auto_criteria) AS MaxAward
	FROM " . TABLE_PREFIX . "user vbuser, " . TABLE_PREFIX . "award_automation award
	WHERE (award.auto_active=1) AND (vbuser.posts >= award.auto_criteria) AND (award.auto_type = 'postcount')	
	GROUP BY UserID
");

while ($array1 = $vbulletin->db->fetch_array($userMaxAwardCriteria))
{
	$getAwardInformation = $vbulletin->db->query_read("
		SELECT auto_active, auto_name, auto_type, auto_criteria, auto_issuereason, auto_awardid
		FROM " . TABLE_PREFIX . "award_automation
		WHERE (auto_active=1) AND (auto_criteria=$array1[MaxAward]) AND (auto_type='postcount')
		");
		
	while ($array2 = $vbulletin->db->fetch_array($getAwardInformation))
	{
		// Check to see if User already has award
		$checkAward = $vbulletin->db->query_read("
			SELECT userid, award_id, award_cgroup
			FROM " . TABLE_PREFIX . "award_user
			WHERE (userid=$array1[UserID]) AND (award_id=$array2[auto_awardid]) AND (award_cgroup='postcount')
			");
			
		$alreadyissued = $vbulletin->db->num_rows($checkAward);
		
		if (empty($alreadyissued)) {
			// Remove Previous Postcount Awards
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "award_user WHERE award_cgroup = 'postcount' AND userid=$array1[UserID]");
			// Issue New Postcount Award
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "award_user (award_id, userid, issue_reason, issue_time, award_cgroup) VALUES ('$array2[auto_awardid]', '$array1[UserID]', '" . addslashes($array2['auto_issuereason']) . "', " . time() . ", 'postcount')");
   		}
	}
}

// USERGROUP AWARDS
$usergroupAwards = $vbulletin->db->query_read("
	SELECT *
	FROM " . TABLE_PREFIX . "award_automation
	WHERE (auto_active=1) AND (auto_type='usergroup')
	");
	
while ($usergroupAwardsArray = $vbulletin->db->fetch_array($usergroupAwards))
{
	$getUsersWithGroup = $vbulletin->db->query_read("
		SELECT userid
		FROM " . TABLE_PREFIX . "user
		WHERE (usergroupid=$usergroupAwardsArray[auto_criteria])
		OR " . $usergroupAwardsArray[auto_criteria] . " IN (membergroupids)
		");
		
	while ($array3 = $vbulletin->db->fetch_array($getUsersWithGroup))
	{
		$checkUsergroupAward = $vbulletin->db->query_read("
			SELECT userid, award_id
			FROM " . TABLE_PREFIX . "award_user
			WHERE (userid=$array3[userid]) AND (award_id=$usergroupAwardsArray[auto_awardid])
			");
		
		$alreadyissued = $vbulletin->db->num_rows($checkUsergroupAward);		
		
		if (empty($alreadyissued)) {
			// Issue New Usergroup Award
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "award_user (award_id, userid, issue_reason, issue_time, award_cgroup) VALUES ('$usergroupAwardsArray[auto_awardid]', '$array3[userid]', '" . addslashes($usergroupAwardsArray['auto_issuereason']) . "', " . time() . ", 'usergroup')");
		}
	}
}

// Membership Length Awards
$userMaxAwardCriteria = $vbulletin->db->query_read("
	SELECT vbuser.userid AS UserID, MAX(award.auto_criteria) AS MaxAward
	FROM " . TABLE_PREFIX . "user vbuser, " . TABLE_PREFIX . "award_automation award
	WHERE (award.auto_active=1) AND (DATEDIFF( NOW(), FROM_UNIXTIME( vbuser.joindate ) ) >= award.auto_criteria) AND (award.auto_type = 'daysasmember')	
	GROUP BY UserID
	
");

while ($array1 = $vbulletin->db->fetch_array($userMaxAwardCriteria))
{
	$getAwardInformation = $vbulletin->db->query_read("
		SELECT auto_active, auto_name, auto_type, auto_criteria, auto_issuereason, auto_awardid
		FROM " . TABLE_PREFIX . "award_automation
		WHERE (auto_active=1) AND (auto_criteria=$array1[MaxAward]) AND (auto_type='daysasmember')
		");
		
	while ($array2 = $vbulletin->db->fetch_array($getAwardInformation))
	{
		// Check to see if User already has award
		$checkAward = $vbulletin->db->query_read("
			SELECT userid, award_id, award_cgroup
			FROM " . TABLE_PREFIX . "award_user
			WHERE (userid=$array1[UserID]) AND (award_id=$array2[auto_awardid]) AND (award_cgroup='daysasmember')
			");
			
		$alreadyissued = $vbulletin->db->num_rows($checkAward);
		
		if (empty($alreadyissued)) {
			// Remove Previous Postcount Awards
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "award_user WHERE award_cgroup = 'daysasmember' AND userid=$array1[UserID]");
			// Issue New Postcount Award
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "award_user (award_id, userid, issue_reason, issue_time, award_cgroup) VALUES ('$array2[auto_awardid]', '$array1[UserID]', '" . addslashes($array2['auto_issuereason']) . "', " . time() . ", 'daysasmember')");
		}
	}
}


?>