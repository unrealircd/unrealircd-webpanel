<?php

class SQLA_User
{
    public $id = NULL;
    public $username = NULL;
    protected $passhash = NULL;
    public $first_name = NULL;
    public $last_name = NULL;
    public $user_meta = [];

    /**
     * Find a user in the database by name or ID
     * @param string $name
     * @param mixed $id
     */
    function __construct(string $name = NULL, $id = NULL)
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
        $data = NULL;
        if ($prep)
            $data = $prep->fetchAll();
        if ($data = $data[0])
        {
            $this->id = $data['user_id'];
            $this->username = $data['user_name'];
            $this->passhash = $data['user_pass'];
            $this->first_name = $data['user_fname'] ?? NULL;
            $this->last_name = $data['user_lname'] ?? NULL;
            $this->user_meta = (new SQLA_User_Meta($this->id))->list;
        }
    }

    function password_verify(string $input)
    {
        if (password_verify($input, $this->passhash))
            return true;
        return false;
    }
}

class SQLA_User_Meta
{
    public $list = [];
    function __construct($id)
    {
        $conn = sqlnew();
        if ($id)
        {
            $prep = $conn->prepare("SELECT * FROM " . SQL_PREFIX . "user_meta WHERE user_id = :id");
            $prep->execute(["id" => $id]);
        }
        foreach ($prep->fetchAll() as $row)
        {
            $this->list[$row['meta_key']] = $row['meta_value'];
        }
    }
}

function create_new_user(array $user) : bool
{
    if (!isset($user['user_name']) || !isset($user['user_pass']))
        throw new Exception("Attempted to add user without specifying user_name or user_pass");

    $username = $user['user_name'];
    $password = password_hash($user['user_pass'], PASSWORD_ARGON2ID);
    $first_name = (isset($user['fname'])) ? $user['fname'] : NULL;
    $last_name = (isset($user['lname'])) ? $user['lname'] : NULL;

    $conn = sqlnew();
    $prep = $conn->prepare("INSERT INTO " . SQL_PREFIX . "users (user_name, user_pass, user_fname, user_lname) VALUES (:name, :pass, :fname, :lname)");
    $prep->execute(["name" => $username, "pass" => $password, "fname" => $first_name, "lname" => $last_name]);
    
    return true;
}

/**
 * Gets the user object for the current session
 * @return SQLA_User|bool
 */
function unreal_get_current_user() : SQLA_User|bool
{
    session_start();
    if (isset($_SESSION['id']))
    {
        $user = new SQLA_User(NULL, $_SESSION['id']);
        if ($user->id)
            return $user;
    }
    return false;
}

/**
 * Checks if a user can do something
 * @param string $permission
 * @return bool
 */
function current_user_can() : bool
{
    
    return false;
}

