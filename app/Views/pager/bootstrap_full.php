<?php
/**
 * @var CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav aria-label="Pagination">
    <ul class="pagination">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>">&laquo; Prev</a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo; Prev</span>
            </li>
        <?php endif; ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
            </li>
        <?php endforeach; ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>">Next &raquo;</a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link">Next &raquo;</span>
            </li>
        <?php endif; ?>
    </ul>
</nav>
