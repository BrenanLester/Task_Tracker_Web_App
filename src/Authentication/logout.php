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
						header('Location: Dashboard/dashboard.php');
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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		html, body { height: 100%; width: 100%; }
		body { font-family: 'Poppins', sans-serif; overflow: hidden; }
		.backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; filter: blur(8px); z-index: 1; }
		.modal-wrapper { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; }
		.confirm-box { background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); max-width: 420px; width: 100%; }
		.confirm-box h5 { color: #1a202c; margin-bottom: 12px; }
		.confirm-box p { color: #718096; font-size: 14px; margin-bottom: 24px; }
	</style>
</head>
<body>
	<iframe class="backdrop" id="bgFrame" style="border:none; pointer-events:none;" src="./Dashboard/dashboard.php"></iframe>
	
	<div class="modal-wrapper">
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
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>