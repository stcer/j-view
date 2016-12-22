<?php
/**
 * @var j\view\View $this
 */
$this->beginPage();
?>

<?php $this->beginBody(); ?>

<?php $this->block('main'); ?>
    <h2>Hello world</h2>
<?php $this->endBlock(); ?>

<?php $this->endBody();?>
<?php $this->endPage(); ?>