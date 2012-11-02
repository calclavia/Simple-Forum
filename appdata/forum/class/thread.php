<?php
/**
 * A thread contains various posts within it.
 * @author Calclavia
 */
class Thread extends ForumElement
{
	//The ID of the Board this is in.
	private $board;
	
	private $timePosted;
	
	function __construct($id, $name, $forum, $board)
	{
		$this->id = $id;
		$this->name = $name;
		$this->forum = $forum;
		$this->board = $board;
		$this->firstSave();
		$this->timePosted = time();
	}
	
	function getDate()
	{
		return date("F j, Y, g:i a", $this->timePosted);
	}
	
	//@post - A Post object.
	function addPost($post)
	{
		$this->content = $this->content.",".$post->id;
	}
	
	function getPosts()
	{
		return explode($this->content, ",");
	}
	
	protected function getFileName()
	{
		return preg_replace('/\s+/ ', "_", trim($this->board."_".$this->id.".thrd"));
	}
	
	static public function load($forum, $fileName)
	{
		return parent::load($forum."/".$fileName.".thrd");
	}
	
	protected function getForumElements()
	{
		global $DATA_DIRECTORY;
		
		$extension = ".pst";
		$returnArray = array();

		if($handle = opendir($DATA_DIRECTORY.$this->forum))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != ".." && strstr($entry, $extension))
				{
					$element = Post::load($this->id, str_replace($extension, "", $entry));
					
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