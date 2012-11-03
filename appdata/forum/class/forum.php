<?php

/**
 * A class used to store data on this forum as a whole.
 */
class Forum
{

	public function addCategory($category)
	{
		
	}

	public function getCategory($id)
	{
		
	}

	public function getCategories()
	{
		
	}

	public function getBoard($id)
	{
		
	}

	public function getName()
	{
		global $forum_name;

		return $forum_name;
	}

	protected function getFileName()
	{
		return preg_replace('/\s+/ ', "_", trim($this->getName() . ".frm"));
	}

	static public function load($fileName)
	{
		return parent::load($fileName . "/" . $fileName . ".frm");
	}

	function backupForum()
	{
		global $directory;

		//Delete old backups
		while (count($this->getBackups()) >= $this->backups)
		{
			$oldestBackup = "";
			$oldestBackupNumber = 0;

			foreach ($this->getBackups() as $value)
			{
				$data = explode("_", $value);

				if (intval($data[2]) < $oldestBackupNumber || $oldestBackupNumber == 0)
				{
					$oldestBackup = $value;
					$oldestBackupNumber = intval($data[2]);
				}
			}

			$this->deleteBackup($oldestBackup);
		}

		$backupName = $this->name . "_" . date('Y-m-d') . "_" . time();

		$filelocation = $directory . "backup/" . $backupName;

		if (!file_exists($filelocation))
		{
			$newfile = fopen($filelocation, "w+");
			fwrite($newfile, serialize($this));
			fclose($newfile);
			return $backupName;
		}

		return false;
	}

	function getBackups()
	{
		global $directory;

		$returnArray = array();

		if ($handle = opendir($directory . "backup/"))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != "..")
				{
					if (strstr($entry, $this->name))
					{
						$returnArray[] = $entry;
					}
				}
			}

			closedir($handle);
		}

		sort($returnArray);

		return $returnArray;
	}

	function deleteBackup($backupFile)
	{
		global $directory;

		$filelocation = $directory . "backup/" . stripslashes(trim(trim($backupFile, "/"), "."));

		if (file_exists($filelocation))
		{
			unlink($filelocation);
			return true;
		}

		return false;
	}

}

?>