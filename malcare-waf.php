<?php
// Please validate auto_prepend_file setting before removing this file

if (file_exists('/sites/stgclientportal.valetdev.io/files/wp-content/plugins/blogvault-real-time-backup/protect/prepend/ignitor.php') && !defined("MCDATAPATH")) {
	define("MCDATAPATH", '/sites/stgclientportal.valetdev.io/files/wp-content/mc_data/');
	define("MCCONFKEY", 'a5cc045877f6d51dc47d01bb4da184ff');
	include_once('/sites/stgclientportal.valetdev.io/files/wp-content/plugins/blogvault-real-time-backup/protect/prepend/ignitor.php');
}
