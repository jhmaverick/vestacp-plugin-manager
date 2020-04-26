<?php if (!class_exists('Vesta')) exit; ?>
<div class="l-center units vesta-plugins-add">
    <form action="index.php" method="post">
        <h1>Install Plugin</h1>

        <p class="vst-text"><b><?= __("Github repository") ?></b></p>
        <input type="text" class="vst-input" name="plugin-url" required/>
        <br><br>

        <input type="hidden" name="action" value="install"/>
        <button class="button confirm" type="submit"><?= __("Install") ?></button>
        <button class="button cancel" type="button"
                onclick="location.href='/plugin-manager/'"><?= __('Back') ?></button>
    </form>
</div>
