<!-- edits: http://fictional-university.local/programs/biology/ -> SO, the programs itself -->
<?php

  get_header();

  while(have_posts()) {
    the_post();
    pageBanner();
     ?>
<div class="container container--narrow page-section">
    <div class="metabox metabox--position-up metabox--with-home-link">
        <p><a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('program'); ?>"><i
                    class="fa fa-home" aria-hidden="true"></i> All Programs</a> <span
                class="metabox__main"><?php the_title(); ?></span></p>
    </div>

    <div class="generic-content"><?php the_field('main_body_content'); ?></div>

    <?php
    // Show the Professors linked to the event
    $relatedProfessors = new WP_Query(
        array(
            'posts_per_page' => -1,     //-1 is all posts (that meet conditions)
            'post_type' => 'professor',
            'orderby' => 'title',       // default = 'post_date', random = 'rand', 'title'
            'order' => 'ASC',            // default = 'DESC'
    
            //Only search for related program events  / meta query is a filter
            'meta_query' => array(
                array(
                'key' => 'related_programs',
                'compare' => 'LIKE',
                'value' => '"' . get_the_ID() . '"',
            )
        )
    ));
    if ($relatedProfessors->have_posts()) {
        echo '<hr class="section-break">';
        echo '<h2 class="headline headline--medium">' . get_the_title() . ' Professor(s)</h2>';
        echo '<ul class="professor-cards">';

        while ($relatedProfessors->have_posts()) {
            $relatedProfessors->the_post(); ?>
            <li class="professor-card__list-item">
                <a class="professor-card" href="<?php the_permalink(); ?>">
                    <img src="<?php the_post_thumbnail_url('professorLandscape'); ?>" class="professor-card__image">
                    <span class="professor-card__name"><?php the_title(); ?></span>
                </a>
            </li>
        <?php }
        echo '</ul>';
    }

    wp_reset_postdata();

    $today = date('Ymd');
    $homepageEvents = new WP_Query(array(
        'posts_per_page' => 2,     //-1 is all posts (that meet conditions)
        'post_type' => 'event',
        'meta_key' => 'event_date', // 'meta_value' needs this line <--  sorts the date
        'orderby' => 'meta_value_num',       // default = 'post_date', random = 'rand', 'title'
        'order' => 'ASC',            // default = 'DESC'
    
        // For Past events - only give us posts if the event date >= then today
        'meta_query' => array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            ),
            //Only search for related program events  / meta query is a filter
            array(
                'key' => 'related_programs',
                'compare' => 'LIKE',
                'value' => '"' . get_the_ID() . '"',
            )
        )
    ));

    if ($homepageEvents->have_posts()) {
        echo '<hr class="section-break">';
        echo '<h2 class="headline headline--medium">Upcoming ' . get_the_title() . ' Event(s)</h2>';

        while ($homepageEvents->have_posts()) {
            $homepageEvents->the_post();
            get_template_part('/template-parts/content', 'event');
        }
    }
    ?>
</div>
    
  <?php }

  get_footer();

?>