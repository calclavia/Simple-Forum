<?php
/**
 * A base class extended by various forum elements.
 * @author Calclavia
 */
abstract class ForumElement
{	
	//The name of this forum element.
	protected $name = "";
	
	//The moderators of this forum element.
	protected $moderators = array();

	//The contents of this forum element.
	protected $content = "";
	
	//The permission Set for this forum element.
	protected $permission = "";
	
	public function getName()
	{
		return $this->name;
	}

	public function getContents()
	{
		return $this->contents;
	}
	
	//Checks to see if this username is a moderator
	public function isModerator($userID)
	{
		foreach($this->moderators as $key => $value)
		{
			if($userID == $value)
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function getPermissionSet()
	{
		return $this->permission;
	}
	
	public function addModerator($moderator)
	{
		$this->moderators[] = $moderator;
	}
	
	public function removeModerator($userID)
	{
		for($i = 0; $i < count($this->moderators); $i ++)
		{
			if($this->moderators[$i] == $userID)
			{
				unset($this->moderators[$i]);
				return true;
			}
		}
		
		return false;
	}
	
	private function createFile($filelocation)
	{
		$filelocation .= $this->id;
		
		if(!file_exists($filelocation))
		{
			$newfile = fopen($filelocation,"w+");
			fwrite($newfile, serialize($this));
			fclose($newfile);
			return true;
		}
		
		return false;
	}
	
	private function deleteFile($filelocation)
	{
		$filelocation .= $this->id;
		
		if(file_exists($filelocation))
		{
			unlink($filelocation);
			return true;
		}
		
		return false;
	}
	
	public function save()
	{
		global $DATA_DIRECTORY;
				
		if($this->createFile($DATA_DIRECTORY))
		{
			return $this->createFile($DATA_DIRECTORY);
		}
		
		return false;
	}
}
?>