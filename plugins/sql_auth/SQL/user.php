<?php

/**
 * SQLA_User
 * This is the User class for the SQL_Auth plugin
 */
class SQLA_User
{
    public $id = NULL;
    public $username = NULL;
    private $passhash = NULL;
    public $first_name = NULL;
    public $last_name = NULL;
    public $created = NULL;
    public $user_meta = [];
    public $bio = NULL;

    /**
     * Find a user in the database by name or ID
     * @param string $name
     * @param mixed $id
     */
    function __construct(string $name = NULL, int $id = NULL)
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
        if (isset($data[0]) && $data = $data[0])
        {
            $this->id = $data['user_id'];
            $this->username = $data['user_name'];
            $this->passhash = $data['user_pass'];
            $this->first_name = $data['user_fname'] ?? NULL;
            $this->last_name = $data['user_lname'] ?? NULL;
            $this->created = $data['created'];
            $this->bio = $data['user_bio'];
            $this->user_meta = (new SQLA_User_Meta($this->id))->list;
        }
    }

    /**
     * Verify a user's password
     * @param string $input
     * @return bool
     */
    function password_verify(string $input) : bool
    {
        if (password_verify($input, $this->passhash))
            return true;
        return false;
    }

    /**
     * Add user meta data
     * @param string $key
     * @param string $value
     * @return bool
     */
    function add_meta(string $key, string $value)
    {
        if (!$key || !$value)
            return false;

        $meta = [
            "id" => $this->id,
            "key" => $key,
            "value" => $value
        ];
        
        $conn = sqlnew();

        /* check if it exists first, update it if it does */
        $query = "SELECT * FROM " . SQL_PREFIX . "user_meta WHERE user_id = :id AND meta_key = :key";
        $stmt = $conn->prepare($query);
        $stmt->execute(["id" => $this->id, "key" => $key]);
        if ($stmt->rowCount()) // it exists, update instead of insert
        {
            $query = "UPDATE " . SQL_PREFIX . "user_meta SET meta_value = :value WHERE user_id = :id AND meta_key = :key";
            $stmt = $conn->prepare($query);
            $stmt->execute($meta);
            if ($stmt->rowCount())
                return true;
            return false;
        }

        else
        {
            $query = "INSERT INTO " . SQL_PREFIX . "user_meta (user_id, meta_key, meta_value) VALUES (:id, :key, :value)";
            $stmt = $conn->prepare($query);
            $stmt->execute($meta);
            if ($stmt->rowCount())
                return true;
            return false;
        }
    }

    /**
     * Delete user meta data by key
     * @param string $key
     * @return bool
     */
    function delete_meta(string $key)
    {
        if (!$key )
            return false;

        $meta = [
            "id" => $this->id,
            "key" => $key,
        ];
        
        $conn = sqlnew();
        $query = "DELETE FROM " . SQL_PREFIX . "user_meta WHERE user_id = :id AND  meta_key = :key";
        $stmt = $conn->prepare($query);
        $stmt->execute($meta);
        if ($stmt->rowCount())
            return true;
        return false;

    }
}


/**
 * This class looks up and returns any user meta.
 * This is used by SQLA_User, so you won't need to 
 * call it separately from SQLA_User.
 */
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

/**
 * Array of user
 * 
 * Required:
 * user_name
 * user_pass
 * 
 * Optional:
 * user_fname
 * user_lname
 * 
 * @param array $user
 * @throws Exception
 * @return bool
 */
function create_new_user(array $user) : bool
{
    if (!isset($user['user_name']) || !isset($user['user_pass']))
        throw new Exception("Attempted to add user without specifying user_name or user_pass");

    $username = $user['user_name'];
    $password = password_hash($user['user_pass'], PASSWORD_ARGON2ID);
    $first_name = (isset($user['fname'])) ? $user['fname'] : NULL;
    $last_name = (isset($user['lname'])) ? $user['lname'] : NULL;
    $user_bio = (isset($user['user_bio'])) ? $user['user_bio'] : NULL;
    

    $conn = sqlnew();
    $prep = $conn->prepare("INSERT INTO " . SQL_PREFIX . "users (user_name, user_pass, user_fname, user_lname, user_bio, created) VALUES (:name, :pass, :fname, :lname, :user_bio, :created)");
    $prep->execute(["name" => $username, "pass" => $password, "fname" => $first_name, "lname" => $last_name, "user_bio" => $user_bio, "created" => date("Y-m-d H:i:s")]);
    
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

/**
 * Delete a user and related meta
 * @param int $id The ID of the user in the SQL database.
 * @param array $info Optional: This will fill with a response.
 * @return int
 * 
 * Return values:
 *  1   The user was successfully deleted.
 *  0   The user was not found
 *  -1  The admin does not have permission to delete users [TODO]
 */
function delete_user(int $id, &$info = []) : int
{
    $user = new SQLA_User(NULL, $id);
    if (!$user->id) {
        $info[] = "Could not find user";
        var_dump("return 1");
        return 0;
    }
    $query = "DELETE FROM " . SQL_PREFIX . "users WHERE user_id = :id";
    $conn = sqlnew();
    $stmt = $conn->prepare($query);
    $stmt->execute(["id" => $user->id]);
    $deleted = $stmt->rowCount();
    if ($user->id)
    {
        $info[] = "Successfully deleted user \"$user->username\"";
        return 1;
    }
    $info[] = "Unknown error";
    return 0;
}

