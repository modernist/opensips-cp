<?
/*
* Copyright (C) 2016 OpenSIPS Project
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
?>

<div id="dialog" class="dialog" style="display:none"></div>
<div onclick="closeDialog();" id="overlay" style="display:none"></div>
<div id="content" style="display:none"></div>

<?php
require("lib/".$page_id.".main.js");
?>


<form action="<?=$page_name?>?action=do_add" method="post">
	<table width="400" cellspacing="2" cellpadding="2" border="0">
	
	<tr align="center">
		<td colspan="2" class="searchTitle">
			Add New Cluster Node
		</td>
	 </tr>

	<?php
	# populate the initial values for the form
	$cl_form['cluster_id'] = null;
	$cl_form['node_id'] = null;
	$cl_form['url'] = null;
	$cl_form['no_ping_retries'] = 3;
	$cl_form['description'] = null;

	require("clusterer.form.php");
	?>

	<tr>
		<td colspan="2" class="dataRecord" align="center">
			<input type="submit" name="add" disabled=true value="Add" class="formButton">
		</td>
	</tr>

	<tr height="10">
		<td colspan="2" class="dataTitle">
			<img src="../../../images/share/spacer.gif" width="5" height="5">
		</td>
	</tr>

	</table>
</form>
<?=$back_link?>

