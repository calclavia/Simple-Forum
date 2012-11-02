<?php
/**
 * A board can contain multiple threads and board within it.
 * @author Calclavia
 */
class Board extends ForumElement
{
	//The ID of the Category this is in.
	private $category;
	
	function __construct($id, $name, $forum, $category)
	{
		$this->id = $id;
		$this->name = $name;
		$this->forum = $forum;
		$this->category = $category;
		$this->firstSave();
	}
	
	protected function getFileName()
	{
		return preg_replace('/\s+/ ', "_", trim($this->category."_".$this->id.".brd"));
	}
	
	static public function load($forum, $fileName)
	{
		return parent::load($forum."/".$fileName.".brd");
	}
	
	public function addThread($name)
	{
		$thread = new Thread($this->getNexID(), $name, $this->forum, $this->id);
		$this->save();
		return $thread->id;
	}
	
	protected function getForumElements()
	{
		global $DATA_DIRECTORY;
		
		$extension = ".thrd";
		$returnArray = array();

		if($handle = opendir($DATA_DIRECTORY.$this->forum))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != ".." && strstr($entry, $extension))
				{
					$element = Thread::load($this->forum, str_replace($extension, "", $entry));
					
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
}
?>