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
				}

/**
 * Your code goes below.
 */

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

function func_widget()
{
    if (function_exists('register_sidebar'))
        register_sidebar(array(
            'name' => 'Team Leader Sidebar',
			'id'            => 'team-leader-sidebar', 
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div></div>',
            'before_title' => '<h3>',
            'after_title' => '</h3><div class="padder">'
        ));
}
add_action('init', 'func_widget');

function home_leader_board($atts)
{
    ob_start();

    // Handle shortcode attributes
    $atts = shortcode_atts(array(
        'id'     => '',
        'league' => '',
        'season' => '',
        'sponsor' => '',
    ), $atts);

    // DEBUG (optional)
    // echo '<pre><strong>Shortcode Inputs:</strong>';
    // print_r($atts);
    // echo '</pre>';

    // Only fetch post by ID (no taxonomy filtering now)
    $args = array(
        'post_type' => 'sp_table',
        'posts_per_page' => -1,
    );

    // If ID provided, filter by that specific table
    if (!empty($atts['id'])) {
        $args['p'] = intval($atts['id']);
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $teams = [];

        while ($query->have_posts()) {
            $query->the_post();
            $post_id  = get_the_ID();
            $sp_teams = get_post_meta($post_id, 'sp_teams', true);

            if (is_array($sp_teams)) {
                foreach ($sp_teams as $team_id => $data) {
                    $team_name = !empty($data['name']) ? $data['name'] : get_the_title($team_id);
                    $logo_url  = get_the_post_thumbnail_url($team_id, 'thumbnail');
                    $logo_img  = $logo_url ? '<img src="' . esc_url($logo_url) . '" width="50" />' : '—';

                    $teams[] = array(
                        'logo'   => $logo_img,
                        'name'   => $team_name,
                        'wins'   => $data['wins'] ?? 0,
                        'losses' => $data['losses'] ?? 0,
                        'winpct' => $data['winpct'] ?? 0,
                        'points' => (int)($data['points'] ?? 0),
                    );
                }
            }
        }

        // Sort by points descending
        usort($teams, fn($a, $b) => $b['points'] - $a['points']);
        $top_teams = array_slice($teams, 0, 10);

        // Header with user-defined labels
        echo '<div class="league-season-header">';
        echo '<span class="sponsor-name">' . (!empty($atts['sponsor']) ? esc_html($atts['sponsor']) : 'Sponsor') . '</span>';
        echo '<span class="league-name">' . (!empty($atts['league']) ? esc_html($atts['league']) : 'League') . '</span>';
        echo '<span class="season-name">' . (!empty($atts['season']) ? esc_html($atts['season']) : 'Season') . '</span>';
        echo '</div>';

        // Leaderboard layout
        echo '<div class="leaderboard-sidebar">
                <div class="leaderboard-nav">
                    <div class="leaderboard-nav-item active">Leaderboard</div>
                </div>';

        $rank_labels_desktop = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th'];
        $rank_labels_mobile = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

		echo '<div class="team_leaderboard_container_main">';
		echo '<div class="team_leaderboard_container_main_inner">';
        foreach ($top_teams as $index => $data) {
            echo '<div class="team_leaderboard_container">
                    <div class="team_leaderboard_list">
                        <div class="teamleader_img">' . $data['logo'] . '</div>
                        <div class="teamleader_text">
                            <h3>' . esc_html($data['name']) . '</h3>
                            <ul>
                                <li><b>W</b> ' . esc_html($data['wins']) . '</li>
                                <li><b>L</b> ' . esc_html($data['losses']) . '</li>
                                <li><b>P</b> ' . esc_html($data['points']) . '</li>
                            </ul>
                        </div>
                    </div>
                    <div class="rank_position">
                        <span class="rank_labels_desktop">' . $rank_labels_desktop[$index] . '</span>
                        <span class="rank_labels_mobile">' . $rank_labels_mobile[$index] . '</span>
                    </div>
                </div>';
        }
		echo "</div>";
		echo "</div>";

        echo '<button class="leaderboard-btn">View Full Leaderboard</button>
              <div class="leaderboard-sponsor">
                  <p>Sponsored by:</p>
                  <img src="' . site_url('/wp-content/uploads/2025/03/partner-court.png') . '" alt="Sponsor Logo">
              </div>
              <button class="leaderboard-live-btn">Watch Live</button>
            </div>';
    } else {
        echo '<p>No teams found for the selected ID.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('leader_board', 'home_leader_board');


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
            echo '<a href="' . esc_url(get_permalink()) . '" class="event-link">';
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
