<form class="search-form" method="get" action="<?php echo esc_url(site_url('/')); ?>">
    <label class="headline headline--medium" for="s">Perform a New Search:</label>
    <!-- Need name="theField" for wordpress search -->
    <div class="search-form-row">
        <input placeholder="What are you looking for?" class="s" type="search" id="s" name="s" />
        <input class="search-submit" type="submit" value="search">
    </div>
</form>