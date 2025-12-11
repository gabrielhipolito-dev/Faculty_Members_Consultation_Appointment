<?php
// Helper functions for the app

/**
 * Return a web-accessible avatar path for a user.
 * @param mysqli $conn
 * @param array|null $user  Row from Users table (may be null)
 * @return string Relative URL to avatar
 */
function get_avatar_path($conn, $user)
{
	$default = '../uploads/profile_pics/default_image.png';
	if (!$user) {
		return $default;
	}
	// Prefer the `profile_picture` field from Users table. Other tables no longer store pictures.
	if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
		$storedPath = $user['profile_picture'];
		$candidate = __DIR__ . '/../' . ltrim($storedPath, '/');
		if (file_exists($candidate)) {
			return '../' . ltrim($storedPath, '/');
		}
		$candidate2 = __DIR__ . '/../uploads/profile_pics/' . basename($storedPath);
		if (file_exists($candidate2)) {
			return '../uploads/profile_pics/' . basename($storedPath);
		}
	}

	return $default;
}

