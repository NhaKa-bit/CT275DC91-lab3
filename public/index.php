<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

// ==========================
// PHÂN TRANG
// ==========================

$limit = 10;

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$contact = new Contact($PDO);

$totalContacts = $contact->count();

$totalPages = (int) ceil($totalContacts / $limit);

if ($totalPages > 0 && $page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;

$contacts = $contact->paginate($offset, $limit);


// ==========================
// HEADER
// ==========================

include_once __DIR__ . '/../src/partials/header.php';

?>

<body>

<?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>


<!-- ==========================
     MAIN CONTENT
========================== -->

<div class="container">

    <?php

    $subtitle = 'View your all contacts here.';

    include_once __DIR__ . '/../src/partials/heading.php';

    ?>


    <!-- ==========================
         ADD CONTACT BUTTON
    =========================== -->

    <div class="mb-3">

        <a
            href="/add.php"
            class="btn btn-primary"
        >
            <i class="bi bi-plus-lg"></i>
            New Contact
        </a>

    </div>


    <!-- ==========================
         CONTACT TABLE
    =========================== -->

    <div class="table-responsive">

        <table class="table table-bordered table-striped align-middle">

            <thead>

                <tr>

                    <th style="width: 100px;">
                        Avatar
                    </th>

                    <th>
                        Name
                    </th>

                    <th>
                        Phone
                    </th>

                    <th>
                        Date Created
                    </th>

                    <th>
                        Notes
                    </th>

                    <th style="width: 230px;">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if (empty($contacts)): ?>

                <tr>

                    <td
                        colspan="6"
                        class="text-center"
                    >
                        No contacts found.
                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($contacts as $contact): ?>

                    <tr>

                        <!-- ==========================
                             AVATAR
                        =========================== -->

                        <td>

                            <?php if (!empty($contact->avatar)): ?>

                                <img
                                    src="/<?= html_escape($contact->avatar) ?>"
                                    alt="Avatar"
                                    width="70"
                                    height="70"
                                    style="
                                        object-fit: cover;
                                        border-radius: 50%;
                                        border: 1px solid #ddd;
                                    "
                                >

                            <?php else: ?>

                                <span class="text-muted">
                                    No avatar
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- ==========================
                             NAME
                        =========================== -->

                        <td>

                            <?= html_escape($contact->name) ?>

                        </td>


                        <!-- ==========================
                             PHONE
                        =========================== -->

                        <td>

                            <?= html_escape($contact->phone) ?>

                        </td>


                        <!-- ==========================
                             DATE CREATED
                        =========================== -->

                        <td>

                            <?= date(
                                'd-m-Y',
                                strtotime($contact->created_at)
                            ) ?>

                        </td>


                        <!-- ==========================
                             NOTES
                        =========================== -->

                        <td>

                            <?= html_escape($contact->notes) ?>

                        </td>


                        <!-- ==========================
                             ACTIONS
                        =========================== -->

                        <td>

                            <a
                                href="/edit.php?id=<?= $contact->id ?>"
                                class="btn btn-warning"
                            >
                                <i class="bi bi-pencil-fill"></i>
                                Edit
                            </a>


                            <form
                                method="post"
                                action="/delete.php"
                                class="d-inline"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $contact->id ?>"
                                >

                                <button
                                    type="submit"
                                    name="delete-contact"
                                    class="btn btn-danger"
                                >
                                    <i class="bi bi-trash-fill"></i>
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>


            <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- ==========================
         PAGINATION
    =========================== -->

    <?php if ($totalPages > 1): ?>

        <nav aria-label="Contact pagination">

            <ul class="pagination justify-content-center">


                <!-- Previous -->

                <li
                    class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"
                >

                    <a
                        class="page-link"
                        href="?page=<?= $page - 1 ?>"
                    >
                        &laquo;
                    </a>

                </li>


                <!-- Page numbers -->

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <li
                        class="page-item <?= $i === $page ? 'active' : '' ?>"
                    >

                        <a
                            class="page-link"
                            href="?page=<?= $i ?>"
                        >
                            <?= $i ?>
                        </a>

                    </li>

                <?php endfor; ?>


                <!-- Next -->

                <li
                    class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"
                >

                    <a
                        class="page-link"
                        href="?page=<?= $page + 1 ?>"
                    >
                        &raquo;
                    </a>

                </li>

            </ul>

        </nav>

    <?php endif; ?>


</div>


<?php include_once __DIR__ . '/../src/partials/footer.php'; ?>


</body>

</html>