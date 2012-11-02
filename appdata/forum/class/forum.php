<?php
/**
 * A class used to store data on this forum as a whole.
 */
class Forum extends ForumElement
{
	//How many backups will this forum keep?
	private $backups = 50;
		
	/**
	 * Creates a new form.
	 */
	function __construct($name, $moderators)
	{
		global $DATA_DIRECTORY;

		$this->name = $this->forum = $name;
		$this->moderators = $moderators;;
		
		if(is_dir($DATA_DIRECTORY.$this->getName()))
		{
			die("Forum already exists!");
		}
		
		mkdir($DATA_DIRECTORY.$this->getName());
		$this->firstSave();
	}
	
	public function addCategory($categoryName)
	{
		$category = new Category($this->getNexID(), $categoryName, $this->getName());
		$this->save();
		return $category->id;
	}
	
	public function getCategory($id)
	{
		foreach(self::getForumElements(".ctg") as $value)
		{
			if($id == $value->id)
			{
				return $value;
			}
		}
	}
	
	public function getCategories()
	{
		return self::getForumElements();
	}
	
	protected function getForumElements()
	{
		global $DATA_DIRECTORY;
		
		$extension = ".ctg";
		$returnArray = array();

		if($handle = opendir($DATA_DIRECTORY.$this->forum))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != ".." && strstr($entry, $extension))
				{
					$category = Category::load($this->getName(), str_replace($extension, "", $entry));
					
					if($category != null)
					{
						$returnArray[] = $category;
					}
				}
			}
			
			closedir($handle);
		}
		
		return array_reverse($returnArray);
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	protected function getFileName()
	{
		return preg_replace('/\s+/ ', "_", trim($this->getName().".frm"));
	}
	
	static public function load($fileName)
	{
		return parent::load($fileName."/".$fileName.".frm");
	}
	
	function backupForum()
	{
		global $directory;
		
		//Delete old backups
		while(count($this->getBackups()) >= $this->backups)
		{
			$oldestBackup = "";
			$oldestBackupNumber = 0;
			
			foreach($this->getBackups() as $value)
			{
				$data = explode("_", $value);

				if(intval($data[2]) < $oldestBackupNumber || $oldestBackupNumber == 0)
				{
					$oldestBackup = $value;
					$oldestBackupNumber = intval($data[2]);
				}
			}
			
			$this->deleteBackup($oldestBackup);
		}
		
		$backupName = $this->name."_".date('Y-m-d')."_".time();		
		
		$filelocation = $directory."backup/".$backupName;
		
		if(!file_exists($filelocation))
		{
			$newfile = fopen($filelocation,"w+");
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

		if($handle = opendir($directory."backup/"))
		{
			while(false !== ($entry = readdir($handle)))
			{
				if($entry != "." && $entry != "..")
				{
					if(strstr($entry, $this->name))
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
		
		$filelocation = $directory."backup/".stripslashes(trim(trim($backupFile, "/"), "."));
	
		if(file_exists($filelocation))
		{
			unlink($filelocation);
			return true;
		}
		
		return false;
	}
}
?>