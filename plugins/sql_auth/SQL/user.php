<?php

class SQLA_User
{
    public $id = NULL;
    public $username = NULL;
    protected $passhash = NULL;
    public $first_name = NULL;
    public $last_name = NULL;

    /**
     * Find a user in the database by name or ID
     * @param string $name
     * @param mixed $id
     */
    function __construct(string $name, $id = NULL)
    {
        $conn = sqlnew();

        if ($id)
        {
            $prep = $conn->prepare("SELECT * FROM " . SQL_PREFIX . "users WHERE user_id = :id LIMIT 1");
            $prep->execute(["id" => strtolower($id)]);
        }
        elseif ($name)
        {
            $prep = $conn->prepare("SELECT * FROM " . SQL_PREFIX . "users WHERE LOWER(user_name) = :name LIMIT 1");
            $prep->execute(["name" => strtolower($name)]);
        }
        $data = $prep->fetchAll();
        if ($data = $data[0])
        {
            $this->id = $data['user_id'];
            $this->username = $data['user_name'];
            $this->passhash = $data['user_pass'];
            $this->first_name = $data['first_name'] ?? NULL;
            $this->last_name = $data['last_name'] ?? NULL;
        }
    }

    function password_verify(string $input)
    {
        if (password_verify($input, $this->passhash))
            return true;
        return false;
    }

}


function get_current_user() : SQLA_User|bool
{
    session_start();
    if (isset($_SESSION['id']))
    {
        $user = new SQLA_User()
    }
    return false;
}