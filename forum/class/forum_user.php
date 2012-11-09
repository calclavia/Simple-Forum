<?php

class ForumUser
{

    /*
     * The ID of the user.
     */

    public $id;

    /**
     * @var int Posts posted.
     */
    public $posts;

    /**
     * @var array Forum elements this user is moderating. 
     */
    public $moderate = array();

    /*
     * The display name of the user.
     */
    public $username;

    /*
     * The email of the user.
     */
    public $email;

    function __construct($id, $username, $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }

    public function hasPermission($permission, $element = null)
    {
        if ($this->id == -1)
        {
            return false;
        }

        if ($element != null)
        {
            if (in_array($element->prefix . $element->getID(), $moderate))
            {
                return true;
            }
            else if ($element instanceof Post)
            {
                if ($element->fields["User"] == $this->id)
                {
                    return true;
                }
            }
            else if ($element instanceof Thread)
            {
                if ($element->getFirstPost()->fields["User"] == $this->id)
                {
                    return true;
                }
            }
        }

        return $permission->default || hasPermission($permission, $element);
    }

}

?>
