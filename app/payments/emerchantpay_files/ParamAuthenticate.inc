<?php
function ParamAuthenticate ($secret,$paramArray)
{
	$sentsignature=$paramArray['PS_SIGNATURE'];
	unset($paramArray['PS_SIGNATURE']);
	$string='';
	ksort($paramArray,SORT_STRING);
	foreach ($paramArray as $key=>$value)
	{
		$string.="&".$key.'='.$value;
	}	
	
	switch ($paramArray['PS_SIGTYPE'])
	{
		case 'md5':
		case 'PSMD5':
			$signature=md5($secret.$string);
			break;
		case 'sha1':
		case 'PSSHA1':
			$signature=sha1($secret.$string);
			break;
		default:
			user_error('Unknown key mode');
			return false;
	}

	if ($sentsignature!=$signature)
	{
		return false;
	}

	if (time()>$paramArray['PS_EXPIRETIME'])
	{
		return false;
	}
	unset($paramArray['PS_SIGTYPE']);
	unset($paramArray['PS_EXPIRETIME']);
	return $paramArray;
}
?>
