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


function func_header_tags()
{
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<?php
}
add_action('wp_head', 'func_header_tags');


function footer_script()
{
?>
    <script>
        const tabs = document.querySelectorAll(".tabs");
        const tab = document.querySelectorAll(".tab");
        const panel = document.querySelectorAll(".panel");

        function onTabClick(event) {

            // deactivate existing active tabs and panel

            for (let i = 0; i < tab.length; i++) {
                tab[i].classList.remove("active");
            }

            for (let i = 0; i < panel.length; i++) {
                panel[i].classList.remove("active");
            }


            // activate new tabs and panel
            event.target.classList.add('active');
            let classString = event.target.getAttribute('data-target');
            console.log(classString);
            document.getElementById('panels').getElementsByClassName(classString)[0].classList.add("active");
        }

        for (let i = 0; i < tab.length; i++) {
            tab[i].addEventListener('click', onTabClick, false);
        }

        const accordion = document.getElementsByClassName('schedule_round_results_container');
        for (i = 0; i < accordion.length; i++) {
            accordion[i].addEventListener('click', function() {
                this.classList.toggle('active')
            })
        }

        function filterTeams(state, event) {
            const teams = document.querySelectorAll('.team_container'); // Match PHP class names
            teams.forEach(team => {
                if (state === 'all' || team.classList.contains(`team-${state}`)) {
                    team.style.display = 'block';
                } else {
                    team.style.display = 'none';
                }
            });

            // Highlight the active tab
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));
            if (event) {
                event.target.classList.add('active');
            }
        }

        // 
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".accordion-button");
            buttons.forEach(button => {
                button.addEventListener("click", function() {
                    const target = document.querySelector(this.getAttribute("data-target"));
                    const isActive = target.classList.contains("active");

                    // Close all open accordions
                    document.querySelectorAll(".accordion-content").forEach(content => {
                        content.style.maxHeight = null;
                        content.classList.remove("active");
                    });
                    document.querySelectorAll(".accordion-button").forEach(btn => {
                        btn.classList.remove("open");
                        btn.querySelector(".chevron").classList.remove("up");
                        btn.querySelector(".chevron").classList.add("down");
                    });

                    // Toggle the clicked accordion
                    if (!isActive) {
                        target.style.maxHeight = target.scrollHeight + "px";
                        target.classList.add("active");
                        this.classList.add("open");
                        this.querySelector(".chevron").classList.remove("down");
                        this.querySelector(".chevron").classList.add("up");
                    }
                });

                // Initialize max-height for the default active accordion
                const target = document.querySelector(button.getAttribute("data-target"));
                if (target && target.classList.contains("active")) {
                    target.style.maxHeight = target.scrollHeight + "px";
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const accordians = document.querySelectorAll('.round_1_accordian');

            accordians.forEach(accordian => {
                accordian.addEventListener('click', function() {
                    const roundInner = this.nextElementSibling; // Target the adjacent .round_inner
                    const chevronIcon = this.querySelector('.chevron-icon'); // Target the chevron icon
                    const isVisible = roundInner.style.display === 'block';

                    // Toggle display
                    roundInner.style.display = isVisible ? 'none' : 'block';

                    // Toggle chevron icon class
                    if (isVisible) {
                        chevronIcon.classList.remove('fa-chevron-up');
                        chevronIcon.classList.add('fa-chevron-down');
                    } else {
                        chevronIcon.classList.remove('fa-chevron-down');
                        chevronIcon.classList.add('fa-chevron-up');
                    }
                });
            });
        });
    </script>

<?php
}
add_action('wp_footer', 'footer_script');




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










// Haris Functions

// team page
function func_teams($atts)
{
    ob_start();

    // Get shortcode attribute
    $atts = shortcode_atts(array(
        'league' => '',
    ), $atts, 'team_list');

    $league_slug = sanitize_title($atts['league']);

    // Setup query args
    $team_args = array(
        'post_type' => 'sp_team',
        'posts_per_page' => -1,
        'meta_key' => 'state', // Assuming 'state' is a meta field
        'orderby' => 'meta_value',
        'order' => 'ASC',
    );

    // Add league filter if slug provided
    if (!empty($league_slug)) {
        $team_args['tax_query'] = array(
            array(
                'taxonomy' => 'sp_league',
                'field'    => 'slug',
                'terms'    => $league_slug,
            ),
        );
    }

    $team_query = new WP_Query($team_args);

    // Collect states dynamically
    $states = [];
    if ($team_query->have_posts()) {
        while ($team_query->have_posts()) {
            $team_query->the_post();
            $state = get_post_meta(get_the_ID(), 'state', true);
            if ($state && !in_array($state, $states)) {
                $states[] = $state;
            }
        }
    }

    $tournament_args = array(
        'post_type'      => 'sp_tournament',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $tournament_query = new WP_Query($tournament_args);

    $tournament_name = 'Tournament Name Not Found'; // Default value
    $tournament_date = ''; // Default date value
    $season_name = 'Season Not Found'; // Default season value

    if ($tournament_query->have_posts()) {
        while ($tournament_query->have_posts()) {
            $tournament_query->the_post();
            $tournament_name = get_the_title();
            $tournament_date = get_the_date('M-Y');
            $tourmnent_logo = get_the_post_thumbnail_url(get_the_ID(), 'large');
            $tournament_id = get_the_ID();

            $tournment_name1 = get_post_meta($tournament_id, 'anchor1_name', true);
            $tournment_name2 = get_post_meta($tournament_id, 'anchor2_name', true);
            $tournment_name3 = get_post_meta($tournament_id, 'anchor3_name', true);
            $tournment_link1 = get_post_meta($tournament_id, 'anchor1_link', true);
            $tournment_link2 = get_post_meta($tournament_id, 'anchor2_link', true);
            $tournment_link3 = get_post_meta($tournament_id, 'anchor3_link', true);



            // Get the season name
            $tournament_id = get_the_ID();
            $seasons = wp_get_post_terms($tournament_id, 'sp_season');
            if (!empty($seasons) && !is_wp_error($seasons)) {
                $season_name = $seasons[0]->name; // Use the first season
            }
        }
    }


    wp_reset_postdata(); // Reset query after collecting states
    ?>

    <div class="team_main_containers">
        <div class="team_main_head">
            <div class="team_heads">
                <div class="team_heads_l">
                    <div class="ournment_logo">
                        <img src="<?php echo $tourmnent_logo; ?>" alt="Tournament Logo" />
                    </div>
                    <div class="team_details">
                        <h5><?php echo esc_html($season_name); ?></h5> <!-- Dynamic season name -->
                        <h2><?php echo esc_html($tournament_name); ?></h2> <!-- Dynamic tournament name -->
                        <h6><?php echo esc_html($tournament_date); ?></h6> <!-- Replace with dynamic date range if needed -->
                        <ul class="team_details_btns">
                            <li>
                                <a href="<?php echo $tournment_link1; ?>"><?php echo $tournment_name1; ?></a>
                            </li>
                            <li>
                                <a href="<?php echo $tournment_link2; ?>"><?php echo $tournment_name2; ?></a>
                            </li>
                            <li>
                                <a href="<?php echo $tournment_link3; ?>"><?php echo $tournment_name3; ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="sponsor_logo team_heads_r">
                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2025/04/sposor_logo.png" />
                </div>
            </div>
        </div>
        <div class="teamm_header">
            <div class="tabs">
                <button class="tab-btn active" onclick="filterTeams('all', event)">All</button>
                <?php foreach ($states as $state) : ?>
                    <button class="tab-btn" onclick="filterTeams('<?php echo esc_attr(sanitize_title($state)); ?>', event)">
                        <?php echo esc_html($state); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="team_title_main">
            <h1 class="">Teams</h1>
        </div>
        <div class="team_main_container">

            <?php
            if ($team_query->have_posts()) {
                while ($team_query->have_posts()) {
                    $team_query->the_post();
                    $team_id = get_the_ID();
                    $team_name = get_the_title();
                    $team_logo = get_the_post_thumbnail_url($team_id, 'thumbnail');
                    $team_permalink = get_permalink($team_id);
                    $team_state = get_post_meta($team_id, 'state', true);

                    // Get the captain of this team using tax + meta query
                    $captain_args = array(
                        'post_type' => 'sp_player',
                        'posts_per_page' => 1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'sp_position',
                                'field' => 'slug',
                                'terms' => 'captain',
                            ),
                        ),
                        'meta_query' => array(
                            array(
                                'key' => 'sp_team',
                                'value' => $team_id,
                                'compare' => '=',
                            ),
                        ),
                    );

                    $captain_query = new WP_Query($captain_args);
                    $captain_name = 'N/A';

                    if ($captain_query->have_posts()) {
                        $captain_query->the_post();
                        $captain_name = get_the_title();
                        wp_reset_postdata();
                    }
            ?>
                    <div class="team_container team-<?php echo esc_attr(sanitize_title($team_state)); ?>">
                        <div class="team_list">
                            <div class="team_head">
                                <div class="team_head_l">
                                    <?php if ($team_logo) : ?>
                                        <img src="<?php echo esc_url($team_logo); ?>" alt="team logo" />
                                    <?php endif; ?>
                                    <h3><?php echo esc_html($team_name); ?></h3>
                                </div>
                                <div class="team_head_r">
                                    <a href="<?php echo esc_url($team_permalink); ?>">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="team_body">
                                <h2><?php echo esc_html($team_name); ?></h2>
                            </div>
                            <div class="team_last">
                                <div class="team_last_l">
                                    <h3>Captain <span><?php echo esc_html($captain_name); ?></span></h3>
                                </div>
                                <div class="team_last_r">
                                    <h4><?php echo esc_html($team_state); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p>No teams found for this league.</p>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('team_list', 'func_teams');

add_action('wp_ajax_filter_events', 'filter_events_handler');
add_action('wp_ajax_nopriv_filter_events', 'filter_events_handler');

// team page filters on select option value
function func_league_tabs_result($atts)
{
    ob_start();

    // Fetch required data for dropdowns
    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1));

    ?>
    <form id="eventFilterForm" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="round-select" name="round[]">
                    <option value="">Select Round</option>
                    <?php
                    $unique_rounds = [];
                    if ($tournament_query->have_posts()) {
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $round_id = get_the_ID();
                            $rounds = get_post_meta(get_the_ID(), 'sp_labels', true) ?: [];
                            foreach ((array) $rounds as $round_group) {
                                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $index => $round) {
                            echo '<option value="' . esc_html($round_id) . '">' . esc_html($round) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="match_type_select" name="match_type[]">
                    <option value="">Select Match Type</option>
                    <?php
                    if ($tournament_query->have_posts()) {
                        $unique_match_types = [];
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $tournament_id = get_the_ID();
                            $match_type = get_post_meta($tournament_id, 'match-type', true) ?: 'Unknown';
                            if (!in_array($match_type, $unique_match_types)) {
                                $unique_match_types[] = $match_type;
                                echo '<option value="' . esc_html($tournament_id) . '">' . esc_html($match_type) . '</option>';
                            }
                        }
                        wp_reset_postdata(); // Reset after loop
                    } else {
                        echo '<option>No events found</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="state-select" name="state[]">
                    <option value="">Select State</option>
                    <?php
                    $unique_states = [];
                    if ($team_query->have_posts()) {
                        while ($team_query->have_posts()) {
                            $team_query->the_post();
                            $state_id = get_the_ID();
                            $state = get_post_meta(get_the_ID(), 'state', true);
                            if ($state && !in_array($state, $unique_states)) {
                                $unique_states[] = $state;
                                echo '<option value="' . $state_id . '">' . esc_html($state) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="venue-select" name="venue[]">
                    <option value="">Select Venue</option>
                    <?php
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <div id="team-players-table">
        <p>Select filters to display events.</p>
    </div>

    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     const form = document.getElementById('eventFilterForm');
        //     const teamPlayersTable = document.getElementById('team-players-table');

        //     form.addEventListener('change', function() {
        //         const formData = new FormData(form);

        //         const formObject = {};
        //         formData.forEach((value, key) => {
        //             if (!formObject[key]) {
        //                 formObject[key] = [];
        //             }
        //             formObject[key].push(value);
        //         });

        //         fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        //                 method: 'POST',
        //                 headers: {
        //                     'Content-Type': 'application/x-www-form-urlencoded'
        //                 },
        //                 body: new URLSearchParams({
        //                     action: 'filter_league_tabs',
        //                     ...formObject
        //                 })
        //             })
        //             .then(response => response.text())
        //             .then(data => {
        //                 teamPlayersTable.innerHTML = data;
        //             })
        //             .catch(error => {
        //                 teamPlayersTable.innerHTML = '<p>Error loading events. Please try again.</p>';
        //                 console.error(error);
        //             });
        //     });
        // });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('eventFilterForm');
            const teamPlayersTable = document.getElementById('team-players-table');

            // Create a loader element
            const loader = document.createElement('div');
            loader.id = 'loader_spinner';
            loader.style.display = 'none';
            loader.innerHTML = '<p>Loading...</p>';
            loader.style.textAlign = 'center';
            loader.style.margin = '20px';
            form.parentElement.insertBefore(loader, form.nextSibling);

            form.addEventListener('change', function() {
                const formData = new FormData(form);

                const formObject = {};
                formData.forEach((value, key) => {
                    if (!formObject[key]) {
                        formObject[key] = [];
                    }
                    formObject[key].push(value);
                });

                // Show loader
                loader.style.display = 'block';

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'filter_league_tabs',
                            ...formObject
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        teamPlayersTable.innerHTML = data;

                        // Hide loader
                        loader.style.display = 'none';

                        // Show "No events found" message if no content
                        if (!data.trim()) {
                            teamPlayersTable.innerHTML = '<p>No events found.</p>';
                        }
                    })
                    .catch(error => {
                        teamPlayersTable.innerHTML = '<p>Error loading events. Please try again.</p>';
                        console.error(error);

                        // Hide loader
                        loader.style.display = 'none';
                    });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('league_tabs_result', 'func_league_tabs_result');



function filter_league_tabs()
{
    // $state = sanitize_text_field($_POST['state'] ?? '');
    // $round = sanitize_text_field($_POST['round'] ?? '');
    // $match_type = sanitize_text_field($_POST['match_type'] ?? '');

    // $venue = intval($_POST['venue'] ?? 0);

    // $args = [
    //     'post_type' => 'sp_event',
    //     'posts_per_page' => -1,
    //     'meta_query' => [],
    //     'tax_query' => ['relation' => 'AND'],
    // ];

    // $tournaments_args = [
    //     'post_type' => 'sp_tournament',
    //     'posts_per_page' => -1,

    // ];

    // $team_args = [
    //     'post_type' => 'sp_team',
    //     'posts_per_page' => -1,

    // ];

    // if ($season) {
    //     $args['tax_query'][] = [
    //         'taxonomy' => 'sp_season',
    //         'field' => 'term_id',
    //         'terms' => $season,
    //     ];
    // }

    // if ($venue) {
    //     $args['tax_query'][] = [
    //         'taxonomy' => 'sp_venue',
    //         'field' => 'term_id',
    //         'terms' => $venue,
    //     ];
    // }

    // if ($state) {
    //     $team_args['meta_query'][] = [
    //         'key' => 'state',
    //         'value' => $state,
    //         'compare' => '=',
    //     ];
    // }

    // if ($round) {
    //     $tournaments_args['meta_query'][] = [
    //         'key' => 'sp_labels',
    //         'value' => sanitize_text_field($round),
    //         'compare' => 'LIKE', // Use LIKE for partial matches
    //     ];
    // }

    // if ($match_type) {
    //     $tournaments_args['meta_query'][] = [
    //         'key' => 'match-type',
    //         'value' => sanitize_text_field($match_type),
    //         'compare' => '=',
    //     ];
    // }




    $state_post = sanitize_text_field($_POST['state'] ?? '');
    $round_post = sanitize_text_field($_POST['round'] ?? '');
    $match_type_post = sanitize_text_field($_POST['match_type'] ?? '');
    $venue_post = intval($_POST['venue'] ?? 0);

    // Main event query arguments
    $args = [
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];

    $tournaments_args = [
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];

    $team_args1 = [
        'post_type' => 'sp_team',
        'posts_per_page' => -1,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];

    // Add filters based on input
    if (!empty($_POST['state'])) {
        $states = array_map('sanitize_text_field', (array) $_POST['state']);
        $team_args['meta_query'][] = [
            'key' => 'state',
            'value' => $state_post,
            'compare' => 'IN',
        ];
    }

    if (!empty($_POST['round'])) {
        $rounds = array_map('sanitize_text_field', (array) $_POST['round']);
        $tournaments_args['meta_query'][] = [
            'key' => 'sp_labels',
            'value' => $round_post,
            'compare' => 'LIKE', // Use LIKE for partial matches
        ];
    }

    if (!empty($_POST['match_type'])) {
        $match_types = array_map('sanitize_text_field', (array) $_POST['match_type']);
        $team_args1['meta_query'][] = [
            'key' => 'match-type',
            'value' => $match_type_post,
            'compare' => '=',
        ];
    }

    if (!empty($_POST['venue'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'sp_venue',
            'field' => 'term_id',
            'terms' => $venue_post,
        ];
    }

    $query = new WP_Query($args);

    $tourna_query = new WP_Query($tournaments_args);

    $team_query = new WP_Query($team_args1);


    if ($tourna_query->have_posts()) {
        while ($tourna_query->have_posts()) {
            $tourna_query->the_post();

            $event_metas = get_post_meta(get_the_ID());
            $tourn_match_type =  maybe_unserialize($event_metas['match-type'] ?? []);
            $tourn_round =  maybe_unserialize($event_metas['sp_labels'][0] ?? []);
            $array = $event_metas['sp_labels'][0];

            if (is_string($array)) {
                $array1_round = @unserialize($array);
                if ($array1_round !== false || $array === serialize(false)) {
                    // Successfully unserialized
                    $array1_round = array_filter((array)$array1_round); // Remove empty values
                } else {
                    $array1_round = ["Invalid serialized string"];
                }
            } else {
                $array1_round = ["Invalid data format"];
            }

            $rounds_display = implode(', ', $array1_round);
            // echo "<pre>";
            // print_r(json_encode($array1_round));
            // print_r($tourn_match_type);
            // echo "</pre>";
        }
    }

    if ($team_query->have_posts()) {
        while ($team_query->have_posts()) {
            $team_query->the_post();
            $team_meta = get_post_meta(get_the_ID());
            $team_state =  maybe_unserialize($team_meta['state'] ?? []);

            // echo "<pre>";
            // print_r($team_state);
            // echo "</pre>";
        }
    }



    if ($query->have_posts()) {
        echo '<table class="event-table league_table_res" border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                           <!-- <th>Team 1</th> -->
                            <th>Player 1</th>
                            <th>Score</th>
                            <th>Player 2</th>
                           <!--  <th>Team 2</th> -->
                            <th>Venue</th>
                           <!-- <th>Rounds</th>
                            <th>Match Type</th>
                            <th>State</th> -->
                        </tr>
                    </thead>
                    <tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $event_meta = get_post_meta(get_the_ID());

            $team_match =  maybe_unserialize($event_meta['match-type'][0] ?? []);


            // if ($state_post === $event_state) {
            //     $new_date = $state_post;
            //     echo $state_post;
            // }


            $event_state =  maybe_unserialize($event_meta['state'][0] ?? []);

            // echo "<pre>";
            // print_r($team_match);
            // echo "</pre>";

            // echo "<pre>";
            // print_r($event_state);
            // echo "</pre>";

    ?>
            <input type="hidden" name="round" value="<?php echo $rounds_display; ?>" />
            <input type="hidden" name="match_type" value="<?php echo $team_match; ?>" />
            <input type="hidden" name="state" value="<?php echo $event_state; ?>" />
            <?php



            $team_ids = maybe_unserialize($event_meta['sp_team'] ?? []);
            $team1 = get_the_title($team_ids[0] ?? '');
            $team2 = get_the_title($team_ids[1] ?? '');

            $team1_image = '';
            $team2_image = '';

            if (!empty($team_ids[0])) {
                $team1_image_id = get_post_thumbnail_id($team_ids[0]);
                $team1_image_src = wp_get_attachment_image_src($team1_image_id, 'full');
                $team1_image = $team1_image_src[0] ?? '';
            }

            if (!empty($team_ids[1])) {
                $team2_image_id = get_post_thumbnail_id($team_ids[1]);
                $team2_image_src = wp_get_attachment_image_src($team2_image_id, 'full');
                $team2_image = $team2_image_src[0] ?? '';
            }

            $player_ids_data = maybe_unserialize($event_meta['sp_player'] ?? []);
            $player_ids_data = array_filter($player_ids_data, function ($value) {
                return $value !== "0";
            });
            $player_ids_data = array_values($player_ids_data);

            $player_1_data = [];
            $player_2_data = [];
            if (count($player_ids_data) >= 1) {
                $player_1_data = array_slice($player_ids_data, 0, min(2, count($player_ids_data) / 2));
                $player_2_data = array_slice($player_ids_data, count($player_1_data));
            }

            $player_1_output = '';
            foreach ($player_1_data as $player_id) {
                $player_name = get_the_title($player_id);
                $player_image_id = get_post_thumbnail_id($player_id);
                $player_image_src = wp_get_attachment_image_src($player_image_id, 'thumbnail');
                $player_image = $player_image_src[0] ?? '';
                $player_1_output .= '<div><img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '"> ' . esc_html($player_name) . '</div>';
            }

            $player_2_output = '';
            foreach ($player_2_data as $player_id) {
                $player_name = get_the_title($player_id);
                $player_image_id = get_post_thumbnail_id($player_id);
                $player_image_src = wp_get_attachment_image_src($player_image_id, 'thumbnail');
                $player_image = $player_image_src[0] ?? '';
                $player_2_output .= '<div><img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '"> ' . esc_html($player_name) . '</div>';
            }

            $scores = maybe_unserialize($event_meta['sp_results'][0] ?? []);
            $score_display = $scores[$team_ids[0]]['points'] . ' - ' . $scores[$team_ids[1]]['points'];

            $venues = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_name = $venues[0]->name ?? 'Unknown';

            // Retrieve and process data (teams, players, scores, etc.)
            $team_ids = maybe_unserialize($event_meta['sp_team'] ?? []);
            $team1 = get_the_title($team_ids[0] ?? '');
            $team2 = get_the_title($team_ids[1] ?? '');

            $venue_terms = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_name = $venue_terms[0]->name ?? 'Unknown';

            ?>

    <?php

            echo '<tr>
                    <td>' . get_the_date('l, d F, Y g:i A') . '</td>
                    <td> <img src="' . esc_url($team1_image) . '" alt="' . esc_attr($team1) . '">' . esc_html($team1) . '</td>
                    <td>' . $player_1_output . '</td>
                    <td>' . esc_html($score_display) . '</td>
                    <td>' . $player_2_output . '</td>
                    <td> <img src="' . esc_url($team2_image) . '" alt="' . esc_attr($team2) . '">' . esc_html($team2) . '</td>
                    <td>' . esc_html($venue_name) . '</td>
                    <td>' . $rounds_display . '</td>
                    <td>' . esc_html($team_match) . '</td>
                    <td>' . esc_html($event_state) . '</td>
                </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No events found.</p>';
    }

    wp_die();
}
add_action('wp_ajax_filter_league_tabs', 'filter_league_tabs');
add_action('wp_ajax_nopriv_filter_league_tabs', 'filter_league_tabs');


function func_league_tabs_schedule($atts)
{
    ob_start();

    // Fetch required data for dropdowns
    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1));

    ?>
    <form id="eventFilterForm" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="round-select" name="round[]">
                    <option value="">Select Round</option>
                    <?php
                    $unique_rounds = [];
                    if ($tournament_query->have_posts()) {
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $round_id = get_the_ID();
                            $rounds = get_post_meta(get_the_ID(), 'sp_labels', true) ?: [];
                            foreach ((array) $rounds as $round_group) {
                                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $index => $round) {
                            echo '<option value="' . esc_html($round_id) . '">' . esc_html($round) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="match_type_select" name="match_type[]">
                    <option value="">Select Match Type</option>
                    <?php
                    if ($tournament_query->have_posts()) {
                        $unique_match_types = [];
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $tournament_id = get_the_ID();
                            $match_type = get_post_meta($tournament_id, 'match-type', true) ?: 'Unknown';
                            if (!in_array($match_type, $unique_match_types)) {
                                $unique_match_types[] = $match_type;
                                echo '<option value="' . esc_html($tournament_id) . '">' . esc_html($match_type) . '</option>';
                            }
                        }
                        wp_reset_postdata(); // Reset after loop
                    } else {
                        echo '<option>No events found</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="state-select" name="state[]">
                    <option value="">Select State</option>
                    <?php
                    $unique_states = [];
                    if ($team_query->have_posts()) {
                        while ($team_query->have_posts()) {
                            $team_query->the_post();
                            $state_id = get_the_ID();
                            $state = get_post_meta(get_the_ID(), 'state', true);
                            if ($state && !in_array($state, $unique_states)) {
                                $unique_states[] = $state;
                                echo '<option value="' . $state_id . '">' . esc_html($state) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="venue-select" name="venue[]">
                    <option value="">Select Venue</option>
                    <?php
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <div id="team-players-table">
        <p>Select filters to display events.</p>
    </div>

    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     const form = document.getElementById('eventFilterForm');
        //     const teamPlayersTable = document.getElementById('team-players-table');

        //     form.addEventListener('change', function() {
        //         const formData = new FormData(form);

        //         const formObject = {};
        //         formData.forEach((value, key) => {
        //             if (!formObject[key]) {
        //                 formObject[key] = [];
        //             }
        //             formObject[key].push(value);
        //         });

        //         fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        //                 method: 'POST',
        //                 headers: {
        //                     'Content-Type': 'application/x-www-form-urlencoded'
        //                 },
        //                 body: new URLSearchParams({
        //                     action: 'filter_league_tabs',
        //                     ...formObject
        //                 })
        //             })
        //             .then(response => response.text())
        //             .then(data => {
        //                 teamPlayersTable.innerHTML = data;
        //             })
        //             .catch(error => {
        //                 teamPlayersTable.innerHTML = '<p>Error loading events. Please try again.</p>';
        //                 console.error(error);
        //             });
        //     });
        // });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('eventFilterForm');
            const teamPlayersTable = document.getElementById('team-players-table');

            // Create a loader element
            const loader = document.createElement('div');
            loader.id = 'loader_spinner';
            loader.style.display = 'none';
            loader.innerHTML = '<p>Loading...</p>';
            loader.style.textAlign = 'center';
            loader.style.margin = '20px';
            form.parentElement.insertBefore(loader, form.nextSibling);

            form.addEventListener('change', function() {
                const formData = new FormData(form);

                const formObject = {};
                formData.forEach((value, key) => {
                    if (!formObject[key]) {
                        formObject[key] = [];
                    }
                    formObject[key].push(value);
                });

                // Show loader
                loader.style.display = 'block';

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'filter_league_tabs',
                            ...formObject
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        teamPlayersTable.innerHTML = data;

                        // Hide loader
                        loader.style.display = 'none';

                        // Show "No events found" message if no content
                        if (!data.trim()) {
                            teamPlayersTable.innerHTML = '<p>No events found.</p>';
                        }
                    })
                    .catch(error => {
                        teamPlayersTable.innerHTML = '<p>Error loading events. Please try again.</p>';
                        console.error(error);

                        // Hide loader
                        loader.style.display = 'none';
                    });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('league_tabs_schedule', 'func_league_tabs_schedule');

function filter_league_tabs_schedule()
{
    // $state = sanitize_text_field($_POST['state'] ?? '');
    // $round = sanitize_text_field($_POST['round'] ?? '');
    // $match_type = sanitize_text_field($_POST['match_type'] ?? '');

    // $venue = intval($_POST['venue'] ?? 0);

    // $args = [
    //     'post_type' => 'sp_event',
    //     'posts_per_page' => -1,
    //     'meta_query' => [],
    //     'tax_query' => ['relation' => 'AND'],
    // ];

    // $tournaments_args = [
    //     'post_type' => 'sp_tournament',
    //     'posts_per_page' => -1,

    // ];

    // $team_args = [
    //     'post_type' => 'sp_team',
    //     'posts_per_page' => -1,

    // ];

    // if ($season) {
    //     $args['tax_query'][] = [
    //         'taxonomy' => 'sp_season',
    //         'field' => 'term_id',
    //         'terms' => $season,
    //     ];
    // }

    // if ($venue) {
    //     $args['tax_query'][] = [
    //         'taxonomy' => 'sp_venue',
    //         'field' => 'term_id',
    //         'terms' => $venue,
    //     ];
    // }

    // if ($state) {
    //     $team_args['meta_query'][] = [
    //         'key' => 'state',
    //         'value' => $state,
    //         'compare' => '=',
    //     ];
    // }

    // if ($round) {
    //     $tournaments_args['meta_query'][] = [
    //         'key' => 'sp_labels',
    //         'value' => sanitize_text_field($round),
    //         'compare' => 'LIKE', // Use LIKE for partial matches
    //     ];
    // }

    // if ($match_type) {
    //     $tournaments_args['meta_query'][] = [
    //         'key' => 'match-type',
    //         'value' => sanitize_text_field($match_type),
    //         'compare' => '=',
    //     ];
    // }




    $state_post = sanitize_text_field($_POST['state'] ?? '');
    $round_post = sanitize_text_field($_POST['round'] ?? '');
    $match_type_post = sanitize_text_field($_POST['match_type'] ?? '');
    $venue_post = intval($_POST['venue'] ?? 0);

    // Main event query arguments
    $args = [
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];

    $tournaments_args = [
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];

    $team_args1 = [
        'post_type' => 'sp_team',
        'posts_per_page' => -1,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];

    // Add filters based on input
    if (!empty($_POST['state'])) {
        $states = array_map('sanitize_text_field', (array) $_POST['state']);
        $team_args['meta_query'][] = [
            'key' => 'state',
            'value' => $state_post,
            'compare' => 'IN',
        ];
    }

    if (!empty($_POST['round'])) {
        $rounds = array_map('sanitize_text_field', (array) $_POST['round']);
        $tournaments_args['meta_query'][] = [
            'key' => 'sp_labels',
            'value' => $round_post,
            'compare' => 'LIKE', // Use LIKE for partial matches
        ];
    }

    if (!empty($_POST['match_type'])) {
        $match_types = array_map('sanitize_text_field', (array) $_POST['match_type']);
        $team_args1['meta_query'][] = [
            'key' => 'match-type',
            'value' => $match_type_post,
            'compare' => '=',
        ];
    }

    if (!empty($_POST['venue'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'sp_venue',
            'field' => 'term_id',
            'terms' => $venue_post,
        ];
    }

    $query = new WP_Query($args);

    $tourna_query = new WP_Query($tournaments_args);

    $team_query = new WP_Query($team_args1);


    if ($tourna_query->have_posts()) {
        while ($tourna_query->have_posts()) {
            $tourna_query->the_post();

            $event_metas = get_post_meta(get_the_ID());
            $tourn_match_type =  maybe_unserialize($event_metas['match-type'] ?? []);
            $tourn_round =  maybe_unserialize($event_metas['sp_labels'][0] ?? []);
            $array = $event_metas['sp_labels'][0];

            if (is_string($array)) {
                $array1_round = @unserialize($array);
                if ($array1_round !== false || $array === serialize(false)) {
                    // Successfully unserialized
                    $array1_round = array_filter((array)$array1_round); // Remove empty values
                } else {
                    $array1_round = ["Invalid serialized string"];
                }
            } else {
                $array1_round = ["Invalid data format"];
            }

            $rounds_display = implode(', ', $array1_round);
            // echo "<pre>";
            // print_r(json_encode($array1_round));
            // print_r($tourn_match_type);
            // echo "</pre>";
        }
    }

    if ($team_query->have_posts()) {
        while ($team_query->have_posts()) {
            $team_query->the_post();
            $team_meta = get_post_meta(get_the_ID());
            $team_state =  maybe_unserialize($team_meta['state'] ?? []);

            // echo "<pre>";
            // print_r($team_state);
            // echo "</pre>";
        }
    }



    if ($query->have_posts()) {

        echo '
        <div class="schedule_round_results_accordion">
            <div class="schedule_round_results_container">
                <div class="label">
                    <div class="schedule_round_results_data">
                        <div class="tournment_logo">
                            <img src="" alt="" />
                        </div>
                        <div class="">
                            <h4>Jan 21 - 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>
                </div>
                <div class="content">';


        echo '<table class="event-table league_table_res" border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Match</th>
                            <!-- <th>Date & Time</th>-->
                            <th>Team 1</th> 
                            <!-- <th>Player 1</th> -->
                            <th>Score</th>
                            <!-- <th>Player 2</th> -->
                            <th>Team 2</th>
                            <th>Venue</th>
                           <!-- <th>Rounds</th>
                            <th>Match Type</th>
                            <th>State</th> -->
                        </tr>
                    </thead>
                    <tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $event_meta = get_post_meta(get_the_ID());

            $team_match =  maybe_unserialize($event_meta['match-type'][0] ?? []);


            // if ($state_post === $event_state) {
            //     $new_date = $state_post;
            //     echo $state_post;
            // }


            $event_state =  maybe_unserialize($event_meta['state'][0] ?? []);

            // echo "<pre>";
            // print_r($team_match);
            // echo "</pre>";

            // echo "<pre>";
            // print_r($event_state);
            // echo "</pre>";

    ?>
            <input type="hidden" name="round" value="<?php echo $rounds_display; ?>" />
            <input type="hidden" name="match_type" value="<?php echo $team_match; ?>" />
            <input type="hidden" name="state" value="<?php echo $event_state; ?>" />
            <?php



            $team_ids = maybe_unserialize($event_meta['sp_team'] ?? []);
            $team1 = get_the_title($team_ids[0] ?? '');
            $team2 = get_the_title($team_ids[1] ?? '');

            $team1_image = '';
            $team2_image = '';

            if (!empty($team_ids[0])) {
                $team1_image_id = get_post_thumbnail_id($team_ids[0]);
                $team1_image_src = wp_get_attachment_image_src($team1_image_id, 'full');
                $team1_image = $team1_image_src[0] ?? '';
            }

            if (!empty($team_ids[1])) {
                $team2_image_id = get_post_thumbnail_id($team_ids[1]);
                $team2_image_src = wp_get_attachment_image_src($team2_image_id, 'full');
                $team2_image = $team2_image_src[0] ?? '';
            }

            $player_ids_data = maybe_unserialize($event_meta['sp_player'] ?? []);
            $player_ids_data = array_filter($player_ids_data, function ($value) {
                return $value !== "0";
            });
            $player_ids_data = array_values($player_ids_data);

            $player_1_data = [];
            $player_2_data = [];
            if (count($player_ids_data) >= 1) {
                $player_1_data = array_slice($player_ids_data, 0, min(2, count($player_ids_data) / 2));
                $player_2_data = array_slice($player_ids_data, count($player_1_data));
            }

            $player_1_output = '';
            foreach ($player_1_data as $player_id) {
                $player_name = get_the_title($player_id);
                $player_image_id = get_post_thumbnail_id($player_id);
                $player_image_src = wp_get_attachment_image_src($player_image_id, 'thumbnail');
                $player_image = $player_image_src[0] ?? '';
                $player_1_output .= '<div><img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '"> ' . esc_html($player_name) . '</div>';
            }

            $player_2_output = '';
            foreach ($player_2_data as $player_id) {
                $player_name = get_the_title($player_id);
                $player_image_id = get_post_thumbnail_id($player_id);
                $player_image_src = wp_get_attachment_image_src($player_image_id, 'thumbnail');
                $player_image = $player_image_src[0] ?? '';
                $player_2_output .= '<div><img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '"> ' . esc_html($player_name) . '</div>';
            }

            $scores = maybe_unserialize($event_meta['sp_results'][0] ?? []);
            $score_display = $scores[$team_ids[0]]['points'] . ' - ' . $scores[$team_ids[1]]['points'];

            $venues = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_name = $venues[0]->name ?? 'Unknown';

            // Retrieve and process data (teams, players, scores, etc.)
            $team_ids = maybe_unserialize($event_meta['sp_team'] ?? []);
            $team1 = get_the_title($team_ids[0] ?? '');
            $team2 = get_the_title($team_ids[1] ?? '');

            $venue_terms = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_name = $venue_terms[0]->name ?? 'Unknown';

            ?>

    <?php

            echo '<tr>
                    <td>' . get_the_date('l, d F, Y g:i A') . '</td>
                    <td> <img src="' . esc_url($team1_image) . '" alt="' . esc_attr($team1) . '">' . esc_html($team1) . '</td>
                    <td>' . $player_1_output . '</td>
                    <td>' . esc_html($score_display) . '</td>
                    <td>' . $player_2_output . '</td>
                    <td> <img src="' . esc_url($team2_image) . '" alt="' . esc_attr($team2) . '">' . esc_html($team2) . '</td>
                    <td>' . esc_html($venue_name) . '</td>
                    <td>' . $rounds_display . '</td>
                    <td>' . esc_html($team_match) . '</td>
                    <td>' . esc_html($event_state) . '</td>
                </tr>';
        }

        echo '
                </tbody>
                </table>
                </div>
                </div>
                </div>';
    } else {
        echo '<p>No events found.</p>';
    }

    wp_die();
}
add_action('wp_ajax_filter_league_tabs_schedule', 'filter_league_tabs_schedule');
add_action('wp_ajax_nopriv_filter_league_tabs_schedule', 'filter_league_tabs_schedule');

// post blog data
function func_post_data()
{
    ob_start();
    ?>
    <?php
    // Query posts from category with ID 9
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Show all posts
        'cat' => 9, // Replace with your desired category ID
    );

    $query = new WP_Query($args);

    echo '<div class="post_data_replay post_data_team">';

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();
            $title = get_the_title();
            $time_ago = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';
            $categories = get_the_category();
            $featured_image = get_the_post_thumbnail_url($post_id, 'full'); // Get featured image URL
            $category_names = array_map(function ($cat) {
                return $cat->name;
            }, $categories);
    ?>
            <div class="post-item">
                <?php if ($featured_image): ?>
                    <a href="<?php echo get_the_permalink($post_id); ?>"><img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($title); ?>" class="featured-image" /></a>
                <?php endif; ?>
                <div class="post-content">
                    <p class="post_duration_ago"><?php echo esc_html($time_ago); ?></p>
                    <h3><a href="<?php echo get_the_permalink($post_id); ?>"><?php echo esc_html($title); ?></a></h3>

                    <p class="post-categories"><?php echo implode(', ', $category_names); ?></p>
                </div>
            </div>
    <?php
        endwhile;
    else :
        echo '<p>No posts found in this category.</p>';
    endif;
    echo '</div>';

    wp_reset_postdata(); // Reset query data
    return ob_get_clean();
}
add_shortcode('post_replays', 'func_post_data');

// league teams
function func_league_teams()
{
    ob_start();

    $team_args = array(
        'post_type' => 'sp_team',
        'posts_per_page' => -1,
        'meta_key' => 'state', // Assuming 'state' is a meta field
        'orderby' => 'meta_value',
        'order' => 'ASC',
    );

    $team_query = new WP_Query($team_args); // Initialize the query
    $states = [];

    if ($team_query->have_posts()) {
        while ($team_query->have_posts()) {
            $team_query->the_post();
            $state = get_post_meta(get_the_ID(), 'state', true);
            if ($state && !in_array($state, $states)) {
                $states[] = $state;
            }
        }
        wp_reset_postdata(); // Reset after collecting states
    }

    ?>
    <div class="league_teams_section">
        <div class="tabs">
            <button class="tab-btn active" onclick="filterTeams('all', event)">All</button>
            <?php foreach ($states as $state) : ?>
                <button class="tab-btn" onclick="filterTeams('<?php echo esc_attr(sanitize_title($state)); ?>', event)">
                    <?php echo esc_html($state); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="team_main_container">
        <?php
        $team_query = new WP_Query($team_args); // Reinitialize query to display teams

        if ($team_query->have_posts()) {
            while ($team_query->have_posts()) {
                $team_query->the_post();
                $team_id = get_the_ID();
                $team_name = get_the_title();
                $team_logo = get_the_post_thumbnail_url($team_id, 'thumbnail');
                $team_permalink = get_permalink($team_id);
                $team_state = get_post_meta($team_id, 'state', true);

                // Get the captain of this team using tax + meta query
                $captain_args = array(
                    'post_type' => 'sp_player',
                    'posts_per_page' => 1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'sp_position',
                            'field' => 'slug',
                            'terms' => 'captain',
                        ),
                    ),
                    'meta_query' => array(
                        array(
                            'key' => 'sp_team',
                            'value' => $team_id,
                            'compare' => '=',
                        ),
                    ),
                );

                $captain_query = new WP_Query($captain_args);
                $captain_name = 'N/A';

                if ($captain_query->have_posts()) {
                    $captain_query->the_post();
                    $captain_name = get_the_title();
                }
                wp_reset_postdata();
        ?>
                <div class="team_container team-<?php echo esc_attr(sanitize_title($team_state)); ?>">
                    <div class="team_list">
                        <div class="team_head">
                            <div class="team_head_l">
                                <?php if ($team_logo) : ?>
                                    <img src="<?php echo esc_url($team_logo); ?>" alt="team logo" />
                                <?php endif; ?>
                                <h3><?php echo esc_html($team_name); ?></h3>
                            </div>
                            <div class="team_head_r">
                                <a href="<?php echo esc_url($team_permalink); ?>">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="team_body">
                            <h2><?php echo esc_html($team_name); ?></h2>
                        </div>
                        <div class="team_last">
                            <div class="team_last_l">
                                <h3>Captain <span><?php echo esc_html($captain_name); ?></span></h3>
                            </div>
                            <div class="team_last_r">
                                <h4><?php echo esc_html($team_state); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<p>No teams found for this league.</p>';
        }
        ?>
    </div>
<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('league_teams', 'func_league_teams');

// league price pool
function func_league_price_pool($atts)
{
    ob_start();

    // Extract shortcode attributes and sanitize inputs
    $atts = shortcode_atts(array(
        'league' => '',
        'season' => '',
    ), $atts);

    $league_slug = sanitize_title($atts['league']);
    $season_slug = sanitize_title($atts['season']);

    // Check if league slug is provided
    if (empty($league_slug)) {
        echo '<p>Please provide a valid league slug in the shortcode.</p>';
        return ob_get_clean();
    }

    // Query tournaments associated with the league and season
    $tournament_args = array(
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'orderby' => 'meta_value', // Assuming sorting by meta key 'state'
        'order' => 'ASC',
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'sp_league', // Taxonomy for league
                'field'    => 'slug',
                'terms'    => $league_slug,
            ),
            array(
                'taxonomy' => 'sp_season', // Taxonomy for season
                'field'    => 'slug',
                'terms'    => $season_slug,
            ),
        ),
    );

    $tournament_query = new WP_Query($tournament_args);

    // Display tournaments
    if ($tournament_query->have_posts()) {
        echo '<div class="league_tournament-list">';
        while ($tournament_query->have_posts()) {
            $tournament_query->the_post();

            // Tournament details
            $tournament_title = get_the_title();
            $tournament_permalink = get_permalink();

            // Display tournament details
            echo '<div class="league_tournament-item">';
            echo '<h4>' . esc_html($tournament_title) . '</h4>';
            echo '<ul>';

            // Handle custom fields for 1st to 8th places
            for ($i = 1; $i <= 8; $i++) {
                // Dynamically generate field names
                $poll_title = get_field("price_title_{$i}");
                $price_pool = get_field("price_poll_{$i}");

                // if ($poll_title && $price_pool) {
                echo '<li>';
                echo '<h4>' . esc_html($poll_title) . '</h4>';
                echo '<h2>' . esc_html($price_pool) . '</h2>';
                echo '</li>';
                // }
            }

            echo '</ul>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No tournaments found for the specified league and season.</p>';
    }

    // Reset post data
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('league_price_pool', 'func_league_price_pool');

// list of all players stats
function func_player_list_stat()
{
    ob_start();

    // Query all players
    $players = new WP_Query([
        'post_type' => 'sp_player',
        'posts_per_page' => -1,
    ]);

    if ($players->have_posts()) {
        echo '<table class="team-players-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>#</th>';
        echo '<th>Name</th>';
        echo '<th>PWR</th>';
        echo '<th>Won</th>';
        echo '<th>Lost</th>';
        echo '<th>F</th>';
        echo '<th>A</th>';
        echo '<th>PTS</th>';
        echo '<th>STRK</th>';
        echo '<th>DUPR</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $counter = 1;

        while ($players->have_posts()) {
            $players->the_post();

            $player_id = get_the_ID();
            $player_name = get_the_title();
            $player_image = get_the_post_thumbnail_url($player_id, 'thumbnail');

            // Helper function for meta values
            $meta = function ($key) use ($player_id) {
                return get_post_meta($player_id, $key, true);
            };

            echo '<tr>';
            echo '<td>' . esc_html($counter++) . '</td>';
            echo '<td class="player_name">';
            echo '<img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '" style="border-radius: 50%;">';
            echo '<h4>' . esc_html($player_name) . '</h4>';
            echo '</td>';
            echo '<td>' . esc_html($meta('player_pwr')) . '</td>';
            echo '<td>' . esc_html($meta('player_won')) . '</td>';
            echo '<td>' . esc_html($meta('player_lost')) . '</td>';
            echo '<td>' . esc_html($meta('player_f')) . '</td>';
            echo '<td>' . esc_html($meta('player_a')) . '</td>';
            echo '<td>' . esc_html($meta('player_points')) . '</td>';
            echo '<td>' . esc_html($meta('player_strick')) . '</td>';
            echo '<td>' . esc_html($meta('player_dupr')) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        wp_reset_postdata();
    } else {
        echo '<p>No players found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('player_list_stat', 'func_player_list_stat');

// League Faqs
function func_league_faqs($atts)
{
    ob_start();

    // Extract the 'cat' attribute for the category ID
    $atts = shortcode_atts([
        'cat' => '', // Default category is empty
    ], $atts, 'league_faqs');

    $cat_id = intval($atts['cat']); // Ensure 'cat' is an integer

    // Define WP_Query arguments
    $args = [
        'post_type'      => 'faq',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'cat'            => $cat_id,
    ];

    $query = new WP_Query($args);

    // Check if there are posts
    if ($query->have_posts()) {
        // Accordion container
        echo '<div class="faq-accordion">';

        $counter = 1; // Counter to determine the default open accordion item
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $title = get_the_title();
            $content = get_the_content();

            // Accordion item
            echo '<div class="faqs_accordion_item">';
            echo '<div class="accordion-header" id="heading-' . $post_id . '">';
            echo '<button class="accordion-button ' . ($counter === 1 ? 'open' : '') . '" data-target="#content-' . $post_id . '">';
            echo esc_html($title);
            echo ' <span class="chevron ' . ($counter === 1 ? 'up' : 'down') . '"><i class="fa-solid fa-chevron-down"></i></span>';
            echo '</button>';
            echo '</div>'; // .accordion-header

            echo '<div id="content-' . $post_id . '" class="accordion-content ' . ($counter === 1 ? 'active' : '') . '">';
            echo '<div class="accordion-content_inner">';
            echo '<div class="cont_faq_l"></div>';
            echo '<div class="cont_faq_r">';
            echo wp_kses_post($content);
            echo '</div>';
            echo '</div>';
            echo '</div>'; // .accordion-content
            echo '</div>'; // .accordion-item

            $counter++;
        }

        echo '</div>'; // .faq-accordion
    } else {
        echo '<p>No FAQs found for this category.</p>';
    }

    wp_reset_postdata(); // Reset post data

    return ob_get_clean();
}
add_shortcode('league_faqs', 'func_league_faqs');

// league overview
function func_league_overview()
{
    ob_start();
    ?>
    <div class="main_league_overview">
        <div class="league_overview_sec">
            <!-- <h2>Overview</h2> -->
            <div class="league_overview_inner">
                <div class="league_overview_inner_l">
                    <?php echo the_field('overview_heading'); ?>
                </div>
                <div class="league_overview_inner_r">
                    <?php echo the_field('overview_content'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_composition_content_sec">
            <h2>League Composition</h2>
            <div class="league_overview_inner league_composition_content_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r league_composition_content_inner_r">
                    <?php echo the_field('league_composition_content'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_venue_sec">
            <h2>Venue</h2>
            <div class="league_overview_inner league_venue_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r league_venue_inner_r">
                    <?php echo the_field('venue_content'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_team_composition_sec">
            <h2>Team Composition</h2>
            <div class="league_overview_inner team_composition_content_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r team_composition_content_inner_r">
                    <?php echo the_field('team_composition_content'); ?>
                </div>
            </div>
        </div>


        <div class="league_overview_sec league_format_sec">
            <h2>Formats</h2>
            <div class="league_overview_inner team_format_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r team_format_inner_r">
                    <?php echo the_field('format_content'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_matches_sec">
            <h2>League Matches</h2>
            <div class="league_overview_inner league_matches_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r league_matches_inner_r">
                    <?php echo the_field('team_composition_content'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_matches_sec">
            <h2>Partners & Sponsors</h2>
            <div class="league_overview_inner league_matches_inner">

                <div class="sponsor_leagues">
                    <?php echo the_field('partners_sponsors'); ?>
                </div>
            </div>
        </div>

    </div>
<?php
    return ob_get_clean();
}
add_shortcode('league_overview', 'func_league_overview');

// League CPT
function create_league_cpt()
{
    // Labels for Custom Post Type
    $labels = array(
        'name' => _x('Leagues', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('League', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('Leagues', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('League', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('League Archives', 'textdomain'),
        'attributes' => __('League Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent League:', 'textdomain'),
        'all_items' => __('All Leagues', 'textdomain'),
        'add_new_item' => __('Add New League', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New League', 'textdomain'),
        'edit_item' => __('Edit League', 'textdomain'),
        'update_item' => __('Update League', 'textdomain'),
        'view_item' => __('View League', 'textdomain'),
        'view_items' => __('View Leagues', 'textdomain'),
        'search_items' => __('Search League', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into League', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this League', 'textdomain'),
        'items_list' => __('Leagues list', 'textdomain'),
        'items_list_navigation' => __('Leagues list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Leagues list', 'textdomain'),
    );

    // Arguments for Custom Post Type
    $args = array(
        'label' => __('League', 'textdomain'),
        'description' => __('Custom post type for Leagues', 'textdomain'),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'trackbacks', 'page-attributes', 'post-formats', 'custom-fields'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );

    // Register Custom Post Type
    register_post_type('league', $args);

    // Register Custom Taxonomy
    $taxonomy_labels = array(
        'name' => _x('League Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('League Category', 'Taxonomy Singular Name', 'textdomain'),
        'menu_name' => __('League Categories', 'textdomain'),
        'all_items' => __('All Categories', 'textdomain'),
        'edit_item' => __('Edit Category', 'textdomain'),
        'view_item' => __('View Category', 'textdomain'),
        'update_item' => __('Update Category', 'textdomain'),
        'add_new_item' => __('Add New Category', 'textdomain'),
        'new_item_name' => __('New Category Name', 'textdomain'),
        'search_items' => __('Search Categories', 'textdomain'),
        'not_found' => __('No categories found', 'textdomain'),
    );

    $taxonomy_args = array(
        'labels' => $taxonomy_labels,
        'hierarchical' => true, // Behaves like categories
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => false,
        'show_in_quick_edit' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
    );

    // Attach the custom taxonomy only to the "league" post type
    register_taxonomy('league_category', array('league'), $taxonomy_args);
}
add_action('init', 'create_league_cpt', 0);

// Pre League CPT
function register_pre_league_cpt_and_taxonomy()
{
    // Register Pre League Custom Post Type
    $labels = array(
        'name' => _x('Pre League', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Pre League', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => 'Pre League',
        'all_items' => 'All Pre League',
        'add_new_item' => 'Add New Pre League',
        'new_item' => 'New Pre League',
        'edit_item' => 'Edit Pre League',
        'view_item' => 'View Pre League',
        'search_items' => 'Search Pre League',
        'not_found' => 'No Pre League found',
        'not_found_in_trash' => 'No Pre League found in Trash',
    );

    $args = array(
        'label' => 'Pre League',
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'author'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'preleague'),
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );

    register_post_type('preleague', $args);

    // Register Pre League Custom Taxonomy
    $taxonomy_labels = array(
        'name' => _x('Pre League Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('Pre League Category', 'Taxonomy Singular Name', 'textdomain'),
        'search_items' => 'Search Pre League Categories',
        'all_items' => 'All Pre League Categories',
        'edit_item' => 'Edit Pre League Category',
        'update_item' => 'Update Pre League Category',
        'add_new_item' => 'Add New Pre League Category',
        'new_item_name' => 'New Pre League Category Name',
        'menu_name' => 'Pre League Categories',
    );

    $taxonomy_args = array(
        'labels' => $taxonomy_labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'preleague-category'),
    );

    register_taxonomy('preleague_category', array('preleague'), $taxonomy_args);
}
add_action('init', 'register_pre_league_cpt_and_taxonomy');

// Register Post League Custom Post Type and Taxonomy
function register_post_league_cpt_and_taxonomy()
{
    // Repeat structure for Post League
    $labels = array(
        'name' => _x('Post League', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Post League', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => 'Post League',
        'all_items' => 'All Post League',
        'add_new_item' => 'Add New Post League',
        'new_item' => 'New Post League',
        'edit_item' => 'Edit Post League',
        'view_item' => 'View Post League',
        'search_items' => 'Search Post League',
        'not_found' => 'No Post League found',
        'not_found_in_trash' => 'No Post League found in Trash',
    );

    $args = array(
        'label' => 'Post League',
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'author'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'postleague'),
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );

    register_post_type('postleague', $args);

    $taxonomy_labels = array(
        'name' => _x('Post League Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('Post League Category', 'Taxonomy Singular Name', 'textdomain'),
        'search_items' => 'Search Post League Categories',
        'all_items' => 'All Post League Categories',
        'edit_item' => 'Edit Post League Category',
        'update_item' => 'Update Post League Category',
        'add_new_item' => 'Add New Post League Category',
        'new_item_name' => 'New Post League Category Name',
        'menu_name' => 'Post League Categories',
    );

    $taxonomy_args = array(
        'labels' => $taxonomy_labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'postleague-category'),
    );

    register_taxonomy('postleague_category', array('postleague'), $taxonomy_args);
}
add_action('init', 'register_post_league_cpt_and_taxonomy');

// Register Mid League Custom Post Type and Taxonomy
function register_mid_league_cpt_and_taxonomy()
{
    // Repeat structure for Mid League
    $labels = array(
        'name' => _x('Mid League', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Mid League', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => 'Mid League',
        'all_items' => 'All Mid League',
        'add_new_item' => 'Add New Mid League',
        'new_item' => 'New Mid League',
        'edit_item' => 'Edit Mid League',
        'view_item' => 'View Mid League',
        'search_items' => 'Search Mid League',
        'not_found' => 'No Mid League found',
        'not_found_in_trash' => 'No Mid League found in Trash',
    );

    $args = array(
        'label' => 'Mid League',
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'author'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'midleague'),
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );

    register_post_type('midleague', $args);

    $taxonomy_labels = array(
        'name' => _x('Mid League Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('Mid League Category', 'Taxonomy Singular Name', 'textdomain'),
        'search_items' => 'Search Mid League Categories',
        'all_items' => 'All Mid League Categories',
        'edit_item' => 'Edit Mid League Category',
        'update_item' => 'Update Mid League Category',
        'add_new_item' => 'Add New Mid League Category',
        'new_item_name' => 'New Mid League Category Name',
        'menu_name' => 'Mid League Categories',
    );

    $taxonomy_args = array(
        'labels' => $taxonomy_labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'midleague-category'),
    );

    register_taxonomy('midleague_category', array('midleague'), $taxonomy_args);
}
add_action('init', 'register_mid_league_cpt_and_taxonomy');

// Faq CPT
function create_faq_cpt()
{

    $labels = array(
        'name' => _x('FAQs', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('FAQ', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('FAQs', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('FAQ', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('FAQ Archives', 'textdomain'),
        'attributes' => __('FAQ Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent FAQ:', 'textdomain'),
        'all_items' => __('All FAQs', 'textdomain'),
        'add_new_item' => __('Add New FAQ', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New FAQ', 'textdomain'),
        'edit_item' => __('Edit FAQ', 'textdomain'),
        'update_item' => __('Update FAQ', 'textdomain'),
        'view_item' => __('View FAQ', 'textdomain'),
        'view_items' => __('View FAQs', 'textdomain'),
        'search_items' => __('Search FAQ', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into FAQ', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this FAQ', 'textdomain'),
        'items_list' => __('FAQs list', 'textdomain'),
        'items_list_navigation' => __('FAQs list navigation', 'textdomain'),
        'filter_items_list' => __('Filter FAQs list', 'textdomain'),
    );
    $args = array(
        'label' => __('FAQ', 'textdomain'),
        'description' => __('', 'textdomain'),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'trackbacks', 'page-attributes', 'post-formats', 'custom-fields'),
        'taxonomies' => array('category', 'post_tag'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 6,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'hierarchical' => true,
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type('faq', $args);
}
add_action('init', 'create_faq_cpt', 0);

// Pre League news data
function func_pre_league_post_data()
{
    ob_start();
    ?>
    <?php
    // Query posts from category with ID 9
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 4, // Show all posts
        'cat' => 9, // Replace with your desired category ID
    );

    $query = new WP_Query($args);

    echo '<div class="post_data_replay post_data_team pre_league_news_blog">';

    if ($query->have_posts()) :
        // Start left section for the first post
        echo '<div class="post_left">';

        // Fetch and display the first post
        $query->the_post();
        $post_id = get_the_ID();
        $title = get_the_title();
        $time_ago = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';
        $categories = get_the_category();
        $featured_image = get_the_post_thumbnail_url($post_id, 'full'); // Get featured image URL
        $category_names = array_map(function ($cat) {
            return $cat->name;
        }, $categories);
    ?>
        <div class="post-item">
            <?php if ($featured_image): ?>
                <a href="<?php echo get_the_permalink($post_id); ?>"><img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($title); ?>" class="featured-image" /></a>
            <?php endif; ?>
            <div class="post-content">
                <p class="post_duration_ago"><?php echo esc_html($time_ago); ?></p>
                <h3><a href="<?php echo get_the_permalink($post_id); ?>"><?php echo esc_html($title); ?></a></h3>
                <p class="post-categories"><?php echo implode(', ', $category_names); ?></p>
            </div>
        </div>
        <?php
        echo '</div>'; // Close post_left

        // Start right section for the remaining 3 posts
        echo '<div class="post_right">';

        // Fetch and display the next 3 posts
        while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();
            $title = get_the_title();
            $time_ago = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';
            $categories = get_the_category();
            $featured_image = get_the_post_thumbnail_url($post_id, 'full'); // Get featured image URL
            $category_names = array_map(function ($cat) {
                return $cat->name;
            }, $categories);
        ?>
            <div class="post-item">
                <?php if ($featured_image): ?>
                    <a href="<?php echo get_the_permalink($post_id); ?>"><img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($title); ?>" class="featured-image" /></a>
                <?php endif; ?>
                <div class="post-content">
                    <p class="post_duration_ago"><?php echo esc_html($time_ago); ?></p>
                    <h3><a href="<?php echo get_the_permalink($post_id); ?>"><?php echo esc_html($title); ?></a></h3>
                    <p class="post-categories"><?php echo implode(', ', $category_names); ?></p>
                </div>
            </div>
        <?php
        endwhile;

        echo '</div>'; // Close post_right
    else :
        echo '<p>No posts found in this category.</p>';
    endif;

    echo '</div>'; // Close post_data_replay

    wp_reset_postdata(); // Reset query data
    return ob_get_clean();
}
add_shortcode('post_news', 'func_pre_league_post_data');


// League Header info
function func_league_tournament_head($atts)
{
    ob_start();

    $atts = shortcode_atts(array(
        'slug' => '',
        'season' => '',
    ), $atts, 'league_head');

    $league_slug = sanitize_title($atts['slug']);

    $term = get_term_by('slug', $league_slug, 'sp_league');

    if ($term && !is_wp_error($term)) {

        $league_logo_id = get_term_meta($term->term_id, 'league_logo', true);
        $sponsor_logo_id = get_term_meta($term->term_id, 'league_sponsor_logo', true);

        $league_logo = wp_get_attachment_url($league_logo_id);
        $sponsor_logo = wp_get_attachment_url($sponsor_logo_id);

        $season_title = get_term_meta($term->term_id, 'league_season_title_span', true);
        $start_date = get_term_meta($term->term_id, 'league_start_date', true);
        $anchor1_link = get_term_meta($term->term_id, 'anchor1_link', true);
        $anchor1_name = get_term_meta($term->term_id, 'anchor1_name', true);
        $anchor2_link = get_term_meta($term->term_id, 'anchor2_link', true);
        $anchor2_name = get_term_meta($term->term_id, 'anchor2_name', true);
        $anchor3_link = get_term_meta($term->term_id, 'anchor3_link', true);
        $anchor3_name = get_term_meta($term->term_id, 'anchor3_name', true);

        ?>
        <div class="team_main_head">
            <div class="team_heads">
                <div class="team_heads_l">
                    <div class="ournment_logo">
                        <img src="<?php echo esc_url($league_logo); ?>" alt="League Logo">
                    </div>
                    <div class="team_details">
                        <h5><span><?php echo esc_html($season_title); ?></span> <?php echo esc_html($atts['season']); ?></h5>
                        <h2><?php echo esc_html($term->name); ?></h2>
                        <h6><?php echo esc_html($start_date); ?></h6>
                        <ul class="team_details_btns">
                            <li>
                                <a href="<?php echo esc_url($anchor1_link); ?>">
                                    <?php echo esc_html($anchor1_name); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url($anchor2_link); ?>">
                                    <?php echo esc_html($anchor2_name); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url($anchor3_link); ?>">
                                    <?php echo esc_html($anchor3_name); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="sponsor_logo team_heads_r">
                    <img decoding="async" src="<?php echo esc_url($sponsor_logo); ?>" alt="Sponsor Logo">
                </div>
            </div>
        </div>
    <?php
    } else {
        echo '<p>No term found for the league slug "' . esc_html($atts['slug']) . '".</p>';
    }

    return ob_get_clean();
}
add_shortcode('league_head', 'func_league_tournament_head');

// League tabs data
function func_league_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'league',
        'posts_per_page' => -1, // Fetch all posts that match the criteria
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'league_category',
                'field'    => 'slug',
                'terms'    => array(
                    'overview',
                    'results',
                    'schedule-and-fixtures',
                    'replays',
                    'teams',
                    'prize-pool',
                    'faqs',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Prepare tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active';

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = esc_html(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'league_category');

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    // Create tab
                    $tabs .= '<div class="tab ' . $active_class . '&nbsp;' . $category_slug . ' " data-target="' . $category_slug . '">' . $category_name . '</div>';

                    // Create panel
                    $panels .= '<div class="' . $category_slug . ' panel ' . $active_class . '">';
                    $panels .= '<h3>' . $post_title . '</h3>';
                    $panels .= '<div>' . $post_content . '</div>';
                    $panels .= '<div>' . get_field('shortcode_place') . '</div>';

                    $panels .= '</div>';

                    // Reset active class after first iteration
                    $active_class = '';
                }
            }
        }

        // Output the tab structure
        echo '<div class="league_content">';
        echo '<div class="container">';
        echo '<div class="tabs">' . $tabs . '</div>';
        echo '<div id="panels">' . $panels . '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No posts found in the specified categories.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('league_tabs_page', 'func_league_tabs_page');

// League pre league Tabs
function func_pre_league_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'preleague',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'preleague_category', // Make sure this is the correct taxonomy
                'field'    => 'slug',
                'terms'    => array(
                    'overview',
                    'key-dates',
                    'locations',
                    'schedule',
                    'news',
                    'price-pool',
                    'faqs',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Prepare tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active'; // Set active class for the first tab/panel

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = esc_html(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'preleague_category'); // Correct taxonomy

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    // Create tab
                    $tabs .= '<div class="tab ' . $active_class . ' ' . $category_slug . '" data-target="' . $category_slug . '">' . $category_name . '</div>';

                    // Create panel
                    $panels .= '<div class="' . $category_slug . ' panel ' . $active_class . '">';
                    $panels .= '<h3>' . $post_title . '</h3>';
                    $panels .= '<div>' . $post_content . '</div>';

                    // Check if ACF field exists and is not empty
                    $shortcode_place = get_field('shortcode_place');
                    if ($shortcode_place) {
                        $panels .= '<div>' . $shortcode_place . '</div>';
                    }

                    $panels .= '</div>';

                    // Reset active class after first iteration
                    $active_class = ''; // Only the first tab/panel should have the active class
                }
            }
        }

        // Output the tab structure
        echo '<div class="league_content">';
        echo '<div class="container">';
        echo '<div class="tabs">' . $tabs . '</div>';
        echo '<div id="panels">' . $panels . '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No posts found in the specified categories.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('pre_league_tabs_page', 'func_pre_league_tabs_page');

// League Post tabs data
function func_post_league_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'postleague',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'postleague_category', // Make sure this is the correct taxonomy
                'field'    => 'slug',
                'terms'    => array(
                    'overview',
                    'key-dates',
                    'locations',
                    'schedule',
                    'news',
                    'price-pool',
                    'faqs',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Prepare tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active'; // Set active class for the first tab/panel

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = esc_html(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'postleague_category'); // Correct taxonomy

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    // Create tab
                    $tabs .= '<div class="tab ' . $active_class . ' ' . $category_slug . '" data-target="' . $category_slug . '">' . $category_name . '</div>';

                    // Create panel
                    $panels .= '<div class="' . $category_slug . ' panel ' . $active_class . '">';
                    $panels .= '<h3>' . $post_title . '</h3>';
                    $panels .= '<div>' . $post_content . '</div>';

                    // Check if ACF field exists and is not empty
                    $shortcode_place = get_field('shortcode_place');
                    if ($shortcode_place) {
                        $panels .= '<div>' . $shortcode_place . '</div>';
                    }

                    $panels .= '</div>';

                    // Reset active class after first iteration
                    $active_class = ''; // Only the first tab/panel should have the active class
                }
            }
        }

        // Output the tab structure
        echo '<div class="league_content">';
        echo '<div class="container">';
        echo '<div class="tabs">' . $tabs . '</div>';
        echo '<div id="panels">' . $panels . '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No posts found in the specified categories.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('post_league_tabs_page', 'func_post_league_tabs_page');

// Mid League post data
function func_mid_league_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'midleague',
        'posts_per_page' => -1, // Fetch all posts that match the criteria
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'midleague_category',
                'field'    => 'slug',
                'terms'    => array(
                    'overview',
                    'schedule-and-fixture',
                    'replays',
                    'teams',
                    'prize-pool',
                    'faqs',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Prepare tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active';

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = esc_html(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'midleague_category');

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    // Create tab
                    $tabs .= '<div class="mid_leagues_tabs tab ' . $active_class . '&nbsp;' . $category_slug . ' " data-target="' . $category_slug . '">' . $category_name . '</div>';

                    // Create panel
                    $panels .= '<div class="mid_leagues_panels ' . $category_slug . ' panel ' . $active_class . '">';
                    $panels .= '<h3>' . $post_title . '</h3>';
                    $panels .= '<div>' . $post_content . '</div>';
                    $panels .= '<div>' . get_field('shortcode_place') . '</div>';

                    $panels .= '</div>';

                    // Reset active class after first iteration
                    $active_class = '';
                }
            }
        }

        // Output the tab structure
        echo '<div class="league_content">';
        echo '<div class="container">';
        echo '<div class="tabs">' . $tabs . '</div>';
        echo '<div id="panels">' . $panels . '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No posts found in the specified categories.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('mid_league_tabs_page', 'func_mid_league_tabs_page');

function func_league_templates()
{
    if (is_page_template('league-template.php')) {
        return do_shortcode('[league_tabs_page]');
    }

    if (is_page_template('mid_league-template.php')) {
        return do_shortcode('[mid_league_tabs_page]');
    }

    if (is_page_template('post_league-template.php')) {
        return do_shortcode('[post_league_tabs_page]');
    }

    if (is_page_template('pre_league-template.php')) {
        return do_shortcode('[pre_league_tabs_page league="npl"]');
    }
}
add_shortcode('league_templates', 'func_league_templates');

// Pre League
function func_pre_league_schedule($atts)
{
    ob_start();

    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $league = get_terms(['taxonomy' => 'sp_league', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1));

    $atts = shortcode_atts(array(
        'league' => '',
    ), $atts, 'team_list');

    $league_slug = sanitize_title($atts['league']);

    ?>
    <form id="eventFilterForm" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="round-select" name="round[]">
                    <option value="">Select Round</option>
                    <?php
                    $unique_rounds = [];
                    if ($tournament_query->have_posts()) {
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $round_id = get_the_ID();
                            $rounds = get_post_meta(get_the_ID(), 'sp_labels', true) ?: [];
                            foreach ((array) $rounds as $round_group) {
                                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $index => $round) {
                            echo '<option value="' . esc_html($round_id) . '">' . esc_html($round) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="match_type_select" name="match_type[]">
                    <option value="">Select Match Type</option>
                    <?php
                    if ($tournament_query->have_posts()) {
                        $unique_match_types = [];
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $tournament_id = get_the_ID();
                            $match_type = get_post_meta($tournament_id, 'match-type', true) ?: 'Unknown';
                            if (!in_array($match_type, $unique_match_types)) {
                                $unique_match_types[] = $match_type;
                                echo '<option value="' . esc_html($tournament_id) . '">' . esc_html($match_type) . '</option>';
                            }
                        }
                        wp_reset_postdata(); // Reset after loop
                    } else {
                        echo '<option>No events found</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="state-select" name="state[]">
                    <option value="">Select State</option>
                    <?php
                    $unique_states = [];
                    if ($team_query->have_posts()) {
                        while ($team_query->have_posts()) {
                            $team_query->the_post();
                            $state_id = get_the_ID();
                            $state = get_post_meta(get_the_ID(), 'state', true);
                            if ($state && !in_array($state, $unique_states)) {
                                $unique_states[] = $state;
                                echo '<option value="' . $state_id . '">' . esc_html($state) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="venue-select" name="venue[]">
                    <option value="">Select Venue</option>
                    <?php
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>


    <?php
    $args_tourna = new WP_Query(array(
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'sp_league',
                'field'    => 'slug',
                'terms'    => $league_slug,
            ),

        ),
    ));

    if ($args_tourna->have_posts()) {
        while ($args_tourna->have_posts()) {
            $args_tourna->the_post();
            $round_id = get_the_ID();
            $event_metas = get_post_meta(get_the_ID());

            echo "<pre>";
            print_r($event_metas);
            echo "</pre>";
        }
    }

    ?>

    <div id="team-players-table">
        <div class="">
            <div class="round_1">
                <div class="round_1_accordian">
                    <div class="round_main_cont">
                        <div class="round_1_img">
                            <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                        </div>
                        <div class="round_1_content">
                            <h4>Jan 21- 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>

                    <div class="chevron">
                        <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span></span>
                    </div>
                </div>

                <div class="round_inner" style="display: none;">
                    <div class="round_inner_time">
                        <h3>9:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="round_inner_time">
                        <h3>11:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="round_1">
                <div class="round_1_accordian">
                    <div class="round_main_cont">
                        <div class="round_1_img">
                            <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                        </div>
                        <div class="round_1_content">
                            <h4>Jan 21- 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>

                    <div class="chevron">
                        <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span></span>
                    </div>
                </div>

                <div class="round_inner" style="display: none;">
                    <div class="round_inner_time">
                        <h3>9:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="round_inner_time">
                        <h3>11:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>

            <div class="round_1">
                <div class="round_1_accordian">
                    <div class="round_main_cont">
                        <div class="round_1_img">
                            <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                        </div>
                        <div class="round_1_content">
                            <h4>Jan 21- 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>

                    <div class="chevron">
                        <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span></span>
                    </div>
                </div>

                <div class="round_inner" style="display: none;">
                    <div class="round_inner_time">
                        <h3>9:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('pre_league_schedule', 'func_pre_league_schedule');

// Pre league Locations tab
function func_pre_league_locations($atts)
{
    ob_start();
    ?>

    <div class="pre_league_location_main">
        <div class="pre_league_location_inner">
            <div class="pre_league_location_items">
                <h3>New South Wales</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Queensland</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Victoria</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Tasmania</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Westren Australia</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <?php
    return ob_get_clean();
}
add_shortcode('pre_league_location', 'func_pre_league_locations');

// Pre league Key dates
function func_pre_league_keydates()
{
    ob_start();
    ?>
    <div class="pre_league_key_dates_main">
        <div class="pre_league_key_dates_inner">
            <div class="pre_league_key_dates_items">
                <h4>Friday Jan 12th</h4>
                <h2>Online Draft</h2>
                <h5>Register Now</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>MaR 15</h4>
                <h2>Round 1</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>MaR 22</h4>
                <h2>Round 2</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>April 10</h4>
                <h2>Round 3</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>April 22</h4>
                <h2>Round 4</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>May 5</h4>
                <h2>Round 5</h2>
                <h5>Upcoming</h5>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pre_league_keydates', 'func_pre_league_keydates');


// Post League Schedule tab
function func_post_league_schedule($atts)
{
    ob_start();

    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $league = get_terms(['taxonomy' => 'sp_league', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1));

    $atts = shortcode_atts(array(
        'league' => '',
    ), $atts, 'team_list');

    $league_slug = sanitize_title($atts['league']);

    ?>
    <form id="eventFilterForm" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="round-select" name="round[]">
                    <option value="">Select Round</option>
                    <?php
                    $unique_rounds = [];
                    if ($tournament_query->have_posts()) {
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $round_id = get_the_ID();
                            $rounds = get_post_meta(get_the_ID(), 'sp_labels', true) ?: [];
                            foreach ((array) $rounds as $round_group) {
                                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $index => $round) {
                            echo '<option value="' . esc_html($round_id) . '">' . esc_html($round) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="match_type_select" name="match_type[]">
                    <option value="">Select Match Type</option>
                    <?php
                    if ($tournament_query->have_posts()) {
                        $unique_match_types = [];
                        while ($tournament_query->have_posts()) {
                            $tournament_query->the_post();
                            $tournament_id = get_the_ID();
                            $match_type = get_post_meta($tournament_id, 'match-type', true) ?: 'Unknown';
                            if (!in_array($match_type, $unique_match_types)) {
                                $unique_match_types[] = $match_type;
                                echo '<option value="' . esc_html($tournament_id) . '">' . esc_html($match_type) . '</option>';
                            }
                        }
                        wp_reset_postdata(); // Reset after loop
                    } else {
                        echo '<option>No events found</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="state-select" name="state[]">
                    <option value="">Select State</option>
                    <?php
                    $unique_states = [];
                    if ($team_query->have_posts()) {
                        while ($team_query->have_posts()) {
                            $team_query->the_post();
                            $state_id = get_the_ID();
                            $state = get_post_meta(get_the_ID(), 'state', true);
                            if ($state && !in_array($state, $unique_states)) {
                                $unique_states[] = $state;
                                echo '<option value="' . $state_id . '">' . esc_html($state) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="venue-select" name="venue[]">
                    <option value="">Select Venue</option>
                    <?php
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>


    <?php
    $args_tourna = new WP_Query(array(
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'sp_league',
                'field'    => 'slug',
                'terms'    => $league_slug,
            ),

        ),
    ));

    if ($args_tourna->have_posts()) {
        while ($args_tourna->have_posts()) {
            $args_tourna->the_post();
            $round_id = get_the_ID();
            $event_metas = get_post_meta(get_the_ID());

            echo "<pre>";
            print_r($event_metas);
            echo "</pre>";
        }
    }

    ?>

    <div id="team-players-table">
        <div class="">
            <div class="round_1">
                <div class="round_1_accordian">
                    <div class="round_main_cont">
                        <div class="round_1_img">
                            <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                        </div>
                        <div class="round_1_content">
                            <h4>Jan 21- 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>

                    <div class="chevron">
                        <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span></span>
                    </div>
                </div>

                <div class="round_inner" style="display: none;">
                    <div class="round_inner_time">
                        <h3>9:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="round_inner_time">
                        <h3>11:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="round_1">
                <div class="round_1_accordian">
                    <div class="round_main_cont">
                        <div class="round_1_img">
                            <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                        </div>
                        <div class="round_1_content">
                            <h4>Jan 21- 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>

                    <div class="chevron">
                        <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span></span>
                    </div>
                </div>

                <div class="round_inner" style="display: none;">
                    <div class="round_inner_time">
                        <h3>9:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="round_inner_time">
                        <h3>11:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>

            <div class="round_1">
                <div class="round_1_accordian">
                    <div class="round_main_cont">
                        <div class="round_1_img">
                            <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                        </div>
                        <div class="round_1_content">
                            <h4>Jan 21- 24</h4>
                            <h2>Round 1</h2>
                            <h5>SEAFORTH, PANANA</h5>
                        </div>
                    </div>

                    <div class="chevron">
                        <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span></span>
                    </div>
                </div>

                <div class="round_inner" style="display: none;">
                    <div class="round_inner_time">
                        <h3>9:30 AM</h3>
                    </div>
                    <div class="">
                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Home Team</th>
                                    <th></th>
                                    <th>Road Team</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Match 1</td>
                                    <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                    <td>0 Vs 0</td>
                                    <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                    <td>The Jar - South Melbourne</td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <span class="span1">Complete</span> | <span>Gallery</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('post_league_schedule', 'func_post_league_schedule');

// Post league location tab
function func_post_league_locations($atts)
{
    ob_start();
    ?>

    <div class="pre_league_location_main">
        <div class="pre_league_location_inner">
            <div class="pre_league_location_items">
                <h3>New South Wales</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Queensland</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Victoria</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Tasmania</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
            <div class="pre_league_location_items">
                <h3>Westren Australia</h3>

                <div class="pre_league_location_items_sec1">
                    <h3>Teams</h3>
                    <h4>2 Guaranteed FINAL SPOTS </h4>
                    <h5>Teams</h5>
                </div>
                <div class="pre_league_location_items_sec2">
                    <h3>Venue</h3>
                    <div class="items2_inner1">
                        <span>Voyager Tennis</span>
                        <span>Seaforth</span> 
                    </div>
                    <div class="items2_inner2">
                        <span>Direction</span>
                        <span>Visit Website</span>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <?php
    return ob_get_clean();
}
add_shortcode('post_league_location', 'func_post_league_locations');

// Post league key dates
function func_post_league_keydates()
{
    ob_start();
    ?>
    <div class="pre_league_key_dates_main">
        <div class="pre_league_key_dates_inner">
            <div class="pre_league_key_dates_items">
                <h4>Friday Jan 12th</h4>
                <h2>Online Draft</h2>
                <h5>Register Now</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>MaR 15</h4>
                <h2>Round 1</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>MaR 22</h4>
                <h2>Round 2</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>April 10</h4>
                <h2>Round 3</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>April 22</h4>
                <h2>Round 4</h2>
                <h5>Upcoming</h5>
            </div>
            <div class="pre_league_key_dates_items">
                <h4>May 5</h4>
                <h2>Round 5</h2>
                <h5>Upcoming</h5>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('post_league_keydates', 'func_post_league_keydates');


// mid league player list tab
function func_mid_league_player_lists()
{
    ob_start();

    // Query players sorted by player_points in descending order
    $players = new WP_Query([
        'post_type' => 'sp_player',
        'posts_per_page' => 15,
        'meta_key' => 'player_points',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
    ]);

    if ($players->have_posts()) {

        echo '<h2 class="heading_underline">Player Ranking & Stats</h2>';


        echo '<table class="team-players-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>#</th>';
        echo '<th>Name</th>';
        echo '<th>PWR</th>';
        echo '<th>Won</th>';
        echo '<th>Lost</th>';
        echo '<th>F</th>';
        echo '<th>A</th>';
        echo '<th>PTS</th>';
        echo '<th>STRK</th>';
        echo '<th>DUPR</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $counter = 1;

        while ($players->have_posts()) {
            $players->the_post();

            $player_id = get_the_ID();
            $player_name = get_the_title();
            $player_image = get_the_post_thumbnail_url($player_id, 'thumbnail');

            // Helper function for meta values
            $meta = function ($key) use ($player_id) {
                return get_post_meta($player_id, $key, true);
            };

            echo '<tr>';
            echo '<td>' . esc_html($counter++) . '</td>';
            echo '<td class="player_name">';
            echo '<img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '" style="border-radius: 50%;">';
            echo '<h4>' . esc_html($player_name) . '</h4>';
            echo '</td>';
            echo '<td>' . esc_html($meta('player_pwr')) . '</td>';
            echo '<td>' . esc_html($meta('player_won')) . '</td>';
            echo '<td>' . esc_html($meta('player_lost')) . '</td>';
            echo '<td>' . esc_html($meta('player_f')) . '</td>';
            echo '<td>' . esc_html($meta('player_a')) . '</td>';
            echo '<td>' . esc_html($meta('player_points')) . '</td>';
            echo '<td>' . esc_html($meta('player_strick')) . '</td>';
            echo '<td>' . esc_html($meta('player_dupr')) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        wp_reset_postdata();
    } else {
        echo '<p>No players found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('mid_league_player_lists', 'func_mid_league_player_lists');

// mid league replays tab
function func_mid_league_replays()
{
    ob_start();

    // Query for events with taxonomy `event-type` set to `completed`
    $event_args = new WP_Query([
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'order' => 'DESC',
        'tax_query' => [
            [
                'taxonomy' => 'event-type',
                'field' => 'slug',
                'terms' => 'completed',
            ],
        ],
    ]);

    echo '<div class="replays_data_videos_main">';

    if ($event_args->have_posts()) {
        echo '<div class="replays_data_videos_inner">';

        while ($event_args->have_posts()) {
            $event_args->the_post();
            $post_id = get_the_ID();
            $event_metas = get_post_meta($post_id);



            $featured_image = get_the_post_thumbnail_url($post_id, 'full');
            $video_url = isset($event_metas['sp_video'][0]) ? esc_url($event_metas['sp_video'][0]) : '';

    ?>
            <div class="replays_data_videos">
                <div class="image_sec">
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                </div>
                <div class="content_sec">
                    <h3><?php echo esc_html(get_the_title()); ?></h3>
                    <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/PlayButton-1.svg" class="play-button" data-video-id="video-<?php echo esc_attr($post_id); ?>" />
                </div>
            </div>
            <div id="video-<?php echo esc_attr($post_id); ?>" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <iframe width="480" height="300" src="<?php echo $video_url; ?>" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
    <?php
        }

        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No completed matches found.</p>';
    }
    echo '</div>';

    // Include the modal script
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            const playButtons = document.querySelectorAll('.play-button');

            playButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const videoId = this.dataset.videoId;
                    const modal = document.getElementById(videoId);
                    modal.style.display = 'block';
                });
            });

            modals.forEach(modal => {
                const closeBtn = modal.querySelector('.close');
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });

                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('mid_league_replays', 'func_mid_league_replays');

// mid league Gallery tab
function func_mid_league_gallery()
{
    ob_start();

    $event_args = new WP_Query([
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'order' => 'DESC',
        'tax_query' => [
            [
                'taxonomy' => 'event-type',
                'field' => 'slug',
                'terms' => 'completed',
            ],
        ],
    ]);

    if ($event_args->have_posts()) {
        echo '<div class="gallery_data_main">';
        echo '<h2 class="heading_underline">Gallery</h2>';
        echo '<div class="gallery_data_inner">';
        echo "<ul>";
        while ($event_args->have_posts()) {
            $event_args->the_post();
            $post_id = get_the_ID();
    ?>
            <?php
            $images = get_field('event_gallery', $post_id);
            $size = 'full'; // Change to desired size (thumbnail, medium, large, etc.)

            if ($images): ?>

                <?php foreach ($images as $image): ?>
                    <li>

                        <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />

                    </li>
                <?php endforeach; ?>

            <?php endif; ?>
        <?php
        }
        echo "</ul>";
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No completed events found.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('mid_league_gallery', 'func_mid_league_gallery');

// Mid league Schedule tabs
function func_mid_league_schedule($atts)
{
    ob_start();

    $atts = shortcode_atts([
        'league' => '',
    ], $atts, 'league_list');

    $league_slug = sanitize_title($atts['league']);

    $args_tourna = new WP_Query([
        'post_type' => 'sp_tournament',
        'posts_per_page' => 1,
        'tax_query' => [
            [
                'taxonomy' => 'sp_league',
                'field'    => 'slug',
                'terms'    => $league_slug,
            ],
        ],
    ]);

    if ($args_tourna->have_posts()) {
        while ($args_tourna->have_posts()) {
            $args_tourna->the_post();
            $round_id = get_the_ID();
        ?>
            <div id="team-players-table">
                <div class="round_1">
                    <div class="round_1_accordian">
                        <div class="round_main_cont">
                            <div class="round_1_img">
                                <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="Round 1 Image" />
                            </div>
                            <div class="round_1_content">
                                <h4>Jan 21-24</h4>
                                <h2>Round 1</h2>
                                <h5>SEAFORTH, PANANA</h5>
                            </div>
                        </div>
                        <div class="chevron">
                            <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span>
                        </div>
                    </div>
                    <div class="round_inner" style="display: none;">
                        <div class="round_inner_time">
                            <h3>9:30 AM</h3>
                        </div>
                        <div>
                            <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Home Team</th>
                                        <th></th>
                                        <th>Road Team</th>
                                        <th>Venue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Match 1</td>
                                        <td><img src="" alt="Home Team Logo" /> Catepillar</td>
                                        <td>0 Vs 0</td>
                                        <td><img src="" alt="Road Team Logo" /> Turtles</td>
                                        <td>The Jar - South Melbourne</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <span class="span1">Complete</span> | <span>Gallery</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><?php
                }
            } else {
                echo '<p>No tournaments found for this league.</p>';
            }

            wp_reset_postdata();
            return ob_get_clean();
        }
        add_shortcode('mid_league_schedule', 'func_mid_league_schedule');


        function func_mid_league_overview($atts)
        {
            ob_start();

            // Extract and sanitize attributes
            // $atts = shortcode_atts([ 
            //     'match_id' => '5232', 
            // ], $atts, 'mid_league_overview');

            // $match_id = sanitize_text_field($atts['match_id']);

            // Set up query arguments
            $query_args = [
                'post_type' => 'sp_event',
                'posts_per_page' => -1,
                'order' => 'DESC',
            ];

            // Add meta_query if match_id is provided
            // if (!empty($match_id)) {
            //     $query_args['meta_query'] = [
            //         [
            //             'key' => 'match_id',
            //             'value' => $match_id,
            //             'compare' => '=',
            //         ],
            //     ];
            // }

            $event_args = new WP_Query($query_args);

            if ($event_args->have_posts()) {
                echo '<div class="mid-league-overview-container">';

                while ($event_args->have_posts()) {
                    $event_args->the_post();
                    $post_id = get_the_ID();
                    $title = get_the_title($post_id);
                    $event_metas = get_post_meta($post_id);

                    // Render event overview
                    ?>
                    <div class="event-overview">
                        <div class="">
                            <div class="">
                                <div class="image_sec">
                                    <img src="" />
                                    <!-- feature image get -->
                                </div>
                                <div class="image_content">
                                    <h3>Watch Live</h3>
                                    <p><?php echo wp_trim_words( get_the_content(), 50, ''); ?></p>
                                </div>
                            </div>
                            <div class="">
                                <div class="">
                                    <div class="">
                                        <h3>match</h3>
                                        <h2>Men Singles</h2>
                                        <h5>Then jar - South Melbourne</h5>
                                    </div>
                                    <div class="">
                                        <img src="" /> 
                                        <!-- match type icon -->
                                    </div>
                                </div>
                                <div class="">
                                    <h4>players</h4>
                                    <h3>Team vs Team</h3>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php
                }

            echo '</div>';
        } else {
            echo '<p>No events found matching the specified criteria.</p>';
        }

        // Reset post data
        wp_reset_postdata();
        return ob_get_clean();
    }
add_shortcode('mid_league_overview', 'func_mid_league_overview');
