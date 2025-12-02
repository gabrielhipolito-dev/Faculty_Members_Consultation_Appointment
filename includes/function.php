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
	$default = '../uploads/profile_pics/default.png';
	if (!$user) {
		return $default;
	}

	$storedPath = null;
	if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
		$storedPath = $user['profile_picture'];
	}

	if (empty($storedPath)) {
		$role = $user['role'] ?? '';
		if (strtolower($role) === 'student') {
			$s = $conn->prepare('SELECT profile_picture FROM Student WHERE user_id = ? LIMIT 1');
			$s->bind_param('i', $user['user_id']);
			$s->execute();
			$sres = $s->get_result();
			if ($sres && $sres->num_rows === 1) {
				$srow = $sres->fetch_assoc();
				$storedPath = $srow['profile_picture'] ?? null;
			}
			$s->close();
		} elseif (strtolower($role) === 'faculty') {
			$f = $conn->prepare('SELECT profile_picture FROM Faculty WHERE user_id = ? LIMIT 1');
			$f->bind_param('i', $user['user_id']);
			$f->execute();
			$fres = $f->get_result();
			if ($fres && $fres->num_rows === 1) {
				$frow = $fres->fetch_assoc();
				$storedPath = $frow['profile_picture'] ?? null;
			}
			$f->close();
		}
	}

	if (!empty($storedPath)) {
		$candidate = __DIR__ . '/../' . ltrim($storedPath, '/');
		if (file_exists($candidate)) {
			return '../' . ltrim($storedPath, '/');
		} else {
			$candidate2 = __DIR__ . '/../uploads/profile_pics/' . basename($storedPath);
			if (file_exists($candidate2)) {
				return '../uploads/profile_pics/' . basename($storedPath);
			}
		}
	}

	return $default;
}

