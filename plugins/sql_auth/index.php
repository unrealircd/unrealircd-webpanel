<?php
$conn = NULL;

require_once "../../common.php";
require_once "../../header.php";
require_once "SQL/user.php";
do_log($_POST);

if (isset($_POST))
{
    $p = $_POST;
    if (isset($p['sql_setup']))
    {
        if ($p['sql_setup'] == "add_tables")
        {
            $conn = sqlnew();
            $conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "users (
                user_id int AUTO_INCREMENT NOT NULL,
                user_name VARCHAR(255) NOT NULL,
                user_pass VARCHAR(255) NOT NULL,
                
                user_fname VARCHAR(255),
                user_lname VARCHAR(255),
                user_bio VARCHAR(255),
                created VARCHAR(255),
                PRIMARY KEY (user_id)
            )");
            $conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "user_meta (
                meta_id int AUTO_INCREMENT NOT NULL,
                user_id int NOT NULL,
                meta_key VARCHAR(255) NOT NULL,
                meta_value VARCHAR(255),
                PRIMARY KEY (meta_id)
            )");
            
        }
    }
}


$conn = sqlnew();
$count = $conn->query("SELECT count(*) FROM ".SQL_PREFIX."users")->fetchColumn();
?>
<div class="mt-5">
    <div class="card text-center" style="width: 18rem;">
            <div class="card-header bg-warning">
                <div class="row">
                    <div class="col">
                        <i class="fa fa-screwdriver-wrench fa-3x"></i>
                    </div>
                    <div class="col">
                        <h3 class="display-4"><?php echo $count; ?></h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h6>Panel Admins</h6>
                    </div>
                    <div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>users">View</a></div>
                </div>
            </div>
        </div>
    </div>
</div>