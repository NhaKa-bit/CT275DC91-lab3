<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$errors = [];

/*
 * ==========================
 * LẤY ID CONTACT
 * ==========================
 */

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    redirect('/');
}


/*
 * ==========================
 * TÌM CONTACT
 * ==========================
 */

$contact = new Contact($PDO);

if (!$contact->find($id)) {
    redirect('/');
}


/*
 * ==========================
 * XỬ LÝ FORM POST
 * ==========================
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
     * ==========================
     * LẤY DỮ LIỆU FORM
     * ==========================
     */

    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',

        // Giữ avatar cũ nếu không chọn ảnh mới
        'avatar' => $contact->avatar ?? ''
    ];


    /*
     * ==========================
     * VALIDATE
     * ==========================
     */

    $errors = $contact->validate($contactData);


    /*
     * ==========================
     * UPLOAD AVATAR MỚI
     * ==========================
     */

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


            /*
             * ==========================
             * KIỂM TRA DUNG LƯỢNG
             * ==========================
             */

            if ($fileSize > 2 * 1024 * 1024) {

                $errors['avatar'] =
                    'Avatar must not exceed 2MB.';

            } else {

                /*
                 * ==========================
                 * KIỂM TRA FILE THẬT
                 * ==========================
                 */

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

                    /*
                     * ==========================
                     * LẤY EXTENSION
                     * ==========================
                     */

                    $extension = strtolower(
                        pathinfo(
                            $_FILES['avatar']['name'],
                            PATHINFO_EXTENSION
                        )
                    );


                    /*
                     * ==========================
                     * TẠO TÊN FILE MỚI
                     * ==========================
                     */

                    $fileName =
                        uniqid('avatar_', true)
                        . '.'
                        . $extension;


                    /*
                     * ==========================
                     * THƯ MỤC UPLOAD
                     * ==========================
                     */

                    $uploadDir = __DIR__ . '/uploads/';

                    if (!is_dir($uploadDir)) {

                        mkdir(
                            $uploadDir,
                            0777,
                            true
                        );
                    }


                    /*
                     * ==========================
                     * ĐƯỜNG DẪN FILE
                     * ==========================
                     */

                    $destination =
                        $uploadDir . $fileName;


                    /*
                     * ==========================
                     * DI CHUYỂN FILE
                     * ==========================
                     */

                    if (
                        move_uploaded_file(
                            $tmpName,
                            $destination
                        )
                    ) {

                        /*
                         * Lưu đường dẫn mới
                         * vào database
                         */

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


    /*
     * ==========================
     * CẬP NHẬT CONTACT
     * ==========================
     */

    if (empty($errors)) {

        $contact->fill($contactData);

        if ($contact->save()) {

            redirect('/');
        }
    }
}


/*
 * ==========================
 * HEADER
 * ==========================
 */

include_once __DIR__ . '/../src/partials/header.php';

?>

<body>

<?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>


<!-- ==========================
     MAIN CONTENT
========================== -->

<div class="container">

    <?php

    $subtitle = 'Edit your contact here.';

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
                        id="name"
                        maxlength="255"
                        placeholder="Enter Name"
                        class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                        value="<?= html_escape($_POST['name'] ?? $contact->name) ?>"
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
                        id="phone"
                        maxlength="255"
                        placeholder="Enter Phone"
                        class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                        value="<?= html_escape($_POST['phone'] ?? $contact->phone) ?>"
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
                    ><?= html_escape($_POST['notes'] ?? $contact->notes) ?></textarea>

                    <?php if (isset($errors['notes'])): ?>

                        <span class="invalid-feedback">

                            <strong>
                                <?= html_escape($errors['notes']) ?>
                            </strong>

                        </span>

                    <?php endif; ?>

                </div>


                <!-- ==========================
                     AVATAR HIỆN TẠI
                =========================== -->

                <div class="mb-3">

                    <label class="form-label">
                        Current Avatar
                    </label>

                    <?php if (!empty($contact->avatar)): ?>

                        <div class="mb-3">

                            <img
                                src="/<?= html_escape($contact->avatar) ?>"
                                alt="Current Avatar"
                                width="120"
                                height="120"
                                style="
                                    object-fit: cover;
                                    border-radius: 50%;
                                    border: 1px solid #ddd;
                                "
                            >

                        </div>

                    <?php else: ?>

                        <p class="text-muted">
                            This contact does not have an avatar.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- ==========================
                     AVATAR MỚI
                =========================== -->

                <div class="mb-3">

                    <label
                        for="avatar"
                        class="form-label"
                    >
                        Change Avatar
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
                        Không chọn ảnh nếu muốn giữ avatar hiện tại.
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
                    Update Contact
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