<thead>
	<tr style="background-color: #50B794; vertical-align: middle !important;">
		<?php
			for($li=0;$li<count($headers);$li++){
				echo '<th  style="text-align: center; vertical-align: middle !important; font-weight: bolder; color: #000; text-transform: uppercase;" title="' . $headers[$li] . '">'.$headers[$li].'</th>';
			}
		?>
	</tr>
</thead>