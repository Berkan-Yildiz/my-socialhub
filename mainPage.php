<?php
session_start();
require_once 'operation/connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
$info=null;

if (!isset($_SESSION["id"])){
    header("location:login.php");
}

$user_id = $_SESSION["id"];
//isim, soyisim, bio almak için
$userInfo = $conn->prepare("SELECT name, surname, biography, profileImage FROM user WHERE userId = :user_id");
$userInfo->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$userInfo->execute();
$userData = $userInfo->fetch(PDO::FETCH_ASSOC);

//yorum ekle
if (isset($_POST["postComment"])) {
    $commentText = $_POST["commentText"];
    $postId = $_POST["postId"];
    $commentDate = date("Y-m-d H:i:s");

    $insertComment = $conn->prepare("INSERT INTO postcomment (userId, postId, postComment, commentDate) VALUES (:userId, :postId, :commentText, :commentDate)");
    $insertComment->bindParam(':userId', $user_id, PDO::PARAM_INT);
    $insertComment->bindParam(':postId', $postId, PDO::PARAM_INT);
    $insertComment->bindParam(':commentText', $commentText);
    $insertComment->bindParam(':commentDate', $commentDate);

    if ($insertComment->execute()){
        header("Location: profile.php");
    } else{
        $info = '<div class="alert alert-danger">Yorum oluşturulurken bir hata oluştu.</div>';
    }
}

?>
    <!doctype html>
    <html lang="en" dir="ltr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Vayora Blog</title>
        <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- inject:css-->
        <link rel="stylesheet" href="assets/vendor_assets/css/bootstrap/bootstrap.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/daterangepicker.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/fontawesome.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/footable.standalone.min.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/fullcalendar@5.2.0.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/jquery-jvectormap-2.0.5.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/jquery.mCustomScrollbar.min.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/leaflet.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/line-awesome.min.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/magnific-popup.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/MarkerCluster.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/MarkerCluster.Default.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/select2.min.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/slick.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/star-rating-svg.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/trumbowyg.min.css">
        <link rel="stylesheet" href="assets/vendor_assets/css/wickedpicker.min.css">
        <link rel="stylesheet" href="style.css">
        <!-- endinject -->
        <link rel="icon" type="image/png" sizes="16x16" href="img/favicon.png">
        <!-- Fonts -->
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    </head>
    <body>
    <div class="container-fluid">
        <div class="profile-content mb-50">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-main">
                        <div class="breadcrumb-action justify-content-center flex-wrap">
                            <nav aria-label="breadcrumb">
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-4 mt-50">
                    <aside class="profile-sider">
                        <div class="card mb-25">
                            <div class="card-body text-center pt-sm-30 pb-sm-0  px-25 pb-0">
                                <div class="account-profile">
                                    <div class="ap-img w-100 d-flex justify-content-center">
                                        <!-- Profile picture image-->
                                        <img class="ap-img__main rounded-circle wh-120" src="<?php $profileImageQuery = $conn->prepare("SELECT profileImage FROM user WHERE userId = :user_id");
                                        $profileImageQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                        $profileImageQuery->execute();
                                        $profileImageData = $profileImageQuery->fetch(PDO::FETCH_ASSOC);
                                        // Display default image if no profile image is set
                                        echo !empty($profileImageData['profileImage']) && file_exists($profileImageData['profileImage'])
                                            ? $profileImageData['profileImage']
                                            : 'img/default-profile.png';?>" alt="profile">
                                    </div>
                                    <div class="ap-nameAddress pb-3 pt-1">
                                        <a href="profile.php">
                                            <h5 class="ap-nameAddress__title"><?php echo $userData['name']. ' ' .$userData['surname'];?></h5>
                                        </a>
                                    </div>
                                    <div class="ap-button button-group d-flex justify-content-center flex-wrap">
                                    </div>
                                </div>
                                <div class="card-footer mt-20 pt-20 pb-20 px-0 bg-transparent">
                                </div>
                            </div>
                        </div>
                        <div class="card mb-25">
                            <?php echo $info;?>
                            <!-- Info content -->
                        </div>
                    </aside>
                </div>
                <div class="col-xxl-9 col-md-8">
                    <div class="tab-content mt-25" id="ap-tabContent">
                        <div class="tab-pane fade" id="ap-overview" role="tabpanel" aria-labelledby="ap-overview-tab">
                            <div class="ap-content-wrapper">
                                <div class="row">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade active show" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                            <div class="ap-post-content">
                                <?php
                                $allDataPost = $conn->prepare('SELECT post.*, user.userName, user.profileImage, user.name, user.surname 
                                FROM post 
                                INNER JOIN user ON user.userId = post.userId 
                                WHERE post.postStatus != 0 
                                ORDER BY post.postDate DESC');
                                $allDataPost->execute();
                                while($postRow = $allDataPost->fetch(PDO::FETCH_ASSOC)){
                                    $postDate = new DateTime($postRow['postDate']);
                                    $currentDate = new DateTime();
                                    $interval = $currentDate->diff($postDate);
                                    if ($interval->y > 0) {
                                        $timeAgo = $interval->y . ' yıl önce';
                                    } elseif ($interval->m > 0) {
                                        $timeAgo = $interval->m . ' ay önce';
                                    } elseif ($interval->d > 0) {
                                        $timeAgo = $interval->d . ' gün önce';
                                    } elseif ($interval->h > 0) {
                                        $timeAgo = $interval->h . ' saat önce';
                                    } elseif ($interval->i > 0) {
                                        $timeAgo = $interval->i . ' dakika önce';
                                    } else {
                                        $timeAgo = 'Az önce';
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-xxl-8">
                                            <!-- Post Template -->
                                            <div class="ap-main-post">
                                                <div class="card mb-25">

                                                    <div class="card-body pb-0 px-sm-25 ap-main-post__header">
                                                        <div class="d-flex flex-row pb-20 border-top-0 border-left-0 border-right-0 ap-post-content__title align-items-center ">
                                                            <div class="d-inline-block align-middle me-15">
                                                                <span class="profile-image bg-opacity-secondary rounded-circle d-block avatar avatar-md m-0"
                                                                      style="background-image: url('<?php
                                                                      $profileImageQuery = $conn->prepare("SELECT profileImage FROM user WHERE userId = :post_user_id");
                                                                      $profileImageQuery->bindParam(':post_user_id', $postRow['userId'], PDO::PARAM_INT);
                                                                      $profileImageQuery->execute();
                                                                      $profileImageData = $profileImageQuery->fetch(PDO::FETCH_ASSOC);
                                                                      echo !empty($profileImageData['profileImage']) && file_exists($profileImageData['profileImage'])
                                                                          ? $profileImageData['profileImage']
                                                                          : 'img/default-profile.png';
                                                                      ?>'); background-size: cover; background-position: center; width: 50px; height: 50px; display: inline-block; border-radius: 50%;">
                                                            </span>
                                                            </div>
                                                            <h6 class="mb-0 flex-1 text-dark">
                                                                <?php echo $postRow['name'] .' '.$postRow['surname']; ?>
                                                                <small class="m-0 d-block">
                                                                    <?php echo $timeAgo;?>
                                                                </small>
                                                            </h6>
                                                            <div class="card-extra">
                                                                <div class="dropdown dropleft">
                                                                    <div class="dropdown-menu">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-15">
                                                            <img src='<?= htmlspecialchars($postRow["postImage"], ENT_QUOTES, 'UTF-8') ?>'
                                                                 alt="Post Image"
                                                                 class="ap-post-attach__headImg w-100">
                                                        </div>
                                                        <div class="pb-3 border-top-0 border-left-0 border-right-0 ap-post-content__p">
                                                            <?php echo $postRow['postText']?>
                                                        </div>
                                                    </div>
                                                    <div class="card-body border-top border-bottom py-0 ap-main-post__reaction">
                                                    </div>
                                                    <form action="" method="post" enctype="multipart/form-data">
                                                        <div class="card-body px-sm-25 py-20 ap-main-post__footer">
                                                            <div class="ap-post-content-comment">
                                                                <div class="pt-0 outline-0 pb-0 pe-0 ps-0 rounded-0 position-relative d-flex align-items-center" tabindex="-1">
                                                                    <span class="profile-image bg-opacity-secondary rounded-circle d-block avatar avatar-md m-0"
                                                                          style="background-image: url('<?php
                                                                          $profileImageQuery = $conn->prepare("SELECT profileImage FROM user WHERE userId = :user_id");
                                                                          $profileImageQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                                                          $profileImageQuery->execute();
                                                                          $profileImageData = $profileImageQuery->fetch(PDO::FETCH_ASSOC);

                                                                          echo !empty($profileImageData['profileImage']) && file_exists($profileImageData['profileImage'])
                                                                              ? $profileImageData['profileImage']
                                                                              : 'img/default-profile.png';
                                                                          ?>'); background-size: cover; background-position: center; width: 40px; height: 40px; display: inline-block; border-radius: 50%;">
                                                                    </span>
                                                                    <div class="d-flex justify-content-between align-items-center w-100">

                                                                        <input type="hidden" name="postId" value="<?php echo $postRow['postId']; ?>">
                                                                        <div class="flex-1 d-flex align-items-center me-10 ap-post-content-comment__write">
                                                                            <input name="commentText" class="form-control border-0 p-0 bg-transparent pe-sm-0 pe-20" placeholder="Bir yorum yap...">
                                                                            <div class="d-flex">
                                                                            </div>
                                                                        </div>
                                                                        <button name="postComment" type="submit" class="border-0 btn-primary wh-50 p-10 rounded-circle">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg replaced-svg">
                                                                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                                                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <!-- Comment Template -->
                                                    <?php
                                                    // Post yorumlarını çeken sorgu
                                                    $postcomment = $conn->prepare('SELECT postcomment.*, user.profileImage, user.userName ,user.name, user.surname
                                                                            FROM postcomment 
                                                                            INNER JOIN user ON user.userId = postcomment.userId 
                                                                            WHERE postId = :postID 
                                                                            ORDER BY commentDate DESC');

                                                    $postcomment->bindParam(':postID', $postRow['postId'], PDO::PARAM_INT);
                                                    $postcomment->execute();

                                                    while($postcommentrow = $postcomment->fetch(PDO::FETCH_ASSOC)){
                                                        $commentDate = new DateTime($postcommentrow['commentDate']);
                                                        $currentDate = new DateTime();
                                                        $commentInterval = $currentDate->diff($commentDate);

                                                        if ($commentInterval->y > 0) {
                                                            $commentTimeAgo = $commentInterval->y . ' yıl önce';
                                                        } elseif ($commentInterval->m > 0) {
                                                            $commentTimeAgo = $commentInterval->m . ' ay önce';
                                                        } elseif ($commentInterval->d > 0) {
                                                            $commentTimeAgo = $commentInterval->d . ' gün önce';
                                                        } elseif ($commentInterval->h > 0) {
                                                            $commentTimeAgo = $commentInterval->h . ' saat önce';
                                                        } elseif ($commentInterval->i > 0) {
                                                            $commentTimeAgo = $commentInterval->i . ' dakika önce';
                                                        } else {
                                                            $commentTimeAgo = 'Az önce';
                                                        }
                                                        ?>

                                                        <div class="card-body pt-20 ap-main-post__comment mb-2">
                                                            <div class="ap-post-cc-reply d-flex flex-column align-items-center">
                                                                <div class="d-flex flex-row w-100">
                                                                    <div class="d-inline-block align-middle status status-sm status-success">
                                                                                            <span class="profile-image bg-opacity-secondary profile-image-md rounded-circle d-block ms-0 wh-36 me-10"
                                                                                                  style="background-image:url('<?= htmlspecialchars($postcommentrow['profileImage'] ?: 'img/default-profile.png') ?>');
                                                                                                          background-size: cover;">
                                                                                            </span>
                                                                    </div>
                                                                    <div class="mb-0 flex-1 text-dark">
                                                                        <div class="cbg-light radius-xl py-10 px-10">
                                                                            <div class="d-flex ap-post-content__title">
                                                                                <h6><?= htmlspecialchars($postcommentrow['name'].' '.$postcommentrow['surname']) ?></h6>
                                                                                <small class="text-muted ms-2">
                                                                                    <small class="text-muted ms-2"><?php echo htmlspecialchars($commentTimeAgo); ?></small>
                                                                                </small>
                                                                            </div>
                                                                            <p class="mb-0 mt-10 text-gray">
                                                                                <?= htmlspecialchars($postcommentrow['postComment']) ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-4">
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>

                            </div>
                        </div>
                        <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                            <div class="ap-post-content">
                                <div class="row">
                                    <div class="col-xxl-8">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('[data-bs-toggle="dropdown"]').addEventListener('click', function () {
            var menu = this.nextElementSibling;
            menu.classList.toggle('show');
        });
    </script>
    </body>
    </html>

<?php
include 'footer.php';
include 'header.php';
