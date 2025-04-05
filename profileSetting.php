<?php
session_start();
$info=null;
require_once 'operation/connection.php';

if (!isset($_SESSION["id"])){
    header("location:login.php");
}
$user_id = $_SESSION["id"];

$userInfo = $conn->prepare("SELECT name, surname, biography, gender FROM user WHERE userId = :user_id");
$userInfo->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$userInfo->execute();
$userData = $userInfo->fetch(PDO::FETCH_ASSOC);

if (isset($_POST["profile_update"])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $biography = $_POST['biography'];
    $gender = $_POST['gender'];

    $updateData = $conn->prepare("UPDATE user SET name = :name, surname = :surname, biography = :biography, gender = :gender WHERE userId = :user_id");
    $updateData->bindParam(':name', $name, PDO::PARAM_STR);
    $updateData->bindParam(':surname', $surname, PDO::PARAM_STR);
    $updateData->bindParam(':biography', $biography, PDO::PARAM_STR);
    $updateData->bindParam(':gender', $gender, PDO::PARAM_STR);
    $updateData->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($updateData->execute()) {
        header("Location: profileSetting.php");

    } else {
        $info = '<div class="alert alert-danger">Güncelleme sırasında bir hata oluştu!</div>';
    }
}

$accountInfo = $conn->prepare("SELECT userMail, userName FROM user WHERE userId = :user_id");
$accountInfo->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$accountInfo->execute();
$accountData = $accountInfo->fetch(PDO::FETCH_ASSOC);

if (isset($_POST["account_update"])) {
    $userMail = $_POST['userMail'];
    $userName = $_POST['userName'];

    if (strlen($userName) < 3 || strlen($userName) > 20) {
        $info = '<div class="alert alert-danger">Kullanıcı Adınız 3 ile 20 Karakter Arasında Olmalıdır!</div>';
    }else{
        $updateData = $conn->prepare("UPDATE user SET userMail = :userMail, userName = :userName WHERE userId = :user_id");
        $updateData->bindParam(':userMail', $userMail, PDO::PARAM_STR);
        $updateData->bindParam(':userName', $userName, PDO::PARAM_STR);
        $updateData->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        if ($updateData->execute()) {
            header("Location: profileSetting.php");
            exit();
        } else {
            $info = '<div class="alert alert-danger">Güncelleme sırasında bir hata oluştu!</div>';
        }
    }

}
$accountPasswordInfo = $conn->prepare("SELECT password FROM user WHERE userId = :user_id");
$accountPasswordInfo->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$accountPasswordInfo->execute();
$accountPasswordData = $accountPasswordInfo->fetch(PDO::FETCH_ASSOC);

if (isset($_POST["password_update"])) {
    // Add this check to prevent undefined key error
    if (!isset($_POST['oldPassword']) || !isset($_POST['password'])) {
        $info = '<div class="alert alert-danger">Lütfen tüm alanları doldurunuz!</div>';
    } else {
        $oldPassword = $_POST['oldPassword'];
        $newPassword = $_POST['password'];
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $newPassword)) {
            $info = '<div class="alert alert-danger">Şifreniz en az bir büyük harf, bir küçük harf ve bir rakam içermelidir!</div>';
        }else{
            if (password_verify($oldPassword, $accountPasswordData['password'])) {
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updatePasswordData = $conn->prepare("UPDATE user SET password = :hashedPassword WHERE userId = :user_id");
                $updatePasswordData->bindParam(':hashedPassword', $hashedNewPassword, PDO::PARAM_STR);
                $updatePasswordData->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                if ($updatePasswordData->execute()) {
                    header("Location: profileSetting.php");
                    exit();
                } else {
                    $info = '<div class="alert alert-danger">Güncelleme sırasında bir hata oluştu!</div>';
                }
            } else {
                $info = '<div class="alert alert-danger">Eski şifreniz yanlış!</div>';
            }
        }
    }
}


if (isset($_POST['deleteAccount']) && isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];

    try {
        $stmt = $conn->prepare("UPDATE user SET status = 0 WHERE userId = :userId");
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            session_destroy();
            header("Location: login.php");
            exit;
        } else {
            echo $info = '<div class="alert alert-danger">Bir hata oluştu!</div>';
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo $info = '<div class="alert alert-danger">Bir hata oluştu!</div>';

    }
}

if (isset($_POST['upload_image'])) {
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
        $user_id = $_SESSION["id"];
        $uploadDir = 'uploads/profileImage/' . $user_id . '/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['fileUpload']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $newFileName = 'profile_' . $user_id . '_' . uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExt, $allowedTypes)) {
            if ($_FILES['fileUpload']['size'] <= 5 * 1024 * 1024) {
                $existingImageQuery = $conn->prepare("SELECT profileImage FROM user WHERE userId = :user_id");
                $existingImageQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $existingImageQuery->execute();
                $existingImageData = $existingImageQuery->fetch(PDO::FETCH_ASSOC);

                if (!empty($existingImageData['profileImage']) && file_exists($existingImageData['profileImage'])) {
                    unlink($existingImageData['profileImage']);
                }

                if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $uploadPath)) {
                    $updateImageQuery = $conn->prepare("UPDATE user SET profileImage = :profileImage WHERE userId = :user_id");
                    $updateImageQuery->bindParam(':profileImage', $uploadPath, PDO::PARAM_STR);
                    $updateImageQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                    if ($updateImageQuery->execute()) {
                        $info = '<div class="alert alert-success">Profil resmi başarıyla güncellendi!</div>';
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        unlink($uploadPath);
                        $info = '<div class="alert alert-danger">Veritabanı güncellemesi başarısız oldu!</div>';
                    }
                } else {
                    $info = '<div class="alert alert-danger">Dosya yüklenirken bir hata oluştu!</div>';
                }
            } else {
                $info = '<div class="alert alert-danger">Dosya boyutu 5MB\'dan büyük olamaz!</div>';
            }
        } else {
            $info = '<div class="alert alert-danger">Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir!</div>';
        }
    } else {
        $info = '<div class="alert alert-danger">Lütfen bir dosya seçin!</div>';
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
<div class="profile-setting ">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">Profilim</h4>
                    <div class="breadcrumb-action justify-content-center flex-wrap">
                        <nav aria-label="breadcrumb">

                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-lg-4 col-sm-5">
                <div class="card mb-25">
                    <div class="card-body text-center p-0">
                        <div class="account-profile border-bottom pt-25 px-25 pb-0 flex-column d-flex align-items-center ">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="ap-img mb-20 pro_img_wrapper">
                                    <input id="file-upload" type="file" name="fileUpload" class="d-none"accept="image/jpeg,image/png">
                                    <label for="file-upload">
                                        <img class="ap-img__main rounded-circle wh-120"
                                             src="<?php
                                             $profileImageQuery = $conn->prepare("SELECT profileImage FROM user WHERE userId = :user_id");
                                             $profileImageQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                             $profileImageQuery->execute();
                                             $profileImageData = $profileImageQuery->fetch(PDO::FETCH_ASSOC);

                                             echo !empty($profileImageData['profileImage']) && file_exists($profileImageData['profileImage'])
                                                 ? $profileImageData['profileImage']
                                                 : 'img/default-profile.png';
                                             ?>"
                                             alt="profile">
                                        <span class="cross" id="remove_pro_pic">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg replaced-svg">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                </span>
                                    </label>
                                </div>
                                <button type="submit" name="upload_image" class="btn btn-primary">Profil Resmini Güncelle</button>
                            </form>
                            <div class="ap-nameAddress pb-3">
                                <h5 class="ap-nameAddress__title"><?php echo $userData['name'].' '.$userData['surname']?></h5>
                            </div>
                        </div>

                        <div class="ps-tab p-20 pb-25">
                            <div class="nav flex-column text-start" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-selected="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg replaced-svg"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>Profili Düzenle</a>
                                <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-selected="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg replaced-svg">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>Hesap Ayarları</a>
                                <a class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" href="#v-pills-messages" role="tab" aria-selected="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg replaced-svg"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>Şifre Değiştir</a>
                            </div>
                        </div>
                        <?php echo $info;?>
                    </div>
                </div>
            </div>
            <div class="col-xxl-9 col-lg-8 col-sm-7">
                <div class="as-cover">
                    <div class="as-cover__imgWrapper">
                    </div>
                </div>
                <div class="mb-50">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade active show" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                            <!-- Edit Profile -->
                            <div class="edit-profile mt-25">
                                <div class="card">
                                    <div class="card-header px-sm-25 px-3">
                                        <div class="edit-profile__title">
                                            <h6>Profili Düzenle</h6>
                                            <span class="fs-13 color-light fw-400">Kişisel bilgilerinizi ayarlayın</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row justify-content-center">
                                            <div class="col-xxl-6">
                                                <div class="edit-profile__body mx-xl-20">
                                                    <form action="" method="POST" name="profilDuzen">
                                                        <div class="form-group mb-20">
                                                            <label for="names">Ad</label>
                                                            <input type="text" name="name" class="form-control" id="names" value="<?php echo $userData['name']?>">
                                                        </div>
                                                        <div class="form-group mb-20">
                                                            <label for="names">Soyad</label>
                                                            <input type="text" name="surname" class="form-control" id="surname" value="<?php echo $userData['surname']?>">
                                                        </div>
                                                        <div class="form-group mb-20">
                                                            <label for="userBio">Biyografi</label>
                                                            <textarea name="biography" class="form-control" id="userBio" rows="3"><?php echo $userData['biography']?></textarea>
                                                        </div>
                                                        <div class="skillsOption">
                                                            <label for="gender">Cinsiyet</label>
                                                            <div class="form-group">
                                                                <select class="form-control" id="gender" name="gender">
                                                                    <option value="" disabled <?= empty($userData['gender']) ? 'selected' : '' ?>>Cinsiyet Seçin</option>
                                                                    <option value="KADIN" <?= $userData['gender'] === 'KADIN' ? 'selected' : '' ?>>Kadın</option>
                                                                    <option value="ERKEK" <?= $userData['gender'] === 'ERKEK' ? 'selected' : '' ?>>Erkek</option>
                                                                    <option value="BELIRTMEK_ISTEMIYORUM" <?= $userData['gender'] === 'BELIRTMEK_ISTEMIYORUM' ? 'selected' : '' ?>>Belirtmek İstemiyorum</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="button-group d-flex flex-wrap pt-30 mb-15">
                                                            <button name="profile_update" class="btn btn-primary btn-default btn-squared me-15 text-capitalize btn-sm">Profili Güncelle
                                                            </button>
                                                            <button class="btn btn-light btn-default btn-squared fw-400 text-capitalize btn-sm">İptal
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="edit-profile mt-25">
                                <div class="card">
                                    <div class="card-header  px-sm-25 px-3">
                                        <div class="edit-profile__title">
                                            <h6>Hesap Ayarları</h6>
                                            <span class="fs-13 color-light fw-400">Kullanıcı adınızı güncelleyin ve hesabınızı yönetin</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row justify-content-center">
                                            <div class="col-xxl-6">
                                                <div class="edit-profile__body mx-xl-20">
                                                    <form action="" method="POST">
                                                        <div class="form-group mb-20">
                                                            <label for="website">E-posta</label>
                                                            <input type="email" class="form-control" id="userMail" name="userMail"value="<?php echo $accountData['userMail']?>">
                                                        </div>
                                                        <div class="form-group mb-20">
                                                            <label for="company1">Kullanıcı Adı</label>
                                                            <input type="text" class="form-control" id="userName" name="userName" value="<?php echo $accountData['userName']?>">
                                                        </div>
                                                        <div class="button-group d-flex flex-wrap pt-35 mb-35">
                                                            <button name="account_update" class="btn btn-primary btn-default btn-squared me-15 text-capitalize">Değişiklikleri Kaydet
                                                            </button>
                                                            <button class="btn btn-light btn-default btn-squared fw-400 text-capitalize">İptal
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="row justify-content-center align-items-center">
                                            <form action="" method="post">
                                                <div class="col-xxl-6">
                                                    <div class="d-flex justify-content-between mt-1 align-items-center flex-wrap">
                                                        <div class="text-capitalize py-10">
                                                            <h6>Hesabı Kapat</h6>
                                                            <span class="fs-13 color-light fw-400">Hesabınızı ve hesap verilerinizi silin</span>
                                                        </div>
                                                        <div class="my-sm-0 my-10 py-10">
                                                            <button name="deleteAccount" class="btn btn-danger btn-default btn-squared fw-400 text-capitalize">Hesabı Kapat
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                            <div class="edit-profile mt-25">
                                <div class="card">
                                    <div class="card-header  px-sm-25 px-3">
                                        <div class="edit-profile__title">
                                            <h6>Şifre Değiştir</h6>
                                            <span class="fs-13 color-light fw-400">Hesabınızı değiştirin veya sıfırlayın şifre</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="row justify-content-center">
                                            <div class="col-xxl-6">
                                                <div class="edit-profile__body mx-xl-20">
                                                        <div class="form-group mb-20">
                                                            <label for="name">Eski Şifre</label>
                                                            <input type="text" class="form-control" id="password" name="oldPassword" required>
                                                        </div>
                                                        <div class="form-group mb-1">
                                                            <label for="password-field">Yeni Şifre</label>
                                                            <div class="position-relative">
                                                                <input id="password-field" type="password" class="form-control" name="password" required>
                                                            </div>
                                                            <small id="passwordHelpInline" class="text-light fs-13">Minimum 6 karakter
                                                            </small>
                                                            <div class="button-group d-flex flex-wrap pt-45 mb-35">
                                                                <button name="password_update" class="btn btn-primary btn-default btn-squared me-15 text-capitalize">Değişiklikleri Kaydet
                                                                </button>
                                                                <button class="btn btn-light btn-default btn-squared fw-400 text-capitalize">İptal
                                                                </button>
                                                            </div>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include 'footer.php';
include 'header.php';
