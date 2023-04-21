<?php
class DbSettings {
	public static function get()
	{
		$conn = sqlnew();
		$query = "SELECT * FROM " . get_config("mysql::table_prefix") . "auth_settings";
		$result = $conn->query($query);
		$list = [];
		while ($row = $result->fetch())
		{
			$list[$row['setting_key']] = unserialize($row['setting_value']);
		}
		return $list;
	}
	public static function set($key, $val) : int
	{
		$conn = sqlnew();
		$stmt = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "auth_settings WHERE setting_key = :name LIMIT 1");
		$stmt->execute(["name" => $key]);
		if ($stmt->rowCount()) // if it already exists update it
			$stmt = $conn->prepare("UPDATE " . get_config("mysql::table_prefix") . "auth_settings SET setting_value = :value WHERE setting_key = :name");
			
		else // otherwise create it
			$stmt = $conn->prepare("INSERT INTO " . get_config("mysql::table_prefix") . "auth_settings (setting_key, setting_value) VALUES (:name, :value)");

		// make sure it's there/correct
		$stmt->execute(["name" => $key, "value" => serialize($val)]);
		$stmt = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "auth_settings WHERE setting_key = :name LIMIT 1");
		$stmt->execute(["name" => $key]);
		return $stmt->rowCount(); // return 1 or 0 bool-like int
	}
}
