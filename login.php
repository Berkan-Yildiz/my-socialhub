<?php
session_start();
require_once 'operation/connection.php';
$info = null;

if (isset($_POST['submit'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    try {
        $stmt = $conn->prepare("SELECT * FROM user WHERE userName = :username OR userMail = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                // Session'ı kontrol et ve içeriğini ayarla
                if(empty($_SESSION["id"])) {
                    $_SESSION["id"] = $user['userId'];
                    $_SESSION["username"] = $user['userName'];
                }

                if(1 == $user['status']) {
                    header('Location: mainPage.php');
                } else {
                    $info = '<div class="alert alert-danger">Bu hesap aktif değil!</div>';
                }
            } else {
                $info = '<div class="alert alert-danger">Şifre Yanlış!</div>';
            }
        } else {
            $info = '<div class="alert alert-danger">Girdiğiniz Kullanıcı Adı veya E-posta Kayıtlı Değil!</div>';
        }
    } catch (PDOException $e) {

    }
}
?>
    <!doctype html>
    <html lang="tr" dir="ltr">
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
                                    <h6>Giriş Yap</h6>
                                </div>
                            </div>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="card-body">
                                    <div class="edit-profile__body">
                                        <div class="form-group mb-25">
                                            <label for="username">Kullanıcı Adı ya da E-posta</label>
                                            <input type="text" class="form-control" name="username" id="username" placeholder="forum@gmail.com" required>
                                        </div>
                                        <div class="form-group mb-15">
                                            <label for="password-field">Şifre</label>
                                            <div class="position-relative">
                                                <input id="password-field" type="password" class="form-control" name="password" placeholder="Şifre" required>
                                                <div class="uil uil-eye-slash text-lighten fs-15 field-icon toggle-password2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="admin-condition">
                                            <div class="checkbox-theme-default custom-checkbox">

                                            </div>
                                        </div>
                                        <div class="admin__button-group button-group d-flex pt-1 justify-content-md-start justify-content-center">
                                            <button type="submit" name="submit" class="btn btn-primary btn-default w-100 btn-squared text-capitalize lh-normal px-50 signIn-createBtn">
                                                Giriş Yap
                                            </button>
                                        </div>
                                        <p id="message">

                                        </p>
                                        <?php echo $info;?>
                                    </div>
                                </div>
                            </form>
                            <div class="admin-topbar">
                                <p class="mb-0">
                                    Hesabın Yok Mu?
                                    <a href="sign.php" class="color-primary">
                                        Kayıt Ol
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
<?php include 'footer.php';