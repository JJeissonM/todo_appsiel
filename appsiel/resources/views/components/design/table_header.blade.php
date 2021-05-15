<style>
	th{
		text-transform: uppercase;
	}
</style>
<thead>
	<tr style=" vertical-align: middle !important;">
		<?php
			for($li=0;$li<count($headers);$li++)
			{
				echo '<th style="text-align: center; vertical-align: middle !important; font-weight: bolder; color: #000; ">'.$headers[$li].'</th>';
			}
		?>
	</tr>
</thead>