<?php $this->layout('template', ['title' => 'Home Page']) ?>

<h1>Home Page</h1>

<?php foreach ($postsInView as $result):?>
<?php echo '<h3>' . $result['id'] . " " . $result['title'] . '</h3>';?>
<?php endforeach;?>

<!--Вывод постов с пагинацией-->
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <?php if ($paginator->getPrevUrl()): ?>
            <li class="page-item"><a class="page-link" href="<?php echo $paginator->getPrevUrl(); ?>">&laquo; Предыдущая</a></li>
        <?php endif; ?>

        <?php foreach ($paginator->getPages() as $page): ?>
            <?php if ($page['url']): ?>
                <li <?php echo $page['isCurrent'] ? 'class="active"' : ''; ?>>
                    <a class="page-link" href="<?php echo $page['url']; ?>"><?php echo $page['num']; ?></a>
                </li>
            <?php else: ?>
                <li class="disabled"><span><?php echo $page['num']; ?></span></li>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($paginator->getNextUrl()): ?>
            <li class="page-item"><a class="page-link" href="<?php echo $paginator->getNextUrl(); ?>">Следующая &raquo;</a></li>
        <?php endif; ?>
    </ul>

    <p class="lead">
        <?php echo $paginator->getTotalItems(); ?> найдено.

        Выводим
        <?php echo $paginator->getCurrentPageFirstItem(); ?>
        -
        <?php echo $paginator->getCurrentPageLastItem(); ?>.
    </p>
</nav>

