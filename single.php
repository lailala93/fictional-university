<!-- This controls a [single] individual POST [ if you click a post IN -> Blog ]-->
<?php

get_header();
pageBanner(); 
?>

  <div class="container container--narrow page-section">
    <div class="metabox metabox--position-up metabox--with-home-link">
      <p><a class="metabox__blog-home-link" href="<?php echo site_url('/blog'); ?>">
        <i class="fa fa-home" aria-hidden="true"></i> Blog Home</a><span
          class="metabox__main">Posted by <?php the_author_posts_link(); ?> on <?php the_time('n.j.y'); ?> in <?php echo get_the_category_list(', '); ?>
</span></p>
    

        </div>

    <div class="generic-content">

      <?php the_content(); ?>

    </div>
  </div>
  <!-- <h2><?php the_title(); ?></h2> -->


<?php 

get_footer();

?>