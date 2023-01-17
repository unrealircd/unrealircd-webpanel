<?php
$conn = NULL;

require_once "../../common.php";
require_once "../../header.php";
require_once "SQL/user.php";
do_log($_POST);

if (isset($_POST))
{
    $p = $_POST;
    
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