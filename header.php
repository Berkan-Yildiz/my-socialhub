<?php
$info=null;
require_once 'operation/connection.php';

if (!isset($_SESSION["id"])){
    header("location:login.php");
    exit();
}
$user_id = $_SESSION["id"];
$userInfo = $conn->prepare("SELECT name, surname, biography FROM user WHERE userId = :user_id");
$userInfo->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$userInfo->execute();
$userData = $userInfo->fetch(PDO::FETCH_ASSOC);
?>
    <header class="header-top">
        <nav class="navbar navbar-light">
            <div class="navbar-left">
                <a class="navbar-brand" href="mainPage.php">
                    <img class="dark" src="img/logo-dark.png" alt="logo">
                    <img class="light" src="img/logo-white.png" alt="logo">
                </a>
                <div class="top-menu">
                    <div class="hexadash-top-menu position-relative">
                        <ul class="d-flex align-items-center flex-wrap">
                            <a href="mainPage.php">
                                <li class="has-subMenu fw-bold">
                                    Ana Sayfa
                                </li>
                            </a>
                            <a href="profile.php">
                                <li class="has-subMenu fw-bold">
                                    Profil
                                </li>
                            </a>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="navbar-right">
                <div class="navbar-right__mobileAction d-md-none">
                    <a href="#" class="btn-search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg feather-search replaced-svg"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg feather-x replaced-svg"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></a>
                    <a href="#" class="btn-author-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg replaced-svg"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg></a>
                </div>
                <ul class="navbar-right__menu">

                    <li class="nav-author">
                        <div class="dropdown-custom">
                            <a href="profile.php" class="nav-item-toggle">
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
                                <span class="nav-item__title"><?php echo $userData['name'] .' '.$userData['surname']; ?><i class="las la-angle-down nav-item__arrow"></i></span>
                            </a>
                            <div class="dropdown-parent-wrapper">
                                <div class="dropdown-wrapper">
                                    <div class="nav-author__info">
                                        <div class="author-img">
                                           <span class="profile-image bg-opacity-secondary rounded-circle d-block avatar avatar-md m-0"
                                                 style="background-image: url('<?php
                                                 $profileImageQuery = $conn->prepare("SELECT profileImage FROM user WHERE userId = :user_id");
                                                 $profileImageQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                                 $profileImageQuery->execute();
                                                 $profileImageData = $profileImageQuery->fetch(PDO::FETCH_ASSOC);

                                                 echo !empty($profileImageData['profileImage']) && file_exists($profileImageData['profileImage'])
                                                     ? $profileImageData['profileImage']
                                                     : 'img/default-profile.png';
                                                 ?>'); background-size: cover; background-position: center; width: 50px; height: 50px; display: inline-block; border-radius: 50%;">
                                                            </span>
                                        </div>
                                        <div>
                                            <h6>
                                                <?php echo $userData['name'] .' '.$userData['surname']; ?>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="nav-author__options">
                                        <ul>
                                            <li>
                                                <a href="profile.php">
                                                    <i class="uil uil-user"></i> Profil</a>
                                            </li>
                                            <li>
                                                <a href="profileSetting.php">
                                                    <i class="uil uil-setting"></i>
                                                    Ayarlar</a>
                                            </li>
                                        </ul>
                                        <a href="logout.php" class="nav-author__signout">
                                            <i class="uil uil-sign-out-alt"></i> Çıkış yap</a>
                                    </div>
                                </div>
                                <!-- ends: .dropdown-wrapper -->
                                <li class="nav-search">
                                    <a href="#" class="search-toggle">

                                        <i class="uil uil-times"></i>
                                    </a>
                                    <form action="/" class="search-form-topMenu">
                                        <span class="search-icon uil uil-search"></span>
                                        <input class="form-control me-sm-2 box-shadow-none" type="search" placeholder="Search..." aria-label="Search">
                                    </form>
                                </li>
                            </div>
                        </div>
                    </li>
                </ul></div>
        </nav>
    </header>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBgYKHZB_QKKLWfIRaYPCadza3nhTAbv7c"></script>
    <!-- inject:js-->
    <script src="assets/vendor_assets/js/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/vendor_assets/js/jquery/jquery-ui.js"></script>
    <script src="assets/vendor_assets/js/bootstrap/popper.js"></script>
    <script src="assets/vendor_assets/js/bootstrap/bootstrap.min.js"></script>
    <script src="assets/vendor_assets/js/moment/moment.min.js"></script>
    <script src="assets/vendor_assets/js/accordion.js"></script>
    <script src="assets/vendor_assets/js/apexcharts.min.js"></script>
    <script src="assets/vendor_assets/js/autoComplete.js"></script>
    <script src="assets/vendor_assets/js/Chart.min.js"></script>
    <script src="assets/vendor_assets/js/daterangepicker.js"></script>
    <script src="assets/vendor_assets/js/drawer.js"></script>
    <script src="assets/vendor_assets/js/dynamicBadge.js"></script>
    <script src="assets/vendor_assets/js/dynamicCheckbox.js"></script>
    <script src="assets/vendor_assets/js/footable.min.js"></script>
    <script src="assets/vendor_assets/js/fullcalendar@5.2.0.js"></script>
    <script src="assets/vendor_assets/js/google-chart.js"></script>
    <script src="assets/vendor_assets/js/jquery-jvectormap-2.0.5.min.js"></script>
    <script src="assets/vendor_assets/js/jquery-jvectormap-world-mill-en.js"></script>
    <script src="assets/vendor_assets/js/jquery.countdown.min.js"></script>
    <script src="assets/vendor_assets/js/jquery.filterizr.min.js"></script>
    <script src="assets/vendor_assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/vendor_assets/js/jquery.peity.min.js"></script>
    <script src="assets/vendor_assets/js/jquery.star-rating-svg.min.js"></script>
    <script src="assets/vendor_assets/js/leaflet.js"></script>
    <script src="assets/vendor_assets/js/leaflet.markercluster.js"></script>
    <script src="assets/vendor_assets/js/loader.js"></script>
    <script src="assets/vendor_assets/js/message.js"></script>
    <script src="assets/vendor_assets/js/moment.js"></script>
    <script src="assets/vendor_assets/js/muuri.min.js"></script>
    <script src="assets/vendor_assets/js/notification.js"></script>
    <script src="assets/vendor_assets/js/popover.js"></script>
    <script src="assets/vendor_assets/js/select2.full.min.js"></script>
    <script src="assets/vendor_assets/js/slick.min.js"></script>
    <script src="assets/vendor_assets/js/trumbowyg.min.js"></script>
    <script src="assets/vendor_assets/js/wickedpicker.min.js"></script>
    <script src="assets/theme_assets/js/apexmain.js"></script>
    <script src="assets/theme_assets/js/charts.js"></script>
    <script src="assets/theme_assets/js/drag-drop.js"></script>
    <script src="assets/theme_assets/js/footable.js"></script>
    <script src="assets/theme_assets/js/full-calendar.js"></script>
    <script src="assets/theme_assets/js/googlemap-init.js"></script>
    <script src="assets/theme_assets/js/icon-loader.js"></script>
    <script src="assets/theme_assets/js/jvectormap-init.js"></script>
    <script src="assets/theme_assets/js/leaflet-init.js"></script>
    <script src="assets/theme_assets/js/main.js"></script>
    </body>
    </html>
<?php