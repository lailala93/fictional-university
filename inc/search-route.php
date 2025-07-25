<?php

// Register a custom REST API route at /wp-json/university/v1/search
add_action('rest_api_init', 'universityRegisterSearch');

function universityRegisterSearch()
{
    // Defines the /search endpoint using the GET method
    register_rest_route('university/v1', 'search', array(
        'methods' => WP_REST_SERVER::READABLE, // Same as GET
        'callback' => 'universitySearchResults'
    ));
}

// Callback function that runs when someone accesses the /search endpoint
function universitySearchResults($data)
{
    // WP_Query searches for posts, pages, and custom post types matching the search term
    $mainQuery = new WP_Query(array(
        'post_type' => array('post', 'page', 'professor', 'program', 'event', 'campus'),
        's' => sanitize_text_field($data['term']) // Sanitize the user input
    ));

    // Prepares the results array structure to organize search results by type
    $results = array(
        'generalInfo' => array(),
        'professors' => array(),
        'programs' => array(),
        'events' => array(),
        'campuses' => array(),
    );

    // Loop through each found post and sort them into the correct array bucket
    while ($mainQuery->have_posts()) {
        $mainQuery->the_post();

        // General info: includes both posts and pages
        if (get_post_type() == 'post' or get_post_type() == 'page') {
            array_push($results['generalInfo'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'postType' => get_post_type(),
                'authorName' => get_the_author()
            ));
        }

        // Professors: custom post type with name, link, and image
        if (get_post_type() == 'professor') {
            array_push($results['professors'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
            ));
        }

        // Programs: custom post type with name, link, and ID for relationship searching
        if (get_post_type() == 'program') {
            $relatedCampuses = get_field('related_campus');

            if ($relatedCampuses) {
                foreach ($relatedCampuses as $campus) {
                    array_push($results['campuses'], array(
                        'title' => get_the_title($campus),
                        'permalink' => get_the_permalink($campus),
                    ));
                }
            }

            array_push($results['programs'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'id' => get_the_ID()
            ));
        }

        // Campuses: just title and permalink
        if (get_post_type() == 'campus') {
            array_push($results['campuses'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink()
            ));
        }

        // Events: includes formatted date and a short description
        if (get_post_type() == 'event') {
            $eventDate = new DateTime(get_field('event_date'));
            $description = null;

            if (has_excerpt()) {
                $description = get_the_excerpt();
            } else {
                $description = wp_trim_words(get_the_content(), 18);
            }

            array_push($results['events'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'month' => $eventDate->format('M'),
                'day' => $eventDate->format('d'),
                'description' => $description
            ));
        }
    }

    // If any programs were found, perform an additional query for related professors
    if ($results['programs']) {
        // Meta query for finding professors related to the found programs
        $programsMetaQuery = array('relation' => 'OR');

        // Dynamically build a meta_query based on related_programs field
        foreach ($results['programs'] as $item) {
            array_push($programsMetaQuery, array(
                'key' => 'related_programs',
                'compare' => 'LIKE',
                'value' => '"' . $item['id'] . '"' // Look for program ID in relationship field
            ));
        }

        // Query for professors connected to any of the found programs
        $programRelationshipQuery = new WP_Query(array(
            'post_type' => array('professor', 'event'),
            'meta_query' => $programsMetaQuery
        ));

        // Add these related professors to the result
        while ($programRelationshipQuery->have_posts()) {
            $programRelationshipQuery->the_post();

            if (get_post_type() == 'event') {
                $eventDate = new DateTime(get_field('event_date'));
                $description = null;

                if (has_excerpt()) {
                    $description = get_the_excerpt();
                } else {
                    $description = wp_trim_words(get_the_content(), 18);
                }

                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $eventDate->format('M'),
                    'day' => $eventDate->format('d'),
                    'description' => $description
                ));
            }
            if (get_post_type() == 'professor') {
                array_push($results['professors'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
                ));
            }
        }

        // Remove any duplicates in the results
        $results['professors'] = array_values(array_unique($results['professors'], SORT_REGULAR));
        $results['events'] = array_values(array_unique($results['events'], SORT_REGULAR));

    }

    // Return the full structured result as the API response
    return $results;
}
?>