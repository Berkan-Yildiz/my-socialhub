<?php
require_once 'operation/connection.php';
$info = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    $name = trim($_POST["name"]);
    $surname = trim($_POST["surname"]);
    $username = trim($_POST["username"]);
    $userMail = trim($_POST["userMail"]);
    $password = $_POST["password"];

    if (empty($name)) {
        $info = '<div class="alert alert-danger">İsim boş bırakılamaz!</div>';
    }
    if (empty($username)) {
        $info = '<div class="alert alert-danger">Kullanıcı Adı boş bırakılamaz!</div>';
    }
    if (empty($userMail)) {
        $info = '<div class="alert alert-danger">E-posta boş bırakılamaz!</div>';
    }
    if (empty($password)) {
        $info = '<div class="alert alert-danger">Şifre boş bırakılamaz!</div>';
    }

    if (!filter_var($userMail, FILTER_VALIDATE_EMAIL)) {
        $info = '<div class="alert alert-danger">Geçerli bir E-posta formatı giriniz!</div>';
    }

    if (strlen($username) < 3 || strlen($username) > 20) {
        $info = '<div class="alert alert-danger">Kullanıcı Adınız 3 ile 20 Karakter Arasında Olmalıdır!</div>';
    }
    if (strlen($password) < 6) {
        $info = '<div class="alert alert-danger">Şifreniz En Az 6 Karakter Olmalıdır!</div>';
    }
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        $info = '<div class="alert alert-danger">Şifreniz en az bir büyük harf, bir küçük harf ve bir rakam içermelidir!</div>';
    }
    try {
        $stmt = $conn->prepare("SELECT * FROM user WHERE userName = :username OR userMail = :userMail");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":userMail", $userMail);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existingUser["userName"] == $username) {
                $info = '<div class="alert alert-danger">Bu kullanıcı adı zaten kullanılıyor!</div>';
            }
            if ($existingUser["userMail"] == $userMail) {
                $info = '<div class="alert alert-danger">Bu e-posta zaten kullanılıyor!</div>';
            }
        }

        if (empty($info)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO user (name, surname, userName, userMail, password, status) VALUES (:name, :surname, :username, :userMail, :password, 1)");
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":surname", $surname);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":userMail", $userMail);
            $stmt->bindParam(":password", $hash);

            if ($stmt->execute()) {
                $info = '<div class="alert alert-success">Hesap başarıyla oluşturuldu 3 saniye sonra giriş ekranına yönlendirileceksiniz!</div>';
                header("Refresh: 1; url=login.php");
                exit();
            } else {
                $info = '<div class="alert alert-danger">Hesap oluşturulurken bir hata oluştu.</div>';
            }
        }
    } catch(PDOException $e) {
        $info = '<div class="alert alert-danger">Bir hata oluştu: ' . $e->getMessage() . '</div>';
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
<main class="main-content">
    <div class="admin">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xxl-3 col-xl-4 col-md-6 col-sm-8">
                    <div class="edit-profile">
                        <img class="dark" src="img/logo-dark1.png" alt="logo">
                        <div class="card border-0">
                            <div class="card-header">
                                <div class="edit-profile__title">
                                    <h6>Kayıt Ol</h6>
                                </div>
                            </div>
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
                                <div class="card-body">
                                    <div class="edit-profile__body">
                                        <div class="edit-profile__body">
                                            <div class="form-group mb-20">
                                                <label for="name">Ad</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="Ad" required>
                                            </div>
                                            <div class="form-group mb-20">
                                                <label for="surname">Soyad</label>
                                                <input type="text" class="form-control" id="name" name="surname" placeholder="Soyad" required>
                                            </div>
                                            <div class="form-group mb-20">
                                                <label for="username">Kullanıcı Adı</label>
                                                <input type="text" class="form-control" id="username" name="username" placeholder="Kullanıcı Adı" required>
                                            </div>
                                            <div class="form-group mb-20">
                                                <label for="email">E-posta</label>
                                                <input type="text" class="form-control" id="email" name="userMail" placeholder="forum@gmail.com" required>
                                            </div>
                                            <div class="form-group mb-15">
                                                <label for="password-field">Şifre</label>
                                                <div class="position-relative">
                                                    <input id="password-field" type="password" class="form-control" name="password" placeholder="Şifre" required>
                                                    <div class="uil uil-eye-slash text-lighten fs-15 field-icon toggle-password2"></div>
                                                </div>
                                            </div>
                                            <div class="admin__button-group button-group d-flex pt-1 justify-content-md-start justify-content-center">
                                                <button class="btn btn-primary btn-default w-100 btn-squared text-capitalize lh-normal px-50 signIn-createBtn ">
                                                    Hesap Oluştur
                                                </button>
                                            </div>
                                            <?php echo $info?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="admin-topbar">
                                <p class="mb-0">
                                    Bir Hesaba Sahip Misin?
                                    <a href="login.php" class="color-primary">
                                        Giriş Yap
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
include 'footer.php';