<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */  

add_action( 'wp_enqueue_scripts', 'hello_elementor_child_style' );
				function hello_elementor_child_style() {
					wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
					wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
					wp_enqueue_style( 'child-style2', get_stylesheet_directory_uri() . '/style_h.css', array('parent-style') );
	
				}

/**
 * Your code goes below.
 */

include_once get_stylesheet_directory() .'/functions_h_dev.php';


add_filter('gettext', 'change_sportspress_admin_text', 20, 3);
function change_sportspress_admin_text($translated, $original, $domain) {
    if (is_admin()) {
        $translated = str_ireplace('SportsPress', 'NPL Manage League', $translated);
    }
    return $translated;
}

// Remove "Settings" under SportsPress

add_action('admin_menu', 'remove_sportspress_settings_link', 999);
function remove_sportspress_settings_link() {
    remove_submenu_page('sportspress', 'sportspress'); 
}





function show_event_venue_name_shortcode( $atts ) {
    $post_id = get_the_ID();

    $venues = get_the_terms( $post_id, 'sp_venue' );

    // Check if there are venues
    if ( !empty($venues) && !is_wp_error($venues) ) {
        $venue_names = wp_list_pluck( $venues, 'name' );
        $venue_list = implode(', ', $venue_names);

        // Return the venue names with inline styles
        return '<span>' . $venue_list . '</span>';
    }

    return '<span >Venue not set</span>';
}
add_shortcode( 'show_venue', 'show_event_venue_name_shortcode' );

function register_event_taxonomies() {
    register_taxonomy(
        'event_location',
        'sp_event',
        array(
            'label'         => __( 'Event Location' ),
            'rewrite'       => array( 'slug' => 'event-location' ),
            'hierarchical'  => false, // important: not hierarchical = behaves like tags
            'show_ui'       => true,
            'show_in_rest'  => true,
            'meta_box_cb'   => 'post_tags_meta_box', // Forces single select dropdown
        )
    );
}
// add_action( 'sportspress_register_taxonomy', 'register_event_taxonomies' );





// Register Custom Post Type: Newspost (Don't Remove This)
function create_newspost_cpt() {
    register_post_type('newspost',
        array(
            'labels' => array(
                'name' => __('News Posts'),
                'singular_name' => __('News Post'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'newspost'),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-media-document',
        )
    );
}
add_action('init', 'create_newspost_cpt');

// Register Custom Taxonomy: news_category
function create_news_taxonomy() {
    register_taxonomy(
        'news_category',
        'newspost',
        array(
            'label' => __('News Categories'),
            'rewrite' => array('slug' => 'news-category'),
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'create_news_taxonomy');

function display_matches_posts() {
    // Enqueue Slick Slider CSS and JS only when this function is called
    if (!wp_script_is('slick', 'enqueued')) {
        wp_enqueue_style('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
        wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
        wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
    }
    // Query the matches post type
    $args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => -1, // You can set a specific number if you want
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $events_query = new WP_Query($args);

    // Start output buffering
    ob_start();

    if ($events_query->have_posts()) {
        // Get all unique venues
        $venues = get_terms(array(
            'taxonomy' => 'sp_venue',
            'hide_empty' => true
        ));

        echo '<div class="matches-container">';
        echo '<div class="event-filter-wrapper">';
        // Event type filter
        echo '<select id="event-filter">';
        echo '<option value="all">All Events</option>';
        echo '<option value="upcoming">Upcoming</option>';
        echo '<option value="past">Past</option>';
        echo '</select>';

        // Venue filter
        echo '<select id="venue-filter">';
        echo '<option value="all">All Venues</option>';
        if (!empty($venues) && !is_wp_error($venues)) {
            foreach ($venues as $venue) {
                echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
            }
        }
        echo '</select>';
        echo '</div>';
        echo '<div class="events-list events-slider">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $bg_image = '';
            if (has_post_thumbnail()) {
                $bg_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $bg_image = $bg_image[0];
            }
            echo '<a href="' . esc_url(site_url('/tournaments-pre-event-states/')) . '" class="event-link">';
            $event_date = get_field('event_date');
            $current_date = date('Y-m-d');
            
            // Extract the start date from range (e.g., 'Dec 21' from 'Dec 21 - 24')
            if ($event_date) {
                $date_parts = explode('-', $event_date);
                $start_date = trim($date_parts[0]); // e.g., 'Dec 21'
                
                // Convert to full date with current year
                $start_date = date('Y-m-d', strtotime($start_date . ' ' . date('Y')));
                $event_type = (!$start_date || strtotime($start_date) < strtotime($current_date)) ? 'past' : 'upcoming';
            } else {
                $event_type = 'past';
            }
            $venue = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_id = ($venue && !is_wp_error($venue)) ? $venue[0]->term_id : '';
            echo '<div class="single-event" data-event-type="' . esc_attr($event_type) . '" data-venue-id="' . esc_attr($venue_id) . '"' . ($bg_image ? ' style="background-image: url(' . esc_url($bg_image) . '); background-size: cover; background-position: center;"' : '') . '">';
            $event_badge = get_field('event_badge');
            if($event_badge) {
                echo '<span class="event-badge">' . esc_html($event_badge) . '</span>';
            } else {
                echo '<span class="event-badge"> PWR </span>';
            }
            $logo_image = get_field('logo_image');
            if($logo_image && is_array($logo_image)) {
                echo '<img src="' . esc_url($logo_image['url']) . '" class="event-logo" alt="' . esc_attr($logo_image['alt'] ? $logo_image['alt'] : 'Event Logo') . '">';
            } else {
                echo '<img src="' . site_url('/wp-content/uploads/2025/03/npl-leagues.png') . '" class="event-logo" alt="NPL Leagues Logo">';
            }
            echo '<h2>' . get_the_title() . '</h2>';
            $venue = get_the_terms(get_the_ID(), 'sp_venue');
            if ($venue && !is_wp_error($venue)) {
                echo '<span class="event-venue">' . esc_html($venue[0]->name) . '</span>';
            } else {
                echo '<span class="event-venue"></span>';
            }
            echo '</div>';
            echo '<div class="event-date-wrapper">';
            $event_date = get_field('event_date');
            if ($event_date) {
                echo '<span class="event-date">' . esc_html($event_date) . '</span>';
            }
            $format = get_post_meta(get_the_ID(), 'sp_format', true);
            if ($format) {
                if ($format === 'friendly') {
                    echo '<span class="event-format">Friendly</span>';
                } elseif ($format === 'competitive') {
                    echo '<span class="event-format">Competitive</span>';
                } elseif ($format === 'tournament') {
                    echo '<span class="event-format">Tournament</span>';
                }
            }
            echo '</div>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<p class="no-events">No events found.</p>';
    }

    // Reset post data
    wp_reset_postdata();

    echo '</div>'; // Close matches-container

    wp_reset_postdata();
    $content = ob_get_clean();

    // Add JavaScript
    ob_start();
    ?>
    <script>
    jQuery(document).ready(function($) {
        const eventFilter = $('#event-filter');
        const venueFilter = $('#venue-filter');
        let slider = $('.events-slider');
        let allEvents = $('.event-link').clone(); // Store all events for filtering

        function initSlider() {
            if (slider.children().length > 0) {
                slider.slick({
                    dots: false,
                    infinite: true,
                    speed: 800,
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    arrows: false,
                    draggable: true,
                    touchThreshold: 10,
                    swipeToSlide: true,
                    cssEase: 'cubic-bezier(0.87, 0.03, 0.41, 0.9)',
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                });
            }
        }

        function filterEvents() {
            const selectedEventType = eventFilter.val();
            const selectedVenue = venueFilter.val();

            // Destroy the slider if initialized
            if (slider.hasClass('slick-initialized')) {
                slider.slick('unslick');
            }

            // Remove all current events
            slider.empty();
            $('.no-events').remove(); // Remove existing no events message if any

            let matchFound = false;

            // Filter and add matching events
            allEvents.each(function() {
                const event = $(this).find('.single-event');
                const matchesEventType = selectedEventType === 'all' || event.attr('data-event-type') === selectedEventType;
                const matchesVenue = selectedVenue === 'all' || event.attr('data-venue-id') === selectedVenue;

                if (matchesEventType && matchesVenue) {
                    slider.append($(this).clone());
                    matchFound = true;
                }
            });

            if (!matchFound) {
                slider.after('<p class="no-events">No events found.</p>');
            } else {
                // Only initialize slider if events were found
                initSlider();
            }
        }

        // Initialize slider first time
        initSlider();

        // Add event listeners
        eventFilter.on('change', filterEvents);
        venueFilter.on('change', filterEvents);
    });
    </script>
    <?php
    $js = ob_get_clean();
    return $content . $js;
}

// Register the shortcode
add_shortcode('show_matches', 'display_matches_posts');

function display_matches_monthly() {
    // Query the matches post type
    $args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $events_query = new WP_Query($args);

    // Start output buffering
    ob_start();

    if ($events_query->have_posts()) {
        // Get all unique venues
        $venues = get_terms(array(
            'taxonomy' => 'sp_venue',
            'hide_empty' => true
        ));

        echo '<div class="matches-container">';
        echo '<div class="event-filter-wrapper">';
        // Event type filter
        echo '<select id="event-type-filter">';
        echo '<option value="all">All Events</option>';
        echo '<option value="upcoming">Upcoming</option>';
        echo '<option value="past">Past</option>';
        echo '</select>';

        // Venue filter
        echo '<select id="venue-filter-monthly">';
        echo '<option value="all">All Venues</option>';
        if (!empty($venues) && !is_wp_error($venues)) {
            foreach ($venues as $venue) {
                echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
            }
        }
        echo '</select>';
        echo '</div>';

        // Current month events section
        $current_month = date('m');
        $current_year = date('Y');
        
        echo '<div class="current-month-events">';
        echo '<div class="event-month">';
        echo '<span>' . date('F') . '</span>';
        echo '<span class="year">' . date('Y') . '</span>';
        echo '</div>';
        echo '<div class="month-events-list">';
        
        $has_current_events = false;
        
        // Loop through events for current month
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $event_date = get_field('event_date');
            
            if ($event_date) {
                $date_parts = explode('-', $event_date);
                $start_date = trim($date_parts[0]); // e.g., 'Apr 29'
                
                // Convert to timestamp with current year
                $event_timestamp = strtotime($start_date . ' ' . date('Y'));
                $event_month = date('m', $event_timestamp);
                $event_year = date('Y', $event_timestamp);
                
                if ($event_month == $current_month && $event_year == $current_year) {
                    $has_current_events = true;
                    $current_date = date('Y-m-d');
                    $event_type = (strtotime($start_date) < strtotime($current_date)) ? 'past' : 'upcoming';
                    $venue = get_the_terms(get_the_ID(), 'sp_venue');
                    $venue_id = $venue ? $venue[0]->term_id : '';
                    
                    echo '<div class="month-event-item" data-event-type="' . esc_attr($event_type) . '" data-venue-id="' . esc_attr($venue_id) . '">';
                    echo '<div class="month-event-details">';
                    echo '<div class="event-info">';
                    echo '<span class="event-title">' . get_the_title() . '</span>';
                    if ($venue && !is_wp_error($venue)) {
                        echo '<span class="month-event-venue">' . esc_html($venue[0]->name) . '</span>';
                    }
                    echo '</div>';
                    $logo_image = get_field('logo_image');
                    if($logo_image && is_array($logo_image)) {
                        echo '<img src="' . esc_url($logo_image['url']) . '" class="event-logo" alt="' . esc_attr($logo_image['alt'] ? $logo_image['alt'] : 'Event Logo') . '">';
                    } else {
                        echo '<img src="' . site_url('/wp-content/uploads/2025/03/npl-leagues.png') . '" class="event-logo" alt="NPL Leagues Logo">';
                    }
                    echo '</div>';
                    echo '<div class="month-event-dates">';
                    echo '<div class="dates-info">';
                    $format = get_post_meta(get_the_ID(), 'sp_format', true);
                    if ($format) {
                        if ($format === 'friendly') {
                            echo '<span class="event-format">Friendly</span>';
                        } elseif ($format === 'competitive') {
                            echo '<span class="event-format">Competitive</span>';
                        } elseif ($format === 'tournament') {
                            echo '<span class="event-format">Tournament</span>';
                        } 
                    }
                    echo '<span class="event-date">' . esc_html($event_date) . '</span>';
                    echo '</div>';
                    $event_badge = get_field('event_badge');
                    if($event_badge) {
                        echo '<img src="https://npl.avantechdev.com.au/wp-content/uploads/2025/04/gold-img.png" alt="Gold Badge">';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            }
        }
        
        if (!$has_current_events) {
            echo '<p class="no-events">No events this month.</p>';
        }
        
        echo '</div>'; // Close month-events-list
        echo '</div>'; // Close current-month-events
        echo '</div>'; // Close matches-container
    }

    wp_reset_postdata();
    $content = ob_get_clean();

    // Add JavaScript
    ob_start();
    ?>
    <script>
    jQuery(document).ready(function($) {
        const eventFilter = $('#event-type-filter');
        const venueFilter = $('#venue-filter-monthly');

        function filterEvents() {
            const selectedEventType = eventFilter.val();
            const selectedVenue = venueFilter.val();
            $('.no-events').remove(); // Remove existing no events message
            let monthMatchFound = false;

            // Filter current month events
            $('.month-event-item').each(function() {
                const matchesEventType = selectedEventType === 'all' || $(this).attr('data-event-type') === selectedEventType;
                const matchesVenue = selectedVenue === 'all' || $(this).attr('data-venue-id') === selectedVenue;
                
                if (matchesEventType && matchesVenue) {
                    $(this).show();
                    monthMatchFound = true;
                } else {
                    $(this).hide();
                }
            });

            if (!monthMatchFound) {
                $('.month-events-list').append('<p class="no-events">No events found for the selected filters.</p>');
            }
        }

        // Add event listeners
        eventFilter.on('change', filterEvents);
        venueFilter.on('change', filterEvents);
    });
    </script>
    <?php
    $js = ob_get_clean();
    return $content . $js;
}

// Register the shortcode
add_shortcode('monthly_events', 'display_matches_monthly');



// News page listing (Ghani) 

function func_news_page_listing($atts) {
    ob_start();

    // Extract shortcode attributes and handle multiple categories
    $atts = shortcode_atts(array(
        'categories' => '', // Comma-separated category IDs
        'category_ids' => '', // Legacy support
    ), $atts);

    // Get category IDs from shortcode attributes
    $shortcode_categories = array();
    if (!empty($atts['categories'])) {
        $shortcode_categories = array_map('intval', explode(',', $atts['categories']));
    } elseif (!empty($atts['category_ids'])) { // Legacy support
        $shortcode_categories = array_map('intval', explode(',', $atts['category_ids']));
    }
    
    // Get categories specified in shortcode for buttons
    $categories = array();
    if (!empty($shortcode_categories)) {
        $categories = get_terms(array(
            'taxonomy' => 'news_category',
            'hide_empty' => false,
            'include' => $shortcode_categories
        ));
        
        if (is_wp_error($categories)) {
            $categories = array();
        }
    }

    // Get all for dropdown
    $all_categories = get_terms(array(
        'taxonomy' => 'news_category',
        'hide_empty' => false
    ));
    
    if (is_wp_error($all_categories)) {
        $all_categories = array();
    }

    // Display filters
    echo '<div class="news-filters">';
    echo '<h1>News</h1>';
    echo '<div class="custom-dropdown">';
    echo '<div class="dropdown-name"> Filter ';
    echo '<span class="dropdown-header">All</span>';
    echo '</div>';
    echo '<div class="dropdown-content">';
    echo '<div class="category-search">';
    echo '<input type="text" class="category-search-input" placeholder="Search categories...">';
    echo '</div>';
    echo '<div class="dropdown-items">';
    echo '<div class="dropdown-item selected" data-category-id="0">All</div>';
    foreach ($all_categories as $category) {
        echo '<div class="dropdown-item" data-category-id="' . $category->term_id . '">' . $category->name . '</div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>'; // Close news-filters

    // Display category buttons
    echo '<div class="news-category-filters">';
    // All posts button
    echo '<button type="button" class="category-button active" data-category-id="0">All</button>';
    
    // Category buttons - using filtered categories from shortcode
    if (!empty($categories)) {
        foreach ($categories as $category) {
            echo '<button type="button" class="category-button" '
                 . 'data-category-id="' . esc_attr($category->term_id) . '">' 
                 . esc_html($category->name) . '</button>';
        }
    }
    echo '</div>';

    
    // Add loader after buttons
    echo '<div class="loader"></div>';

    // Add container for AJAX-loaded content
    echo '<div id="news-content">';

    // Initial query arguments
    $args = array(
        'post_type' => 'newspost',
        'posts_per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    // If shortcode has categories, show posts from those categories initially
    if (!empty($shortcode_categories)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'news_category',
                'field' => 'term_id',
                'terms' => $shortcode_categories,
            ),
        );
    }

    echo '<h2 class="category-heading" style="display: none;">All</h2>';
    echo '<div class="news-grid" style="display: none;">';
    echo '<div class="loader" style="display: none;"></div>';
    $query = new WP_Query($args);
    render_news_posts($query, $shortcode_categories);
    echo '</div>';

    echo '</div>'; // Close news-content div

    // Initialize JavaScript
    ?>
    <script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var newsNonce = '<?php echo wp_create_nonce('news_filter_nonce'); ?>';
    
    jQuery(document).ready(function($) {
        var shortcodeCategories = <?php echo json_encode($shortcode_categories); ?>;
        var selectedCategory = 0;
        var searchTimer;

        // Toggle dropdown
        $('.dropdown-name').on('click', function() {
            $('.dropdown-content').toggleClass('active');
            $('.dropdown-arrow').toggleClass('up');
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-dropdown').length) {
                $('.dropdown-content').removeClass('active');
                $('.dropdown-arrow').removeClass('up');
            }
        });

        // Handle dropdown item click
        $('.dropdown-item').on('click', function() {
            var categoryId = parseInt($(this).data('category-id'));
            var categoryName = $(this).text();
            selectedCategory = categoryId;
            
            // Update dropdown text and close dropdown
            $('.dropdown-header').text(categoryName);
            $('.dropdown-content').removeClass('active');
            $('.dropdown-arrow').removeClass('up');

            // Reset all buttons' active state
            $('.category-button').removeClass('active');
            
            // If All is selected
            if (categoryId === 0) {
                $('.category-button[data-category-id="0"]').addClass('active');
            } else {
                // Check if there's a matching button
                var matchingButton = $('.category-button[data-category-id="' + categoryId + '"]');
                if (matchingButton.length) {
                    matchingButton.addClass('active');
                }
            }

            loadPosts(categoryId, $('.news-search-input').val(), true);
        });

        // Handle category button click
        $(document).on('click', '.category-button', function() {
            var categoryId = parseInt($(this).data('category-id'));
            var categoryName = $(this).text();
            selectedCategory = categoryId;
            
            // Update button states
            $('.category-button').removeClass('active');
            $(this).addClass('active');
            
            // Update dropdown text
            if (categoryId > 0) {
                $('.dropdown-header').text(categoryName);
            } else {
                $('.dropdown-header').text('All');
            }
            
            // Load posts with button context
            loadPosts(categoryId, $('.news-search-input').val(), false);
        });

        // Handle category search
        $('.category-search-input').on('input', function() {
            var searchQuery = $(this).val().toLowerCase();
            $('.dropdown-item').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchQuery));
            });
        });



        function loadPosts(categoryId, searchQuery, fromDropdown = false) {
            // Update heading
            var categoryName = 'All';
            if (categoryId > 0) {
                categoryName = $('.dropdown-item[data-category-id="' + categoryId + '"]').text() || 
                             $('.category-button[data-category-id="' + categoryId + '"]').text();
            }
            $('.category-heading').text(categoryName);

            // Hide news grid and heading, then show loader
            $('.news-grid, .category-heading').fadeOut(200, function() {
                $('.loader').fadeIn(200);
            });
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_category_posts',
                    category: categoryId,
                    search: searchQuery,
                    shortcode_categories: shortcodeCategories,
                    from_dropdown: fromDropdown,
                    nonce: newsNonce
                },
                success: function(response) {
                    // Hide loader and show new posts with fade
                    $('.loader').fadeOut(200, function() {
                        $('.news-grid').html(response).fadeIn(300);
                        $('.category-heading').fadeIn(300);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    // Hide loader and show error
                    $('.loader').hide();
                    $('.news-grid').show();
                }
            });
        }

        // Initialize variables
        var searchTimer = null;
        var categorySearchTimer = null;
        var selectedCategory = 0;

        // Show loader first
        $('.loader').fadeIn(200, function() {
            // Then load initial posts
            loadPosts(0, '', false);
        });
    });
    </script>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('news_page_listing', 'func_news_page_listing');

// AJAX handler for loading posts
// Helper function to render news posts
function render_news_posts($query, $shortcode_categories = array()) {
    if ($query->have_posts()) {
        $count = 0;

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $categories = get_the_terms($post_id, 'news_category');
            $category_names = array();

            if ($categories && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                }
            }

            echo '<div class="news-item' . ($count < 2 ? ' featured' : '') . '">';
            echo '<a href="' . get_permalink() . '" class="news-link">';
            if ($count < 6 && has_post_thumbnail()) {
                echo '<div class="news-image">';
                echo get_the_post_thumbnail(null, 'full');
                echo '</div>';
            }
            echo '<div class="news-content">';
            echo '<div class="news-info">';
            echo '<span class="news-date">' . get_the_date('F j, Y') . '</span>';
            echo '<h3>' . get_the_title() . '</h3>';
            echo '</div>';
            if (!empty($category_names)) {
                echo '<span class="news-categories">' . implode(', ', $category_names) . '</span>';
            }
            echo '</div>';
            echo '</a>'; // Close news-content
            echo '</div>'; // Close news-item

            $count++;
        }
    } else {
        echo '<div class="no-posts-message">No news found in this category.</div>';
    }
    
    wp_reset_postdata();
}

// AJAX handler for loading posts
function load_category_posts() {
    check_ajax_referer('news_filter_nonce', 'nonce');
    
    $category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $shortcode_categories = isset($_POST['shortcode_categories']) ? array_map('intval', $_POST['shortcode_categories']) : array();
    $from_dropdown = isset($_POST['from_dropdown']) ? filter_var($_POST['from_dropdown'], FILTER_VALIDATE_BOOLEAN) : false;

    $args = array(
        'post_type' => 'newspost',
        'posts_per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC',
        'suppress_filters' => false,
    );

    // Apply search query if exists
    if (!empty($search_query)) {
        $args['s'] = $search_query;
    }

    // Add category filter
    if ($from_dropdown) {
        // For dropdown selection
        if ($category_id > 0) {
            // Show posts from selected category
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'news_category',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ),
            );
        }
    } else {
        // For button clicks
        if (!empty($shortcode_categories)) {
            if ($category_id > 0) {
                // Show posts from specific category if it's in shortcode
                if (in_array($category_id, $shortcode_categories)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'news_category',
                            'field' => 'term_id',
                            'terms' => $category_id,
                        ),
                    );
                } else {
                    // Category not in shortcode, return no posts
                    $args['post__in'] = array(0);
                }
            } else {
                // All button - show posts from all shortcode categories
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'news_category',
                        'field' => 'term_id',
                        'terms' => $shortcode_categories,
                        'operator' => 'IN',
                    ),
                );
            }
        }
    }

    $query = new WP_Query($args);
    render_news_posts($query, $shortcode_categories);
    wp_die();
}
add_action('wp_ajax_load_category_posts', 'load_category_posts');
add_action('wp_ajax_nopriv_load_category_posts', 'load_category_posts');

// News Widget Function
function func_news_widget() {
    ob_start();

    // Get all categories, including empty ones
    $categories = get_terms(array(
        'taxonomy' => 'news_category',
        'hide_empty' => false
    ));

    echo '<div class="news-widget">';
    echo '<h3 class="main-heading">News</h3>';
    echo '<a href="/news" class="main-heading-link">News <img src="/wp-content/uploads/2025/05/chevron-right.svg"></a>';
    
    // Category Navigation
    echo '<div class="news-nav">';
    echo '<button class="news-nav-arrow prev" aria-label="Previous categories"><img src="/wp-content/uploads/2025/05/chevron-right.svg"></button>';
    echo '<div class="news-nav-scroll">';
    echo '<a href="#" class="news-nav-item active" data-category="0">All</a>';
    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            echo '<a href="#" class="news-nav-item" data-category="' . $category->term_id . '">' . $category->name . '</a>';
        }
    }
    echo '</div>';
    echo '<button class="news-nav-arrow next" aria-label="Next categories"><img src="/wp-content/uploads/2025/05/chevron-right.svg"></button>';
    echo '</div>';

    // News Content Area
    echo '<div class="news-widget-content">';
    echo '<h2 class="news-widget-heading">All</h2>';
    echo '<div class="news-widget-loader" style="display: none;"></div>';
    echo '<div class="news-widget-grid">';
    
    // Initial query for latest posts
    $args = array(
        'post_type' => 'newspost',
        'posts_per_page' => 4,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        render_news_widget_posts($query);
    } else {
        echo '<div class="no-posts-message">No news found</div>';
    }
    wp_reset_postdata();
    echo '</div>'; // Close news-widget-grid
    echo '<div class="news-widget-footer">';
    echo '<a href="/news" class="news-widget-button">View All News</a>';
    echo '<div class="sponser"><span>Sponsored by:</span><img src="/wp-content/uploads/2025/05/ahm.svg"></div>';
    echo '</div>'; // Close news-widget-footer
    echo '</div>'; // Close news-widget-content
    echo '</div>'; // Close news-widget

    // Add JavaScript
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Initial load handling
        $('.news-widget-loader').fadeIn(200);
        $('.news-widget-grid').hide();
        
        // After a short delay, hide loader and show content
        setTimeout(function() {
            $('.news-widget-loader').fadeOut(200, function() {
                $('.news-widget-grid').fadeIn(300);
            });
        }, 300);
        // Handle navigation arrows
        $('.news-nav-arrow').on('click', function() {
            var $scroll = $(this).siblings('.news-nav-scroll');
            var scrollAmount = 200;
            
            if ($(this).hasClass('prev')) {
                $scroll.animate({ scrollLeft: '-=' + scrollAmount }, 300);
            } else {
                $scroll.animate({ scrollLeft: '+=' + scrollAmount }, 300);
            }
        });

        // Update arrow visibility based on scroll position
        $('.news-nav-scroll').each(function() {
            var $scroll = $(this);
            var $nav = $scroll.closest('.news-nav');
            
            function updateArrows() {
                var scrollLeft = $scroll.scrollLeft();
                var scrollWidth = $scroll[0].scrollWidth;
                var visibleWidth = $scroll.width();
                
                $nav.find('.prev').toggleClass('hidden', scrollLeft <= 0);
                $nav.find('.next').toggleClass('hidden', Math.ceil(scrollLeft + visibleWidth) >= scrollWidth);
                
                // Force recalculation after a short delay
                setTimeout(function() {
                    var scrollLeft = $scroll.scrollLeft();
                    var scrollWidth = $scroll[0].scrollWidth;
                    var visibleWidth = $scroll.width();
                    $nav.find('.next').toggleClass('hidden', Math.ceil(scrollLeft + visibleWidth) >= scrollWidth);
                }, 100);
            }
            
            $scroll.on('scroll', updateArrows);
            $(window).on('resize', updateArrows); // Update on window resize
            setTimeout(updateArrows, 100); // Initial check with delay
        });

        // Handle category click
        $('.news-nav-item').on('click', function(e) {
            e.preventDefault();
            var categoryId = $(this).data('category');
            
            // Update active state and heading
            $('.news-nav-item').removeClass('active');
            $(this).addClass('active');
            var categoryName = $(this).text();
            $(this).closest('.news-widget').find('.news-widget-heading').text(categoryName);
            
            // Show loader and hide content
            var $targetWidget = $(this).closest('.news-widget').find('.news-widget-grid');
            var $targetLoader = $(this).closest('.news-widget').find('.news-widget-loader');
            
            $targetWidget.fadeOut(200, function() {
                $targetLoader.fadeIn(200);
            });
            
            // Load posts via AJAX
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'load_widget_posts',
                    category: categoryId,
                    nonce: '<?php echo wp_create_nonce('news_widget_nonce'); ?>'
                },
                success: function(response) {
                    $targetLoader.fadeOut(200, function() {
                        $targetWidget.html(response).fadeIn(300);
                    });
                }
            });
        });
    });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('news_widget', 'func_news_widget');

// Helper function to render news widget posts
function render_news_widget_posts($query) {
    while ($query->have_posts()) {
        $query->the_post();
        echo '<div class="news-widget-item">';
        echo '<a href="' . get_permalink() . '" class="news-widget-link">';
        echo '<div class="news-widget-info">';
        echo '<span class="news-widget-date">' . get_the_date('F j, Y') . '</span>';
        echo '<h3>' . get_the_title() . '</h3>';
        // Get post categories
        $categories = get_the_terms(get_the_ID(), 'news_category');
        if ($categories && !is_wp_error($categories)) {
            echo '<span class="news-widget-category">' . esc_html($categories[0]->name) . '</span>';
        }
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
}

// AJAX handler for widget posts
function load_widget_posts() {
    check_ajax_referer('news_widget_nonce', 'nonce');
    
    $category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
    
    $args = array(
        'post_type' => 'newspost',
        'posts_per_page' => 4,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish'
    );
    
    if ($category_id > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'news_category',
                'field' => 'term_id',
                'terms' => $category_id
            )
        );
    }
    
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        render_news_widget_posts($query);
    } else {
        echo '<div class="no-posts-message">No news found</div>';
    }
    wp_reset_postdata();
    
    die();
}
add_action('wp_ajax_load_widget_posts', 'load_widget_posts');
add_action('wp_ajax_nopriv_load_widget_posts', 'load_widget_posts');


