<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==========================
    // LẤY DỮ LIỆU TỪ FORM
    // ==========================

    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'avatar' => ''
    ];

    $contact = new Contact($PDO);

    // ==========================
    // KIỂM TRA DỮ LIỆU
    // ==========================

    $errors = $contact->validate($contactData);

    // ==========================
    // XỬ LÝ UPLOAD AVATAR
    // ==========================

    if (
        isset($_FILES['avatar']) &&
        $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        // Kiểm tra lỗi upload
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {

            $errors['avatar'] = 'Upload avatar failed.';

        } else {

            $tmpName = $_FILES['avatar']['tmp_name'];
            $fileSize = $_FILES['avatar']['size'];

            // --------------------------
            // Kiểm tra kích thước
            // Tối đa 2MB
            // --------------------------

            if ($fileSize > 2 * 1024 * 1024) {

                $errors['avatar'] =
                    'Avatar must not exceed 2MB.';

            } else {

                // --------------------------
                // Kiểm tra loại file
                // --------------------------

                $fileType = mime_content_type($tmpName);

                $allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp'
                ];

                if (!in_array($fileType, $allowedTypes)) {

                    $errors['avatar'] =
                        'Avatar must be JPG, PNG, GIF or WEBP.';

                } else {

                    // --------------------------
                    // Lấy phần mở rộng
                    // --------------------------

                    $extension = strtolower(
                        pathinfo(
                            $_FILES['avatar']['name'],
                            PATHINFO_EXTENSION
                        )
                    );

                    // --------------------------
                    // Tạo tên file mới
                    // --------------------------

                    $fileName =
                        uniqid('avatar_', true)
                        . '.'
                        . $extension;

                    // --------------------------
                    // Thư mục uploads
                    // --------------------------

                    $uploadDir = __DIR__ . '/uploads/';

                    // Nếu chưa có thư mục thì tạo
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // --------------------------
                    // Đường dẫn lưu file
                    // --------------------------

                    $destination =
                        $uploadDir . $fileName;

                    // --------------------------
                    // Lưu file
                    // --------------------------

                    if (
                        move_uploaded_file(
                            $tmpName,
                            $destination
                        )
                    ) {

                        // Lưu đường dẫn vào dữ liệu contact
                        $contactData['avatar'] =
                            'uploads/' . $fileName;

                    } else {

                        $errors['avatar'] =
                            'Cannot save avatar file.';
                    }
                }
            }
        }
    }

    // ==========================
    // LƯU CONTACT
    // ==========================

    if (empty($errors)) {

        $contact->fill($contactData);

        if ($contact->save()) {

            redirect('/');
        }
    }
}

include_once __DIR__ . '/../src/partials/header.php';

?>

<body>

<?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>


<!-- Main Page Content -->
<div class="container">

    <?php

    $subtitle = 'Add your contacts here.';

    include_once __DIR__ . '/../src/partials/heading.php';

    ?>


    <div class="row">

        <div class="col-12">

            <form
                method="post"
                enctype="multipart/form-data"
                class="col-md-6 offset-md-3"
            >

                <!-- ==========================
                     NAME
                =========================== -->

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                        maxlength="255"
                        id="name"
                        placeholder="Enter Name"
                        value="<?= html_escape($_POST['name'] ?? '') ?>"
                    >

                    <?php if (isset($errors['name'])): ?>

                        <span class="invalid-feedback">

                            <strong>
                                <?= html_escape($errors['name']) ?>
                            </strong>

                        </span>

                    <?php endif; ?>

                </div>


                <!-- ==========================
                     PHONE
                =========================== -->

                <div class="mb-3">

                    <label
                        for="phone"
                        class="form-label"
                    >
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                        maxlength="255"
                        id="phone"
                        placeholder="Enter Phone"
                        value="<?= html_escape($_POST['phone'] ?? '') ?>"
                    >

                    <?php if (isset($errors['phone'])): ?>

                        <span class="invalid-feedback">

                            <strong>
                                <?= html_escape($errors['phone']) ?>
                            </strong>

                        </span>

                    <?php endif; ?>

                </div>


                <!-- ==========================
                     NOTES
                =========================== -->

                <div class="mb-3">

                    <label
                        for="notes"
                        class="form-label"
                    >
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        maxlength="255"
                        class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>"
                        placeholder="Enter notes (maximum character limit: 255)"
                    ><?= html_escape($_POST['notes'] ?? '') ?></textarea>

                    <?php if (isset($errors['notes'])): ?>

                        <span class="invalid-feedback">

                            <strong>
                                <?= html_escape($errors['notes']) ?>
                            </strong>

                        </span>

                    <?php endif; ?>

                </div>


                <!-- ==========================
                     AVATAR
                =========================== -->

                <div class="mb-3">

                    <label
                        for="avatar"
                        class="form-label"
                    >
                        Avatar
                    </label>

                    <input
                        type="file"
                        name="avatar"
                        id="avatar"
                        class="form-control<?= isset($errors['avatar']) ? ' is-invalid' : '' ?>"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                    >

                    <?php if (isset($errors['avatar'])): ?>

                        <span class="invalid-feedback">

                            <strong>
                                <?= html_escape($errors['avatar']) ?>
                            </strong>

                        </span>

                    <?php endif; ?>

                    <div class="form-text">
                        Chọn ảnh JPG, PNG, GIF hoặc WEBP.
                        Kích thước tối đa 2MB.
                    </div>

                </div>


                <!-- ==========================
                     BUTTON
                =========================== -->

                <button
                    type="submit"
                    name="submit"
                    class="btn btn-primary"
                >
                    Add Contact
                </button>

                <a
                    href="/"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>


<?php include_once __DIR__ . '/../src/partials/footer.php'; ?>

</body>

</html>