<?php
session_start();
unset($_SESSION['reset']);
?>
<html>
	<body>
		<script language="javascript">
			setTimeout('window.location="/admin"',2000)
		</script>
		Logging Out... Please Wait
	</body>
</html>
