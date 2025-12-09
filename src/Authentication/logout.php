<?php
session_start();

// If the user submitted the confirmation form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$confirm = $_POST['confirm'] ?? '';

		if ($confirm === 'yes') {
				// Clear all session data and redirect to landing page
				session_unset();
				session_destroy();
				header("Location: ../../index.html");
				exit;
		} else {
				// User chose No — redirect back to referrer if safe, otherwise to dashboard
				$referer = $_SERVER['HTTP_REFERER'] ?? '';
				if ($referer && stripos($referer, 'logout.php') === false) {
						header('Location: ' . $referer);
				} else {
						header('Location: ./Dashboard/dashboard.php');
				}
				exit;
		}
}

// Display confirmation page (GET request)
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Confirm Logout</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f7fafc; }
		.backdrop { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter:blur(5px); z-index:999; }
		.confirm-box { background: #fff; padding: 24px; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); max-width:420px; width:100%; position:relative; z-index:1000; }
	</style>
</head>
<body>
	<div class="backdrop"></div>
	<div class="confirm-box">
		<h5 class="mb-3">Are you sure you want to logout?</h5>
		<p class="text-muted">You will be returned to the landing page if you confirm.</p>

		<form method="post">
			<div class="d-flex gap-2">
				<button type="submit" name="confirm" value="yes" class="btn btn-danger">Yes</button>
				<button type="submit" name="confirm" value="no" class="btn btn-secondary">No</button>
			</div>
		</form>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>