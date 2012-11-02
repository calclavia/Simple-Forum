<?php
/**
 * A category contains various forum boards within.
 * @author Calclavia
 */
class Category extends ForumElement
{	
	function __construct($id, $name, $forum)
	{
		$this->id = $id;
		$this->name = $name;
		$this->forum = $forum;
		$this->firstSave();
	}
	
	protected function getFileName()
	{
		return preg_replace('/\s+/ ', "_", trim($this->forum."_".$this->id.".ctg"));
	}
	
	public function addBoard($boardName)
	{
		$board = new Board($this->getNexID(), $boardName, $this->forum, $this->id);
		$this->save();
		return $board->id;
	}
	
	public function getBoard($id)
	{
		foreach(self::getForumElements() as $value)
		{
			if($id == $value->id)
			{
				return $value;
			}
		}
	}
	
	public function getBoards()
	{
		return self::getForumElements();
	}
	
	protected function getForumElements()
	{
		global $DATA_DIRECTORY;
		
		$extension = ".brd";
		$returnArray = array();

		if($handle = opendir($DATA_DIRECTORY.$this->forum))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != ".." && strstr($entry, $extension))
				{
					$element = Board::load($this->forum, str_replace($extension, "", $entry));
					
					if($element != null)
					{
						if(strstr($entry, $this->id."_"))
						{
							$returnArray[] = $element;
						}
					}
				}
			}
			
			closedir($handle);
		}
		
		return array_reverse($returnArray);
	}
	
	static public function load($forum, $fileName)
	{
		return parent::load($forum."/".$fileName.".ctg");
	}
}
?>