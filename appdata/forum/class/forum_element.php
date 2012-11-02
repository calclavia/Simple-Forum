<?php
/**
 * A base class extended by various forum elements.
 * @author Calclavia
 */

abstract class ForumElement
{
	protected $id = 0;
	
	//The name of the forum.
	protected $forum;
	
	//The name of this forum element.
	protected $name = "";
	
	//The moderators of this forum element. An array containing all the user IDs.
	protected $moderators = array();
	
	//The permission Set for this forum element.
	protected $permission = "";
	
	//The next ID for the next new category
	protected $nextID = 0;
	
	/**
	 * Gets the file name and the extension to be saved.
	 */
	abstract protected function getFileName();
	
	public function getID()
	{
		return $this->id;
	}
	
	protected function getNexID()
	{
		$this->nextID ++;
		return $this->nextID;
	}
	
	public function save()
	{
		global $DATA_DIRECTORY;
		
		$fileName = $DATA_DIRECTORY.$this->forum."/".$this->getFileName();
		
		if($this->deleteFile($fileName))
		{
			return $this->createFile($fileName);
		}
		
		return false;
	}
	
	public function firstSave()
	{
		global $DATA_DIRECTORY;
		$fileName = $DATA_DIRECTORY.$this->forum."/".$this->getFileName();
		return $this->createFile($fileName);
	}
	
	protected function createFile($filelocation)
	{		
		if(!file_exists($filelocation))
		{
			$newfile = fopen($filelocation,"w+");
			fwrite($newfile, serialize($this));
			fclose($newfile);
			return true;
		}
		
		return false;
	}
	
	protected function deleteFile($filelocation)
	{		
		if(file_exists($filelocation))
		{
			unlink($filelocation);
			return true;
		}
		
		return false;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getPermissionSet()
	{
		return $this->permission;
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
	
	/**
	 * @moderator - A moderator to be added.
	 */
	public function addModerator($moderator)
	{
		$this->moderators[] = $moderator;
	}
	
	/**
	 * @moderators - An array of all moderators to be added.
	 */
	public function addModerators($moderators)
	{
		$this->moderators[] = array_merge($this->moderators, $moderators);
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
	
	static public function load($fileName)
	{
		global $DATA_DIRECTORY;
		
		$fileName = $DATA_DIRECTORY.$fileName;
				
		if(file_exists($fileName))
		{
			$newfile = fopen($fileName, "r");
			$forum = unserialize(fread($newfile, filesize($fileName)));
			fclose($newfile);

			return $forum;
		}
		
		return null;
	}
	
	abstract protected function getForumElements();
	
	public function getChild($id)
	{
		foreach($this->getForumElements() as $value)
		{
			if($id == $value->id)
			{
				return $value;
			}
		}
	}
	
	public function getChildren()
	{
		return $this->getForumElements();
	}
}
?>