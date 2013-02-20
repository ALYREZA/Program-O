<?php
/***************************************
* http://www.program-o.com
* PROGRAM O 
* Version: 2.1.1
* FILE: chatbot/core/aiml/buildingphp_code_functions.php
* AUTHOR: ELIZABETH PERREAU
* DATE: MAY 4TH 2011
* DETAILS: this file contains the functions to turn the generated php code to code that can evaluate
***************************************/

/**
 * function clean_for_eval()
 * This function cleans the generated code so that it can be safely evaluated as PHP
 * @param  string $parsed_template - this is the code which was generated by interpreting the aiml
 * @param  int $count -  this function is called twice (once by itself) this value is the number of times it has run
 * @return string $parsed_template (after the clean)
**/
function clean_for_eval($parsed_template,$count=0)
{
	runDebug( __FILE__, __FUNCTION__, __LINE__, "Cleaning the generated code for evaluating (running ".($count+1)." time)",4);	
	$new_parsed_template = "";
	$end = "\r\n\r\n\$botsay = \$tmp_botsay;\r\n";
	
	if($count==0){
		$parsed_template = foreignchar_replace('encode',$parsed_template);
		$parsed_template = entity_replace("encode",$parsed_template);
	}
	
	$parsed_template = $parsed_template ."';\r\n";
	$parsed_template = "\r\n\$tmp_botsay = \"\";\r\n".$parsed_template ."';\r\n";
		
	$parsed_template = preg_replace("/\r\n\'.call_user_func/", '$tmp_botsay .= call_user_func', $parsed_template);
	$parsed_template = preg_replace("/\r\n(\s|\s+)?\'.call_user_func/", '$tmp_botsay .= call_user_func', $parsed_template);
	$parsed_template = preg_replace('/\s\s+/', ' ', $parsed_template);
	
	$parsed_template= str_replace("';';","';",$parsed_template);
	$parsed_template= str_replace(";';","';",$parsed_template);
	$parsed_template= str_replace(";",";\r\n",$parsed_template);
	$parsed_template= str_replace("]';","];",$parsed_template);
	$parsed_template= str_replace("close_bracket. '.",'close_bracket.',$parsed_template);
	$parsed_template= str_replace("','  '.","',",$parsed_template);
	$parsed_template= str_replace("',' '.","',",$parsed_template);
	$parsed_template= str_replace(".''close_bracket","close_bracket",$parsed_template);
	$parsed_template= str_replace("close_bracket.''","close_bracket",$parsed_template);
	$parsed_template= str_replace("close_bracket. 'close_bracket","close_bracketclose_bracket",$parsed_template);
	$parsed_template= str_replace(".' 'close_bracket","close_bracket",$parsed_template);
	$parsed_template= str_replace(".' .",".",$parsed_template);
	$parsed_template= str_replace("\r\n.","\$tmp_botsay .= ",$parsed_template);
	$parsed_template= str_replace("\r\n';","\r\n",$parsed_template);
	$parsed_template= str_replace("\r\n ';","\r\n",$parsed_template);
	$parsed_template= str_replace(">. call", ">'. call", $parsed_template);
	$parsed_template= str_replace("> . call", ">' . call", $parsed_template);
	$parsed_template= str_replace("close_bracket.';", "close_bracket;", $parsed_template);
	$parsed_template= str_replace("''.", "", $parsed_template);
	$parsed_template= str_replace("\r\n'.call","\$tmp_botsay .= call",$parsed_template);	
	$parsed_template= str_replace("\r\n  '.call","\$tmp_botsay .= call",$parsed_template);
	$parsed_template= str_replace("\r\n '.call","\$tmp_botsay .= call",$parsed_template);
	$parsed_template= str_replace("\$condition = \"\"","\r\n\$condition=\"\"",$parsed_template);
	$parsed_template= str_replace("\"\";\$tmp_botsay","\"\";\r\n\$tmp_botsay",$parsed_template);
	$parsed_template= str_replace("\$tmp_botsay","\r\n\$tmp_botsay",$parsed_template);
	$parsed_template= str_replace("close_bracket.' ;","close_bracket;",$parsed_template);
	$parsed_template= str_replace('$tmp_botsay .= \' if','if',$parsed_template);
	$parsed_template= str_replace(".'. \r\n",".'. ';\r\n",$parsed_template);	
	$parsed_template= str_replace("close_bracket.' \r\n", "close_bracket; ", $parsed_template);
	$parsed_template= str_replace("close_bracket';", "close_bracket;", $parsed_template);
	$parsed_template= str_replace("close_bracket.\\\"';","close_bracket.'\\\"';", $parsed_template);
	
	//TODO BE CAREFULL this might break things ... needed for learn.aiml
	$parsed_template= str_replace("close_bracket.'close_bracket;","close_bracket close_bracket;", $parsed_template);

	
	
	$linebyline = explode("\r\n",$parsed_template);
	
	foreach($linebyline as $index => $value)
	{
		if((trim($value)=="\$tmp_botsay .= ' ;")||(trim($value)=="\$botsay =")||(trim($value)=="\$tmp_botsay;")||(trim($value)=="\$tmp_botsay .= '")||(trim($value)=="';")||(trim($value)==";")||(trim($value)=='$tmp_botsay=\' ;')||(trim($value)=='$tmp_botsay=\'')){
		}
		else{
			runDebug( __FILE__, __FUNCTION__, __LINE__, "Balancing open/close brackets",4);	
			$bracketed_value = best_guess_bracket_clean($value);
			$new_parsed_template .= $bracketed_value."\r\n";
		}
	}
	$parsed_template= str_replace('~apos~',"\'",$new_parsed_template);
	$parsed_template= str_replace("tmp_botsay=' \$tmp_botsay .= call","tmp_botsay .= call",$parsed_template);
	$parsed_template= str_replace("tmp_botsay=' \$tmp_botsay .= ","tmp_botsay .= '",$parsed_template);
	$parsed_template= str_replace("\r\n').' '.call","\r\n\$tmp_botsay .= call",$parsed_template);

	$parsed_template .= $end;
	
	if($count==0){
		$count++;
		runDebug( __FILE__, __FUNCTION__, __LINE__, "Re-Clean just in case",4);	
		$parsed_template = clean_for_eval($parsed_template,$count);
	}
	else {
		$parsed_template = replace_conflicting_htmltags($parsed_template);
		$parsed_template = entity_replace("decode",$parsed_template);
		$parsed_template = foreignchar_replace('decode',$parsed_template);	
	}
	
	$parsed_template = clean_smilies($parsed_template);
	$parsed_template = preg_replace('/\s\s+/', ' ', $parsed_template);
	return $parsed_template;
}

/**
 * function best_guess_bracket_clean()
 * This function performs a best guess on the opening and closing brackets on each line of code then tries to balance them out
 * @param  string $value - this is the line of code
 * @return string $value
**/	
function best_guess_bracket_clean($value)
{
	runDebug( __FILE__, __FUNCTION__, __LINE__, "Trying a best guess to balance the open/closing brackets",4);	
	
	$open_bracket_count = substr_count($value, '(');
	$close_bracket_count = substr_count($value, ')');	
	$bracket_difference = ($open_bracket_count - $close_bracket_count);
			
	if($bracket_difference<0){
		$value = trim($value);
		$value = trim($value,";");
				
		for($i=0;$i>$bracket_difference;$i--){
			$value = trim($value,")");
		}
			
		$value .= ";";
	}
	elseif($bracket_difference>0){
		$value = trim($value);
		$value = trim($value,";");
				
		for($i=$bracket_difference;$i>0;$i--){
			$value .= ")";
		}		

		$value .= ";";
	}
	return $value;
}

/**
 * function clean_smilies()
 * This function performs extra cleaning duties on smilies within the generated code
 * @param  string $parsed_template - this is the code which was generated by interpreting the aiml
 * @return string $parsed_template (after the replacements)
**/	
function clean_smilies($parsed_template)
{
	runDebug( __FILE__, __FUNCTION__, __LINE__, "Extra smilie cleaning",4);
	
	$parsed_template= str_replace("':\(')", "':\('",$parsed_template);
	$parsed_template= str_replace("';\(')", "';\('",$parsed_template);
	$parsed_template= str_replace("':-\(')", "':-\('",$parsed_template);
	$parsed_template= str_replace("';-\(')", "';-\('",$parsed_template);
	return $parsed_template;
}

/**
 * function replace_conflicting_htmltags()
 * This function replaces back into html tags which may conflict with aiml tags
 * e.g. <li> 
 * @param  string $parsed_template - this is the code which was generated by interpreting the aiml
 * @return string $parsed_template (after the replacements)
**/	
function replace_conflicting_htmltags($parsed_template)
{
	runDebug( __FILE__, __FUNCTION__, __LINE__, "replacing conflicting html tags",4);
	
	$parsed_template = str_replace("<bulletpoint>","<li>",$parsed_template);
	$parsed_template = str_replace("</bulletpoint>","</li>",$parsed_template);
	return $parsed_template;
}
	
/**
 * function get_random_str()
 * This function generates a random string to be used in the generated aiml to php code in items like array names
 * @return string $string
**/	
function get_random_str()
{
	
    $length = 10;
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $string ='';    
    for ($p = 0; $p < $length-1; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }
    runDebug( __FILE__, __FUNCTION__, __LINE__, "making a random string e.g.: $string",4);
    return $string;
}

/**
 * function tidy_function_calls()
 * This function cleans the generated code spefically around the function calls
 * @param  string $parsed_template - this is the code which was generated by interpreting the aiml
 * @return string $parsed_template (after the clean)
**/
function tidy_function_calls($parsed_template)
{
	runDebug( __FILE__, __FUNCTION__, __LINE__, "Tiding up the function calls",4);
	
	$parsed_template= str_replace('."\'','',$parsed_template);
	$parsed_template= str_replace('\'".','',$parsed_template);
	
	$parsed_template= str_replace(".\\\"'","",$parsed_template);
	$parsed_template= str_replace("'\\\".","",$parsed_template);
	
	return "call_user_func(".$parsed_template.")";
}

/**
 * function clean_condition()
 * This function cleans an item to be used in conditional checks
 * @param  string $condition - item to be cleaned
 * @return string $condition - item after clean
**/
function clean_condition($condition)
{
	runDebug( __FILE__, __FUNCTION__, __LINE__, "Cleaning condition - $condition ",4);
	return trim($condition);
}
?>