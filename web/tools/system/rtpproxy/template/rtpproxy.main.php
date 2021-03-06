<!--
 /*
 * $Id$
 * Copyright (C) 2011 OpenSIPS Project
 *
 * This file is part of opensips-cp, a free Web Control Panel Application for 
 * OpenSIPS SIP server.
 *
 * opensips-cp is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * opensips-cp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
-->
<div id="dialog" class="dialog" style="display:none"></div>
<div onclick="closeDialog();" id="overlay" style="display:none"></div>
<div id="content" style="display:none"></div>
<form action="<?=$page_name?>?action=dp_act" method="post">
<?php
//fetch cache data

$mi_connectors=get_proxys_by_assoc_id($talk_to_this_assoc_id);

// fetch data from the first box only
$message = mi_command('rtpproxy_show',$mi_connectors[0], $mi_type, $errors,$status);

if ($mi_type != "json"){
	$sets = array();
	$pattern = '/node\:\:\s+(?P<sock>[a-zA-Z0-9.:=]+)\s+index=(?P<index>\d+)\s+disabled=(?P<status>\d+)\s+weight=(?P<weight>\d+)\s+recheck_ticks=(?P<ticks>\d+)(\s|\n)*/';
	$message = explode("Set:: ",trim($message));
	for ($j=0; $j<count($message); $j++){
		$setid = substr($message[$j],0,strpos($message[$j],"\n"));
		if ($setid != "" && $setid != null) {
			preg_match_all($pattern,substr($message[$j],strpos($message[$j],"\n")+1),$matches);
			if (count($matches[0]) > 0){
				$sets[$setid] = $matches;
			}
		}
		$data_no += count($matches[0]);
	}
} else {
	$message = json_decode($message,true);
	$message = $message['Set'];
	$data_no = count($message);
}

$rtpproxies_cache = array();

if ($mi_type != "json"){
	foreach ($sets as $setid => $matches) {
		for ($i=0; $i<count($matches[0]);$i++) {
			$rtpproxies_cache[$setid][$matches['sock'][$i]]['status'] 	= $matches['status'][$i]; 
			$rtpproxies_cache[$setid][$matches['sock'][$i]]['weight'] 	= $matches['weight'][$i]; 
			$rtpproxies_cache[$setid][$matches['sock'][$i]]['ticks'] 	= $matches['ticks'][$i];

			if ($matches['status'][$i] == 1){
				$rtpproxies_cache[$setid][$matches['sock'][$i]]['state_link'] 	= '<a href="'.$page_name.'?action=change_state&state='.$matches['status'][$i].'&sock='.$matches['sock'][$i].'"><img align="center" name="status'.$i.'" src="../../../images/share/inactive.png" alt="'.$state[$i].'" onclick="return confirmStateChange(\''.$matches['status'][$i].'\')" border="0"></a>';
			} else if ($matches['status'][$i] == 0){
				$rtpproxies_cache[$setid][$matches['sock'][$i]]['state_link'] 	= '<a href="'.$page_name.'?action=change_state&state='.$matches['status'][$i].'&sock='.$matches['sock'][$i].'"><img align="center" name="status'.$i.'" src="../../../images/share/active.png" alt="'.$state[$i].'" onclick="return confirmStateChange(\''.$matches['status'][$i].'\')" border="0"></a>';
			}
		}
	}
}
else {
	// $message is an array of sets right now
	for ($i=0; $i<count($message);$i++) {
		// get each node from the SET
		for ($j=0; $j<count($message[$i]['children']['node']); $j++){
			$node = $message[$i]['children']['node'][$j];
			$rtpproxies_cache[ $i ][ $node['value'] ]['status'] = $node['attributes']['disabled'];
			$rtpproxies_cache[ $i ][ $node['value'] ]['weight'] = $node['attributes']['weight'];
			$rtpproxies_cache[ $i ][ $node['value'] ]['ticks']  = $node['attributes']['recheck_ticks'];
		
			if ($node['attributes']['disabled'] == 1){
				$rtpproxies_cache[ $i ][ $node['value'] ]['state_link'] 	= '<a href="'.$page_name.'?action=change_state&state='.$node['attributes']['disabled'].'&sock='.$node['value'].'"><img align="center" name="status'.$i.'" src="../../../images/share/inactive.png" alt="'.$node['attributes']['disabled'].'" onclick="return confirmStateChange(\''.$node['attributes']['disabled'].'\')" border="0"></a>';
			} else if ($node['attributes']['disabled'] == 0){
				$rtpproxies_cache[ $i ][ $node['value'] ]['state_link'] 	= '<a href="'.$page_name.'?action=change_state&state='.$node['attributes']['disabled'].'&sock='.$node['value'].'"><img align="center" name="status'.$i.'" src="../../../images/share/active.png" alt="'.$node['attributes']['disabled'].'" onclick="return confirmStateChange(\''.$node['attributes']['disabled'].'\')" border="0"></a>';
			}
		}
	} 	
}
$sql_search="";
$search_setid=$_SESSION['rtpproxy_setid'];
$search_sock=$_SESSION['rtpproxy_sock'];
if($search_setid!="") { 
	$sql_search.="and set_id=".$search_setid;
}
if ( $search_sock!="" ) {
	$sql_search.=" and rtpproxy_sock like '%".$search_sock."%'";
} else {
	$sql_search.=" and rtpproxy_sock like '%'";		
}


require("lib/".$page_id.".main.js");

if(!$_SESSION['read_only']){
	$colspan = 8;
}else{
	$colspan = 5;
}
  ?>
<table width="50%" cellspacing="2" cellpadding="2" border="0">
 <tr align="center">
  <td colspan="2" height="10" class="rtpproxyTitle"></td>
 </tr>
  <tr>
  <td class="searchRecord">RTPproxy Sock</td>
  <td class="searchRecord" width="200"><input type="text" name="rtpproxy_sock" 
  value="<?=$search_sock?>" class="searchInput"></td>
 </tr>
  <tr>
  <td class="searchRecord">Setid</td>
  <td class="searchRecord" width="200"><input type="text" name="rtpproxy_setid" 
  value="<?=$search_setid?>" maxlength="16" class="searchInput"></td>
 </tr>
  <tr height="10">
  <td colspan="2" class="searchRecord" align="center">
  <input type="submit" name="search" value="Search" class="searchButton">&nbsp;&nbsp;&nbsp;
  <input type="submit" name="show_all" value="Show All" class="searchButton"></td>
 </tr>

 <tr height="10">
  <td colspan="2" class="rtpproxyTitle"><img src="../../../images/share/spacer.gif" width="5" height="5"></td>
 </tr>

</table>
</form>

<form action="<?=$page_name?>?action=add&clone=0" method="post">
 <?php if (!$_SESSION['read_only']) echo('<input type="submit" name="add_new" value="Add New" class="formButton">') ?>
</form>

<table class="ttable" width="95%" cellspacing="2" cellpadding="2" border="0">
 <tr align="center">
  <th class="rtpproxyTitle">ID</th>
  <th class="rtpproxyTitle">RTPproxy Sock</th>
  <th class="rtpproxyTitle">Setid</th>
  <th class="rtpproxyTitle">Weight</th>
  <th class="rtpproxyTitle">Ticks</th>
  <?
  if(!$_SESSION['read_only']){
  	echo('<th class="rtpproxyTitle">Memory State</th>');
  	echo('<th class="rtpproxyTitle">Edit</th>'); 
	echo ('<th class="rtpproxyTitle">Delete</th>');
  }
  ?>
 </tr>
<?php
if ($sql_search=="") $sql_command="select * from ".$table." where(1=1) order by id asc";
else $sql_command="select * from ".$table." where (1=1) ".$sql_search." order by id asc";
$result = $link->queryAll($sql_command);
if(PEAR::isError($result)) {
         die('Failed to issue query, error message : ' . $result->getMessage());
}

$data_no=count($result);
if ($data_no==0) echo('<tr><td colspan="'.$colspan.'" class="rowEven" align="center"><br>'.$no_result.'<br><br></td></tr>');
else
{
$res_no=$config->results_per_page;
$page=$_SESSION[$current_page];
$page_no=ceil($data_no/$res_no);
if ($page>$page_no) {
$page=$page_no;
$_SESSION[$current_page]=$page;
}
$start_limit=($page-1)*$res_no;
//$sql_command.=" limit ".$start_limit.", ".$res_no;
if ($start_limit==0) $sql_command.=" limit ".$res_no;
else $sql_command.=" limit ". $res_no . " OFFSET " . $start_limit;
$result = $link->queryAll($sql_command);
if(PEAR::isError($result)) {
	  die('Failed to issue query, error message : ' . $resultset->getMessage());
}
require("lib/".$page_id.".main.js");
$index_row=0;
for ($i=0;count($result)>$i;$i++)
{
$index_row++;
if ($index_row%2==1) $row_style="rowOdd";
else $row_style="rowEven";

if(!$_SESSION['read_only']){

	$edit_link = '<a href="'.$page_name.'?action=edit&clone=0&id='.$result[$i]['id'].'"><img src="../../../images/share/edit.gif" border="0"></a>';
	$delete_link='<a href="'.$page_name.'?action=delete&clone=0&id='.$result[$i]['id'].'"onclick="return confirmDelete()"><img src="../../../images/share/trash.gif" border="0"></a>';
}
?>
<tr>
<td class="<?=$row_style?>">&nbsp;<?=$result[$i]['id']?></td>
<td class="<?=$row_style?>">&nbsp;<?=$result[$i]['rtpproxy_sock']?></td>
<td class="<?=$row_style?>">&nbsp;<?=$result[$i]['set_id']?></td>
<td class="<?=$row_style?>">&nbsp;<?=isset($rtpproxies_cache[$result[$i]['set_id']][$result[$i]['rtpproxy_sock']]['weight'])?$rtpproxies_cache[$result[$i]['set_id']][$result[$i]['rtpproxy_sock']]['weight']:"n/a"?></td>
<td class="<?=$row_style?>">&nbsp;<?=isset($rtpproxies_cache[$result[$i]['set_id']][$result[$i]['rtpproxy_sock']]['ticks'])?$rtpproxies_cache[$result[$i]['set_id']][$result[$i]['rtpproxy_sock']]['ticks']:"n/a"?></td>
<? 
if(!$_SESSION['read_only']){
?>
<td class="<?=$row_style?>" align="center"><?=isset($rtpproxies_cache[$result[$i]['set_id']][$result[$i]['rtpproxy_sock']]['state_link'])?$rtpproxies_cache[$result[$i]['set_id']][$result[$i]['rtpproxy_sock']]['state_link']:"n/a"?></td>
<td class="<?=$row_style?>" align="center"><?=$edit_link?></td>
<td class="<?=$row_style?>" align="center"><?=$delete_link?></td>
<?php
}
?>  
</tr>  
<?php
}
}
?>
<tr>
<th colspan="<?=$colspan?>" class="rtpproxyTitle">
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
     <tr>
      <th align="left">
       &nbsp;Page:
       <?php
       if ($data_no==0) echo('<font class="pageActive">0</font>&nbsp;');
       else {
       	$max_pages = $config->results_page_range;
       	// start page
       	if ($page % $max_pages == 0) $start_page = $page - $max_pages + 1;
       	else $start_page = $page - ($page % $max_pages) + 1;
       	// end page
       	$end_page = $start_page + $max_pages - 1;
       	if ($end_page > $page_no) $end_page = $page_no;
       	// back block
       	if ($start_page!=1) echo('&nbsp;<a href="'.$page_name.'?page='.($start_page-$max_pages).'" class="menuItem"><b>&lt;&lt;</b></a>&nbsp;');
       	// current pages
       	for($i=$start_page;$i<=$end_page;$i++)
       	if ($i==$page) echo('<font class="pageActive">'.$i.'</font>&nbsp;');
       	else echo('<a href="'.$page_name.'?page='.$i.'" class="pageList">'.$i.'</a>&nbsp;');
       	// next block
       	if ($end_page!=$page_no) echo('&nbsp;<a href="'.$page_name.'?page='.($start_page+$max_pages).'" class="menuItem"><b>&gt;&gt;</b></a>&nbsp;');
       }
       ?>
      </th>
      <th align="right">Total Records: <?=$data_no?>&nbsp;</th>
     </tr>
    </table>
  </th>
 </tr>
</table>
<br>


