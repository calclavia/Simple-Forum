<?php

/**
 * A base class extended by various forum elements.
 * @author Calclavia
 */
abstract class ForumElement
{

	protected $id = -1;
	public $name;
	public $element_name;
	public $prefix;
	public $fields = array();

	public function getID()
	{
		return $this->id;
	}

	public function isModerator($userID)
	{
		return false;
	}

	public function save($con)
	{
		global $table_prefix;

		if ($this->id < 0)
		{
			$query = "INSERT INTO {$table_prefix}{$this->element_name} (Name";

			foreach ($this->fields as $key => $value)
			{
				$query .= ",";
				$query .= $key;
			}

			$query .= ") VALUES ('" . mysql_real_escape_string($this->name) . "'";

			foreach ($this->fields as $key => $value)
			{
				$query .= ",";

				if (is_int($value))
				{
					$query .= $value;
				} else
				{
					$query .= "'" . mysql_real_escape_string($value) . "'";
				}
			}

			$query .= ")";

			mysql_query($query) or die("Failed to create forum element: " . mysql_error() . ", Q = " . $query);
			$result = mysql_query("SHOW TABLE STATUS LIKE '{$table_prefix}{$this->element_name}'");
			$row = mysql_fetch_array($result);
			$maxRows = intval($row['Auto_increment']);
			$this->id = $maxRows - 1;
			return true;
		} else
		{
			$query = "UPDATE {$table_prefix}{$this->element_name} SET Name='" . mysql_real_escape_string($this->name) . "'";

			$i = 0;

			foreach ($this->fields as $key => $value)
			{
				$query .= ",";

				$query .= $key . "=";

				if (is_int($value))
				{
					$query .= $value;
				} else
				{
					$query .= "'" . mysql_real_escape_string($value) . "'";
				}
			}

			$query .= " WHERE ID={$this->id} LIMIT 1";

			mysql_query($query) or die("Failed to save forum element: " . mysql_error() . ", Q = " . $query);
			return true;
		}

		return false;
	}

	public function delete($con)
	{
		global $table_prefix;

		$children = $this->getChildren();

		if ($children != null && count($children) > 0)
		{
			foreach ($children as $child)
			{
				$child->delete($con);
			}
		}

		if ($this instanceof Post)
		{
			$user = getUserByID($this->fields["User"]);

			if ($user != null)
			{
				$user->unmoderate($this);
			}
		}

		mysql_query("DELETE FROM {$table_prefix}{$this->element_name} WHERE ID={$this->id} LIMIT 1");
	}

	public abstract function getChildren();
}

?>