<?php

function func_header_tags()
{
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

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

        jQuery('.league_content #panels .panel:first-child, .league_content .tabs .tab:first-child').addClass('active');


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



/*
 * Haris function code start
 * */

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


function func_league_tabs_result($atts)
{
    ob_start();

    // Get all needed data first
    $seasons = get_terms(['taxonomy' => 'sp_season', 'orderby' => 'name', 'hide_empty' => false]);
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $events = new WP_Query(['post_type' => 'sp_event', 'posts_per_page' => -1, 'order' => 'ASC']);

    // Get unique states and rounds
    $unique_states = [];
    $unique_rounds = [];
    if ($events->have_posts()) {
        while ($events->have_posts()) {
            $events->the_post();

            // Get unique states
            $state = get_post_meta(get_the_ID(), 'state', true);
            if (!empty($state) && !in_array($state, $unique_states)) {
                $unique_states[] = $state;
            }

            // Get unique rounds
            $rounds = maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)) ?: [];
            foreach ((array) $rounds as $round_group) {
                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
            }
        }
        $unique_rounds = array_unique($unique_rounds);
        $events->rewind_posts();
    }

    // Display filters
?>
    <form action="" class="filter_form player_info_venue">
        <div class="filter_group form-group_main">
            <div class="filter_item form-group">
                <select id="filter-season">
                    <option value=''>Select Season</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season->name); ?>"><?php echo esc_html($season->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-state">
                    <option value=''>Select State</option>
                    <?php foreach ($unique_states as $state): ?>
                        <option value="<?php echo esc_attr($state); ?>"><?php echo esc_html($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-venue">
                    <option value=''>Select Venue</option>
                    <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo esc_attr($venue->name); ?>"><?php echo esc_html($venue->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-round">
                    <option value=''>Select Round</option>
                    <?php foreach ($unique_rounds as $round): ?>
                        <option value="<?php echo esc_attr(sanitize_title($round)); ?>"><?php echo esc_html($round); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        // Organize matches by round
        $matches_by_round = [];
        if ($events->have_posts()) {
            while ($events->have_posts()) {
                $events->the_post();

                // Prepare match data
                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true) ?: get_the_date('Y-m-d');

                $match_data = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'date' => date('F j, Y', strtotime($match_date)),
                    'time' => get_post_time('g:i A', false, get_the_ID(), true),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', ['fields' => 'names']),
                    'season' => wp_get_post_terms(get_the_ID(), 'sp_season', ['fields' => 'names']),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', ['fields' => 'names']),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => get_the_title($tournament_associate_id),
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'raw_date' => $match_date,
                    'state' => get_post_meta(get_the_ID(), 'state', true),
                ];

                // Get round name
                $rounds = $match_data['rounds'];
                $round_name = empty($rounds) ? 'Uncategorized' : (is_array($rounds) ? implode(', ', $rounds) : $rounds);

                if (!isset($matches_by_round[$round_name])) {
                    $matches_by_round[$round_name] = [];
                }

                $matches_by_round[$round_name][] = $match_data;
            }

            // Sort rounds by date (newest first)
            uasort($matches_by_round, function ($a, $b) {
                return strtotime($b[0]['raw_date']) - strtotime($a[0]['raw_date']);
            });

            // Display matches by round
            foreach ($matches_by_round as $round_name => $matches) {
                $round_slug = sanitize_title($round_name);

                // Get venues for this round
                $round_venues = [];
                foreach ($matches as $match) {
                    foreach ($match['venue'] as $venue) {
                        if (!in_array($venue, $round_venues)) {
                            $round_venues[] = $venue;
                        }
                    }
                }
                $venue_display = !empty($round_venues) ? implode(', ', $round_venues) : 'Venue not specified';
        ?>
                <div class="rounds_wise_categories" data-round_cate="<?php echo esc_attr($round_slug); ?>">
                    <div class="rounds_wise_categories_inner">
                        <div class="rounds_wise_img">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/npl-leagues.png" alt="">
                        </div>
                        <div class="rounds_wise_cont">
                            <h2><?php echo esc_html($round_name); ?></h2>
                            <h3><?php echo esc_html($venue_display); ?></h3>
                        </div>
                    </div>
                    <div class="round-chevron"><i class="fas fa-chevron-down"></i></div>
                    <div style="clear: both;"></div>
                </div>

                <div class="round-matches-container" data-round_match="<?php echo esc_attr($round_slug); ?>" style="display:none;">
                    <?php
                    // Group matches by date
                    $matches_by_date = [];
                    foreach ($matches as $match) {
                        $matches_by_date[$match['date']][] = $match;
                    }
                    krsort($matches_by_date);

                    foreach ($matches_by_date as $date => $date_matches):
                        $first_match = $date_matches[0];
                    ?>
                        <div class="match_date_wisess match-row"
                            data-season="<?php echo esc_attr($match['season'][0] ?? ''); ?>"
                            data-state="<?php echo esc_attr($match['state'] ?? ''); ?>"
                            data-venue="<?php echo esc_attr($match['venue'][0] ?? ''); ?>"
                            data-round="<?php echo esc_attr($round_slug); ?>">
                            <h2 class="heading_underline"><?php echo esc_html($date); ?></h2>

                            <table class="team-players-table">
                                <thead>
                                    <tr>
                                        <th>Match No</th>
                                        <th>Team 1</th>
                                        <th>Score</th>
                                        <th>Team 2</th>
                                        <th>Venue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tbody>
                                    <?php foreach ($date_matches as $index => $match):
                                        $players_data = $match['players'] ?? []; // Fetch players data array
                                        $team_ids = array_keys($players_data); // Extract team IDs (e.g., 3070, 3044)

                                        // Fetch team names using team IDs
                                        $team1_name = isset($team_ids[0]) ? esc_html(get_the_title($team_ids[0])) : 'Team 1';
                                        $team2_name = isset($team_ids[1]) ? esc_html(get_the_title($team_ids[1])) : 'Team 2';

                                        // Get scores from the points data in players array
                                        $team1_score = isset($team_ids[0], $players_data[$team_ids[0]]['points'])
                                            ? $players_data[$team_ids[0]]['points']
                                            : '0';
                                        $team2_score = isset($team_ids[1], $players_data[$team_ids[1]]['points'])
                                            ? $players_data[$team_ids[1]]['points']
                                            : '0';

                                        // Fallback to results array if points not found (optional)
                                        if ($team1_score === '0' && $team2_score === '0' && isset($match['results'][0])) {
                                            $team1_score = $match['results'][0]['team_1_score'] ?? '0';
                                            $team2_score = $match['results'][0]['team_2_score'] ?? '0';
                                        }

                                        $teams_scores = [];

                                        if (!empty($match['results'])) {
                                            foreach ($match['results'] as $team_id => $result) {
                                                $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                                            }
                                        }

                                        $team_ids = array_keys($teams_scores);

                                    ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $team1_name; ?></td>

                                            <td><?php echo esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0'); ?></td>

                                            <td><?php echo $team2_name; ?></td>
                                            <td><?php echo esc_html(implode(', ', $match['venue'])); ?></td>
                                        </tr>
                                        <?php
                                        echo '<tr>';
                                        echo '<td colspan="5" class="tr_2_styling"> ' . $match['event_type'][0] . ' | <a href="' . site_url() . '/gallery">Gallery</td>';
                                        echo '</tr>';
                                        ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                        </div>



                    <?php endforeach; ?>
                </div>
        <?php
            }
        }
        wp_reset_postdata();
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            const filters = {
                season: document.getElementById('filter-season'),
                state: document.getElementById('filter-state'),
                venue: document.getElementById('filter-venue'),
                round: document.getElementById('filter-round')
            };

            // Tab switching
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.style.display = 'none';
                    });

                    // Show the target tab content
                    document.getElementById(target).style.display = 'block';

                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Add active class to the clicked tab
                    this.classList.add('active');
                });
            });

            // Initial tab setup
            if (tabs.length > 0) {
                tabs[0].click(); // Activate the first tab
            }

            // Dropdown filter functionality
            function filterTable() {
                const season = filters.season.value.trim().toLowerCase();
                const state = filters.state.value.trim().toLowerCase();
                const venue = filters.venue.value.trim().toLowerCase();
                const round = filters.round.value.trim().toLowerCase();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season')?.toLowerCase() || '';
                    const matchState = row.getAttribute('data-state')?.toLowerCase() || '';
                    const matchVenue = row.getAttribute('data-venue')?.toLowerCase() || '';
                    const matchRound = row.getAttribute('data-round')?.toLowerCase() || '';

                    const show =
                        (!season || matchSeason === season) &&
                        (!state || matchState === state) &&
                        (!venue || matchVenue === venue) &&
                        (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';

                    // Hide empty date sections
                    const dateSection = row.closest('.main_live_tournas');
                    if (dateSection) {
                        const visibleRows = dateSection.querySelectorAll('.match-row[style=""]').length;
                        dateSection.style.display = visibleRows > 0 ? '' : 'none';
                    }
                });
            }

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });

            filterTable(); // Initial filter on page load

            // Accordion functionality
            document.querySelectorAll('.rounds_wise_categories').forEach(header => {
                header.addEventListener('click', function() {
                    const roundSlug = this.getAttribute('data-round_cate');
                    const container = document.querySelector(`.round-matches-container[data-round_match="${roundSlug}"]`);
                    const chevron = this.querySelector('.round-chevron i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        container.style.display = 'none';
                        chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
        });
    </script>
<?php

    return ob_get_clean();
}
add_shortcode('league_tabs_result', 'func_league_tabs_result');


function func_league_tabs_schedule() {
    ob_start();

    // Get current date for comparison
    $current_date = current_time('Y-m-d');

    // Get all needed data first
    $seasons = get_terms([
        'taxonomy' => 'sp_season', 
        'orderby' => 'name', 
        'hide_empty' => false
    ]);
    
    $venues = get_terms([
        'taxonomy' => 'sp_venue', 
        'orderby' => 'name', 
        'hide_empty' => false
    ]);
    
    // Query only future events
    $events = new WP_Query([
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => '_wp_old_date',
                'value' => $current_date,
                'compare' => '>=',
                'type' => 'DATE'
            ]
        ]
    ]);

    // Get unique states and rounds
    $unique_states = [];
    $unique_rounds = [];
    
    if ($events->have_posts()) {
        while ($events->have_posts()) {
            $events->the_post();

            // Get unique states
            $state = get_post_meta(get_the_ID(), 'state', true);
            if (!empty($state) && !in_array($state, $unique_states)) {
                $unique_states[] = $state;
            }

            // Get unique rounds
            $rounds = maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)) ?: [];
            foreach ((array) $rounds as $round_group) {
                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
            }
        }
        $unique_rounds = array_unique($unique_rounds);
        $events->rewind_posts();
    }

    // Display filters
    ?>
    <form action="" class="filter_form player_info_venue">
        <div class="filter_group form-group_main">
            <div class="filter_item form-group">
                <select id="filter-seasons">
                    <option value=''>Select Season</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season->name); ?>"><?php echo esc_html($season->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-states">
                    <option value=''>Select State</option>
                    <?php foreach ($unique_states as $state): ?>
                        <option value="<?php echo esc_attr($state); ?>"><?php echo esc_html($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-venues">
                    <option value=''>Select Venue</option>
                    <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo esc_attr($venue->name); ?>"><?php echo esc_html($venue->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-rounds">
                    <option value=''>Select Round</option>
                    <?php foreach ($unique_rounds as $round): ?>
                        <option value="<?php echo esc_attr(sanitize_title($round)); ?>"><?php echo esc_html($round); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        // Organize matches by round
        $matches_by_round = [];
        
        if ($events->have_posts()) {
            while ($events->have_posts()) {
                $events->the_post();

                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true) ?: get_the_date('Y-m-d');
                
                // Skip if date is before today (additional safety check)
                if (strtotime($match_date) < strtotime($current_date)) {
                    continue;
                }

                // Prepare match data
                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);

                $match_data = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'date' => date('F j, Y', strtotime($match_date)),
                    'time' => get_post_time('g:i A', false, get_the_ID(), true),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', ['fields' => 'names']),
                    'season' => wp_get_post_terms(get_the_ID(), 'sp_season', ['fields' => 'names']),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', ['fields' => 'names']),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => get_the_title($tournament_associate_id),
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'raw_date' => $match_date,
                    'state' => get_post_meta(get_the_ID(), 'state', true),
                ];

                // Get round name
                $rounds = $match_data['rounds'];
                $round_name = empty($rounds) ? 'Uncategorized' : (is_array($rounds) ? implode(', ', $rounds) : $rounds);

                if (!isset($matches_by_round[$round_name])) {
                    $matches_by_round[$round_name] = [];
                }

                $matches_by_round[$round_name][] = $match_data;
            }

            // Sort rounds by date (nearest first)
            uasort($matches_by_round, function ($a, $b) {
                return strtotime($a[0]['raw_date']) - strtotime($b[0]['raw_date']);
            });

            // Display matches by round
            if (empty($matches_by_round)) {
                echo '<p>No upcoming events scheduled.</p>';
            } else {
                foreach ($matches_by_round as $round_name => $matches) {
                    $round_slug = sanitize_title($round_name);

                    // Get venues for this round
                    $round_venues = [];
                    foreach ($matches as $match) {
                        foreach ($match['venue'] as $venue) {
                            if (!in_array($venue, $round_venues)) {
                                $round_venues[] = $venue;
                            }
                        }
                    }
                    $venue_display = !empty($round_venues) ? implode(', ', $round_venues) : 'Venue not specified';
                    ?>
                    <div class="rounds_wise_categories" data-round_cate="<?php echo esc_attr($round_slug); ?>">
                        <div class="rounds_wise_categories_inner">
                            <div class="rounds_wise_img">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/npl-leagues.png" alt="">
                            </div>
                            <div class="rounds_wise_cont">
                                <h2><?php echo esc_html($round_name); ?></h2>
                                <h3><?php echo esc_html($venue_display); ?></h3>
                            </div>
                        </div>
                        <div class="round-chevron"><i class="fas fa-chevron-down"></i></div>
                        <div style="clear: both;"></div>
                    </div>

                    <div class="round-matches-container" data-round_match="<?php echo esc_attr($round_slug); ?>" style="display:none;">
                        <?php
                        // Group matches by date
                        $matches_by_date = [];
                        foreach ($matches as $match) {
                            $matches_by_date[$match['date']][] = $match;
                        }
                        // Sort dates chronologically
                        ksort($matches_by_date);

                        foreach ($matches_by_date as $date => $date_matches):
                            $first_match = $date_matches[0];
                        ?>
                            <div class="match_date_wisess match-row"
                                data-season="<?php echo esc_attr($first_match['season'][0] ?? ''); ?>"
                                data-state="<?php echo esc_attr($first_match['state'] ?? ''); ?>"
                                data-venue="<?php echo esc_attr($first_match['venue'][0] ?? ''); ?>"
                                data-round="<?php echo esc_attr($round_slug); ?>">
                                <h2 class="heading_underline"><?php echo esc_html($date); ?></h2>

                                <table class="team-players-table">
                                    <thead>
                                        <tr>
                                            <th>Match No</th>
                                            <th>Team 1</th>
                                            <th>Score</th>
                                            <th>Team 2</th>
                                            <th>Venue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($date_matches as $index => $match):
                                            $players_data = $match['players'] ?? [];
                                            $team_ids = array_keys($players_data);
                                            $team1_name = isset($team_ids[0]) ? esc_html(get_the_title($team_ids[0])) : 'Team 1';
                                            $team2_name = isset($team_ids[1]) ? esc_html(get_the_title($team_ids[1])) : 'Team 2';

                                            $teams_scores = [];
                                            if (!empty($match['results'])) {
                                                foreach ($match['results'] as $team_id => $result) {
                                                    $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                                                }
                                            }
                                            $team_ids = array_keys($teams_scores);
                                        ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo $team1_name; ?></td>
                                                <td><?php echo esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0'); ?></td>
                                                <td><?php echo $team2_name; ?></td>
                                                <td><?php echo esc_html(implode(', ', $match['venue'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="tr_2_styling">
                                                    <?php echo $match['event_type'][0] ?? ''; ?> | 
                                                    <a href="<?php echo site_url(); ?>/gallery">Gallery</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                }
            }
        } else {
            echo '<p>No upcoming events found.</p>';
        }
        wp_reset_postdata();
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            const filters = {
                season: document.getElementById('filter-seasons'),
                state: document.getElementById('filter-states'),
                venue: document.getElementById('filter-venues'),
                round: document.getElementById('filter-rounds')
            };

            // Tab switching
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    tabContents.forEach(content => content.style.display = 'none');
                    document.getElementById(target).style.display = 'block';
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Initial tab setup
            if (tabs.length > 0) tabs[0].click();

            // Dropdown filter functionality
            function filterTable() {
                const season = filters.season.value.trim().toLowerCase();
                const state = filters.state.value.trim().toLowerCase();
                const venue = filters.venue.value.trim().toLowerCase();
                const round = filters.round.value.trim().toLowerCase();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season')?.toLowerCase() || '';
                    const matchState = row.getAttribute('data-state')?.toLowerCase() || '';
                    const matchVenue = row.getAttribute('data-venue')?.toLowerCase() || '';
                    const matchRound = row.getAttribute('data-round')?.toLowerCase() || '';

                    const show = (!season || matchSeason === season) &&
                                (!state || matchState === state) &&
                                (!venue || matchVenue === venue) &&
                                (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';
                });
            }
            
            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });
            filterTable();

            // Accordion functionality
            document.querySelectorAll('.rounds_wise_categories').forEach(header => {
                header.addEventListener('click', function() {
                    const roundSlug = this.getAttribute('data-round_cate');
                    const container = document.querySelector(`.round-matches-container[data-round_match="${roundSlug}"]`);
                    const chevron = this.querySelector('.round-chevron i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        container.style.display = 'none';
                        chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('league_tabs_schedule', 'func_league_tabs_schedule');



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


function func_league_price_pool($atts)
{
    ob_start();

            // Display tournament details
        echo '<div class="league_tournament-item">';
            echo '<ul>';
                for ($i = 1; $i <= 8; $i++) {
                    // Dynamically generate field names
                    $poll_title = get_field("price_title_{$i}");
                    $price_pool = get_field("price_poll_{$i}");

                    echo '<li>';
                    echo '<h4>' . esc_html($poll_title) . '</h4>';
                    echo '<h2>' . esc_html($price_pool) . '</h2>';
                    echo '</li>';
                }

            echo '</ul>';
        echo '</div>';
        



    return ob_get_clean();
}
add_shortcode('league_price_pool', 'func_league_price_pool');


function func_league_replays()
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

    echo '<div class="league_replay_main">';

    if ($event_args->have_posts()) {
        echo '<div class="league_replay_main_inner">';

        while ($event_args->have_posts()) {
            $event_args->the_post();
            $post_id = get_the_ID();
            $event_metas = get_post_meta($post_id);



            $featured_image = get_the_post_thumbnail_url($post_id, 'full');
            $video_url = isset($event_metas['sp_video'][0]) ? esc_url($event_metas['sp_video'][0]) : '';

    ?>
            <div class="replays_data_videos_sec">
                <div class="image_secs">
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                    <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/PlayButton-1.svg" class="play-button" data-video-id="video-<?php echo esc_attr($post_id); ?>" />
                </div>
                <div class="content_secs">
                    <h3><?php echo esc_html(get_the_title()); ?></h3>
                </div>
                <div id="video-<?php echo esc_attr($post_id); ?>" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <iframe width="480" height="300" src="https://www.youtube.com/embed/<?php echo $video_url; ?>" frameborder="0" allowfullscreen></iframe>
                    </div>
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
            const filters = {
                season: document.getElementById('filter-season'),
                state: document.getElementById('filter-state'),
                venue: document.getElementById('filter-venue'),
                round: document.getElementById('filter-round')
            };

            const tableBody = document.querySelector('.data-table tbody');
            const loadingSpinner = document.createElement('div');
            loadingSpinner.className = 'loading-spinner';
            loadingSpinner.textContent = 'Loading...';

            function showLoading() {
                tableBody.parentNode.insertBefore(loadingSpinner, tableBody);
            }

            function hideLoading() {
                if (loadingSpinner.parentNode) {
                    loadingSpinner.parentNode.removeChild(loadingSpinner);
                }
            }

            function filterTable() {
                showLoading();
                const season = filters.season.value.trim();
                const state = filters.state.value.trim();
                const venue = filters.venue.value.trim();
                const round = filters.round.value.trim();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season');
                    const matchState = row.getAttribute('data-state');
                    const matchVenue = row.getAttribute('data-venue');
                    const matchRound = row.getAttribute('data-round');

                    const show =
                        (!season || matchSeason === season) &&
                        (!state || matchState === state) &&
                        (!venue || matchVenue === venue) &&
                        (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';
                hideLoading();
                });
            }

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });

            filterTable(); // Initial filter on page load
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
add_shortcode('league_replays', 'func_league_replays');


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


    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('league_overview', 'func_league_overview');


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


function func_pre_league_post_data()
{
    ob_start();

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
        $end_date = get_term_meta($term->term_id, 'league_end_date', true);
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
                        <h5>
                            <?php if ($season_title) {
                            ?>
                                <span><?php echo esc_html($season_title); ?></span>
                            <?php
                            } ?>
                            <?php echo esc_html($atts['season']); ?>
                        </h5>
                        <h2><?php echo esc_html($term->name); ?></h2>
                        <h6>

                            <?php
                            if (!empty($start_date)) {
                                $date = DateTime::createFromFormat('Ymd', $start_date);
                                if ($date) {
                                    $formatted_date = $date->format('F j'); // May 5, 2025
                                } else {
                                    $formatted_date = 'Invalid date'; // Fallback if date is malformed
                                }
                            } else {
                                $formatted_date = 'No date set'; // Fallback if empty
                            }

                            echo $formatted_date;
                            ?>


                            -

                            <?php
                            if (!empty($end_date)) {
                                $date = DateTime::createFromFormat('Ymd', $end_date);
                                if ($date) {
                                    $formatted_date = $date->format('F j'); // May 5, 2025
                                } else {
                                    $formatted_date = 'Invalid date'; // Fallback if date is malformed
                                }
                            } else {
                                $formatted_date = 'No date set'; // Fallback if empty
                            }

                            echo $formatted_date;
                            ?>
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

    // Get all needed data first
    $seasons = get_terms(['taxonomy' => 'sp_season', 'orderby' => 'name', 'hide_empty' => false]);
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $events = new WP_Query(['post_type' => 'sp_event', 'posts_per_page' => -1, 'order' => 'ASC']);

    // Get unique states and rounds
    $unique_states = [];
    $unique_rounds = [];
    if ($events->have_posts()) {
        while ($events->have_posts()) {
            $events->the_post();

            // Get unique states
            $state = get_post_meta(get_the_ID(), 'state', true);
            if (!empty($state) && !in_array($state, $unique_states)) {
                $unique_states[] = $state;
            }

            // Get unique rounds
            $rounds = maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)) ?: [];
            foreach ((array) $rounds as $round_group) {
                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
            }
        }
        $unique_rounds = array_unique($unique_rounds);
        $events->rewind_posts();
    }

    // Display filters
    ?>
    <form action="" class="filter_form player_info_venue">
        <div class="filter_group form-group_main">
            <div class="filter_item form-group">
                <select id="filter-season">
                    <option value=''>Select Season</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season->name); ?>"><?php echo esc_html($season->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-state">
                    <option value=''>Select State</option>
                    <?php foreach ($unique_states as $state): ?>
                        <option value="<?php echo esc_attr($state); ?>"><?php echo esc_html($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-venue">
                    <option value=''>Select Venue</option>
                    <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo esc_attr($venue->name); ?>"><?php echo esc_html($venue->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-round">
                    <option value=''>Select Round</option>
                    <?php foreach ($unique_rounds as $round): ?>
                        <option value="<?php echo esc_attr(sanitize_title($round)); ?>"><?php echo esc_html($round); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        // Organize matches by round
        $matches_by_round = [];
        if ($events->have_posts()) {
            while ($events->have_posts()) {
                $events->the_post();

                // Prepare match data
                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true) ?: get_the_date('Y-m-d');

                $match_data = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'date' => date('F j, Y', strtotime($match_date)),
                    'time' => get_post_time('g:i A', false, get_the_ID(), true),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', ['fields' => 'names']),
                    'season' => wp_get_post_terms(get_the_ID(), 'sp_season', ['fields' => 'names']),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', ['fields' => 'names']),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => get_the_title($tournament_associate_id),
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'raw_date' => $match_date,
                    'state' => get_post_meta(get_the_ID(), 'state', true),
                ];

                // Get round name
                $rounds = $match_data['rounds'];
                $round_name = empty($rounds) ? 'Uncategorized' : (is_array($rounds) ? implode(', ', $rounds) : $rounds);

                if (!isset($matches_by_round[$round_name])) {
                    $matches_by_round[$round_name] = [];
                }

                $matches_by_round[$round_name][] = $match_data;
            }

            // Sort rounds by date (newest first)
            uasort($matches_by_round, function ($a, $b) {
                return strtotime($b[0]['raw_date']) - strtotime($a[0]['raw_date']);
            });

            // Display matches by round
            foreach ($matches_by_round as $round_name => $matches) {
                $round_slug = sanitize_title($round_name);

                // Get venues for this round
                $round_venues = [];
                foreach ($matches as $match) {
                    foreach ($match['venue'] as $venue) {
                        if (!in_array($venue, $round_venues)) {
                            $round_venues[] = $venue;
                        }
                    }
                }
                $venue_display = !empty($round_venues) ? implode(', ', $round_venues) : 'Venue not specified';
        ?>
                <div class="rounds_wise_categories" data-round_cate="<?php echo esc_attr($round_slug); ?>">
                    <div class="rounds_wise_categories_inner">
                        <div class="rounds_wise_img">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/npl-leagues.png" alt="">
                        </div>
                        <div class="rounds_wise_cont">
                            <h2><?php echo esc_html($round_name); ?></h2>
                            <h3><?php echo esc_html($venue_display); ?></h3>
                        </div>
                    </div>
                    <div class="round-chevron"><i class="fas fa-chevron-down"></i></div>
                    <div style="clear: both;"></div>
                </div>

                <div class="round-matches-container" data-round_match="<?php echo esc_attr($round_slug); ?>" style="display:none;">
                    <?php
                    // Group matches by date
                    $matches_by_date = [];
                    foreach ($matches as $match) {
                        $matches_by_date[$match['date']][] = $match;
                    }
                    krsort($matches_by_date);

                    foreach ($matches_by_date as $date => $date_matches):
                        $first_match = $date_matches[0];
                    ?>
                        <div class="match_date_wisess match-row"
                            data-season="<?php echo esc_attr($match['season'][0] ?? ''); ?>"
                            data-state="<?php echo esc_attr($match['state'] ?? ''); ?>"
                            data-venue="<?php echo esc_attr($match['venue'][0] ?? ''); ?>"
                            data-round="<?php echo esc_attr($round_slug); ?>">
                            <h2 class="heading_underline"><?php echo esc_html($date); ?></h2>

                            <table class="team-players-table">
                                <thead>
                                    <tr>
                                        <th>Match No</th>
                                        <th>Team 1</th>
                                        <th>Score</th>
                                        <th>Team 2</th>
                                        <th>Venue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($date_matches as $index => $match):

                                        // echo "<pre>";
                                        // print_r($players);
                                        // echo "</pre>";

                                        $players_data = $match['players'] ?? []; // Fetch players data array
                                        $team_ids = array_keys($players_data); // Extract team IDs (e.g., 3072, 3065)

                                        // Fetch team names using team IDs
                                        $team1_name = isset($team_ids[0]) ? esc_html(get_the_title($team_ids[0])) : 'Team 1';
                                        $team2_name = isset($team_ids[1]) ? esc_html(get_the_title($team_ids[1])) : 'Team 2';



                                        $teams_scores = [];

                                        if (!empty($match['results'])) {
                                            foreach ($match['results'] as $team_id => $result) {
                                                $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                                            }
                                        }

                                        $team_ids = array_keys($teams_scores);

                                    ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $team1_name; ?></td>

                                            <td><?php echo esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0'); ?></td>

                                            <td><?php echo $team2_name; ?></td>
                                            <td><?php echo esc_html(implode(', ', $match['venue'])); ?></td>
                                        </tr>
                                        <?php
                                        echo '<tr>';
                                        echo '<td colspan="5" class="tr_2_styling"> ' . $match['event_type'][0] . ' | <a href="' . site_url() . '/gallery">Gallery</td>';
                                        echo '</tr>';
                                        ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                        </div>



                    <?php endforeach; ?>
                </div>
        <?php
            }
        }
        wp_reset_postdata();
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filters = {
                season: document.getElementById('filter-season'),
                state: document.getElementById('filter-state'),
                venue: document.getElementById('filter-venue'),
                round: document.getElementById('filter-round')
            };

            function filterTable() {
                const season = filters.season.value.trim().toLowerCase();
                const state = filters.state.value.trim().toLowerCase();
                const venue = filters.venue.value.trim().toLowerCase();
                const round = filters.round.value.trim().toLowerCase();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season')?.toLowerCase() || '';
                    const matchState = row.getAttribute('data-state')?.toLowerCase() || '';
                    const matchVenue = row.getAttribute('data-venue')?.toLowerCase() || '';
                    const matchRound = row.getAttribute('data-round')?.toLowerCase() || '';

                    const show =
                        (!season || matchSeason === season) &&
                        (!state || matchState === state) &&
                        (!venue || matchVenue === venue) &&
                        (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';

                    // Hide empty date sections
                    const dateSection = row.closest('.main_live_tournas');
                    if (dateSection) {
                        const visibleRows = dateSection.querySelectorAll('.match-row[style=""]').length;
                        dateSection.style.display = visibleRows > 0 ? '' : 'none';
                    }
                });
            }

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });

            filterTable(); // Initial filter on page load

            // Toggle round containers
            document.querySelectorAll('.rounds_wise_categories').forEach(header => {
                header.addEventListener('click', function() {
                    const roundSlug = this.getAttribute('data-round_cate');
                    const container = document.querySelector(`.round-matches-container[data-round_match="${roundSlug}"]`);
                    const chevron = this.querySelector('.round-chevron i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        container.style.display = 'none';
                        chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
        });
    </script>
<?php

    return ob_get_clean();
}
add_shortcode('pre_league_schedule', 'func_pre_league_schedule');


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


function func_pre_league_keydates()
{
    ob_start();
?>
    <?php if (have_rows('league_key_dates_schedule')) : ?>
        <div class="pre_league_key_dates_main">
            <div class="pre_league_key_dates_inner">
                <?php while (have_rows('league_key_dates_schedule')) : the_row(); ?>
                    <div class="pre_league_key_dates_items">
                        <h4><?php the_sub_field('league_date'); ?></h4>
                        <h2><?php the_sub_field('league_text'); ?></h2>
                        <h5><?php the_sub_field('league_status'); ?></h5>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php else : ?>
        <p>No key dates available at the moment.</p>
    <?php endif; ?>
<?php
    return ob_get_clean();
}
add_shortcode('pre_league_keydates', 'func_pre_league_keydates');


// Post League
function func_post_league_schedule($atts)
{
    ob_start();

    // Get current date
    $today = date('Y-m-d');

    // Get all needed data first
    $seasons = get_terms(['taxonomy' => 'sp_season', 'orderby' => 'name', 'hide_empty' => false]);
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);

    // Modify the events query to get matches up to today
    $events = new WP_Query([
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => '_wp_old_date',
                'value' => $today,
                'compare' => '<=',
                'type' => 'DATE'
            ],
            [
                'key' => '_wp_old_date',
                'compare' => 'NOT EXISTS'
            ]
        ],
        'date_query' => [
            [
                'before' => $today,
                'inclusive' => true,
            ]
        ]
    ]);

    // Rest of your existing code...
    // Get unique states and rounds
    $unique_states = [];
    $unique_rounds = [];
    if ($events->have_posts()) {
        while ($events->have_posts()) {
            $events->the_post();

            // Get unique states
            $state = get_post_meta(get_the_ID(), 'state', true);
            if (!empty($state) && !in_array($state, $unique_states)) {
                $unique_states[] = $state;
            }

            // Get unique rounds
            $rounds = maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)) ?: [];
            foreach ((array) $rounds as $round_group) {
                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
            }
        }
        $unique_rounds = array_unique($unique_rounds);
        $events->rewind_posts();
    }

    // Display filters
?>
    <form action="" class="filter_form player_info_venue">
        <div class="filter_group form-group_main">
            <div class="filter_item form-group">
                <select id="filter-season">
                    <option value=''>Select Season</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season->name); ?>"><?php echo esc_html($season->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-state">
                    <option value=''>Select State</option>
                    <?php foreach ($unique_states as $state): ?>
                        <option value="<?php echo esc_attr($state); ?>"><?php echo esc_html($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-venue">
                    <option value=''>Select Venue</option>
                    <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo esc_attr($venue->name); ?>"><?php echo esc_html($venue->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-round">
                    <option value=''>Select Round</option>
                    <?php foreach ($unique_rounds as $round): ?>
                        <option value="<?php echo esc_attr(sanitize_title($round)); ?>"><?php echo esc_html($round); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        // Organize matches by round
        $matches_by_round = [];
        if ($events->have_posts()) {
            while ($events->have_posts()) {
                $events->the_post();

                // Prepare match data
                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true) ?: get_the_date('Y-m-d');

                $match_data = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'date' => date('F j, Y', strtotime($match_date)),
                    'time' => get_post_time('g:i A', false, get_the_ID(), true),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', ['fields' => 'names']),
                    'season' => wp_get_post_terms(get_the_ID(), 'sp_season', ['fields' => 'names']),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', ['fields' => 'names']),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => get_the_title($tournament_associate_id),
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'raw_date' => $match_date,
                    'state' => get_post_meta(get_the_ID(), 'state', true),
                ];

                // Get round name
                $rounds = $match_data['rounds'];
                $round_name = empty($rounds) ? 'Uncategorized' : (is_array($rounds) ? implode(', ', $rounds) : $rounds);

                if (!isset($matches_by_round[$round_name])) {
                    $matches_by_round[$round_name] = [];
                }

                $matches_by_round[$round_name][] = $match_data;
            }

            // Sort rounds by date (newest first)
            uasort($matches_by_round, function ($a, $b) {
                return strtotime($b[0]['raw_date']) - strtotime($a[0]['raw_date']);
            });

            // Display matches by round
            foreach ($matches_by_round as $round_name => $matches) {
                $round_slug = sanitize_title($round_name);

                // Get venues for this round
                $round_venues = [];
                foreach ($matches as $match) {
                    foreach ($match['venue'] as $venue) {
                        if (!in_array($venue, $round_venues)) {
                            $round_venues[] = $venue;
                        }
                    }
                }
                $venue_display = !empty($round_venues) ? implode(', ', $round_venues) : 'Venue not specified';
        ?>
                <div class="rounds_wise_categories" data-round_cate="<?php echo esc_attr($round_slug); ?>">
                    <div class="rounds_wise_categories_inner">
                        <div class="rounds_wise_img">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/npl-leagues.png" alt="">
                        </div>
                        <div class="rounds_wise_cont">
                            <h2><?php echo esc_html($round_name); ?></h2>
                            <h3><?php echo esc_html($venue_display); ?></h3>
                        </div>
                    </div>
                    <div class="round-chevron"><i class="fas fa-chevron-down"></i></div>
                    <div style="clear: both;"></div>
                </div>

                <div class="round-matches-container" data-round_match="<?php echo esc_attr($round_slug); ?>" style="display:none;">
                    <?php
                    // Group matches by date
                    $matches_by_date = [];
                    foreach ($matches as $match) {
                        $matches_by_date[$match['date']][] = $match;
                    }
                    krsort($matches_by_date);

                    foreach ($matches_by_date as $date => $date_matches):
                        $first_match = $date_matches[0];
                    ?>
                        <div class="match_date_wisess match-row"
                            data-season="<?php echo esc_attr($match['season'][0] ?? ''); ?>"
                            data-state="<?php echo esc_attr($match['state'] ?? ''); ?>"
                            data-venue="<?php echo esc_attr($match['venue'][0] ?? ''); ?>"
                            data-round="<?php echo esc_attr($round_slug); ?>">
                            <h2 class="heading_underline"><?php echo esc_html($date); ?></h2>

                            <table class="team-players-table">
                                <thead>
                                    <tr>
                                        <th>Match No</th>
                                        <th>Team 1</th>
                                        <th>Score</th>
                                        <th>Team 2</th>
                                        <th>Venue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($date_matches as $index => $match):

                                        $players_data = $match['players'] ?? []; // Fetch players data array
                                        $team_ids = array_keys($players_data); // Extract team IDs (e.g., 3072, 3065)

                                        // Fetch team names using team IDs
                                        $team1_name = isset($team_ids[0]) ? esc_html(get_the_title($team_ids[0])) : 'Team 1';
                                        $team2_name = isset($team_ids[1]) ? esc_html(get_the_title($team_ids[1])) : 'Team 2';

                                        $teams_scores = [];

                                        if (!empty($match['results'])) {
                                            foreach ($match['results'] as $team_id => $result) {
                                                $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                                            }
                                        }

                                        $team_ids = array_keys($teams_scores);

                                    ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $team1_name; ?></td>

                                            <td><?php echo esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0'); ?></td>

                                            <td><?php echo $team2_name; ?></td>
                                            <td><?php echo esc_html(implode(', ', $match['venue'])); ?></td>
                                        </tr>
                                        <?php
                                        echo '<tr>';
                                        echo '<td colspan="5" class="tr_2_styling"> ' . $match['event_type'][0] . ' | <a href="' . site_url() . '/gallery">Gallery</td>';
                                        echo '</tr>';
                                        ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                        </div>
                    <?php endforeach; ?>
                </div>
        <?php
            }
        } else {
            echo '<p>No previous matches found.</p>';
        }
        wp_reset_postdata();
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filters = {
                season: document.getElementById('filter-season'),
                state: document.getElementById('filter-state'),
                venue: document.getElementById('filter-venue'),
                round: document.getElementById('filter-round')
            };

            function filterTable() {
                const season = filters.season.value.trim().toLowerCase();
                const state = filters.state.value.trim().toLowerCase();
                const venue = filters.venue.value.trim().toLowerCase();
                const round = filters.round.value.trim().toLowerCase();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season')?.toLowerCase() || '';
                    const matchState = row.getAttribute('data-state')?.toLowerCase() || '';
                    const matchVenue = row.getAttribute('data-venue')?.toLowerCase() || '';
                    const matchRound = row.getAttribute('data-round')?.toLowerCase() || '';

                    const show =
                        (!season || matchSeason === season) &&
                        (!state || matchState === state) &&
                        (!venue || matchVenue === venue) &&
                        (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';

                    // Hide empty date sections
                    const dateSection = row.closest('.main_live_tournas');
                    if (dateSection) {
                        const visibleRows = dateSection.querySelectorAll('.match-row[style=""]').length;
                        dateSection.style.display = visibleRows > 0 ? '' : 'none';
                    }
                });
            }

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });

            filterTable(); // Initial filter on page load

            // Toggle round containers
            document.querySelectorAll('.rounds_wise_categories').forEach(header => {
                header.addEventListener('click', function() {
                    const roundSlug = this.getAttribute('data-round_cate');
                    const container = document.querySelector(`.round-matches-container[data-round_match="${roundSlug}"]`);
                    const chevron = this.querySelector('.round-chevron i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        container.style.display = 'none';
                        chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
        });
    </script>
<?php

    return ob_get_clean();
}
add_shortcode('post_league_schedule', 'func_post_league_schedule');



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


function func_post_league_keydates()
{
    ob_start();
?>
    <?php if (have_rows('league_key_dates_schedule')) : ?>
        <div class="pre_league_key_dates_main">
            <div class="pre_league_key_dates_inner">
                <?php while (have_rows('league_key_dates_schedule')) : the_row(); ?>
                    <div class="pre_league_key_dates_items">
                        <h4><?php the_sub_field('league_date'); ?></h4>
                        <h2><?php the_sub_field('league_text'); ?></h2>
                        <h5><?php the_sub_field('league_status'); ?></h5>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php else : ?>
        <p>No key dates available at the moment.</p>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
add_shortcode('post_league_keydates', 'func_post_league_keydates');


// mid league

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
                    <iframe width="480" height="300" src="https://www.youtube.com/embed/<?php echo $video_url; ?>" frameborder="0" allowfullscreen></iframe>
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
            const filters = {
                season: document.getElementById('filter-season'),
                state: document.getElementById('filter-state'),
                venue: document.getElementById('filter-venue'),
                round: document.getElementById('filter-round')
            };

            const tableBody = document.querySelector('.data-table tbody');
            const loadingSpinner = document.createElement('div');
            loadingSpinner.className = 'loading-spinner';
            loadingSpinner.textContent = 'Loading...';

            function showLoading() {
                tableBody.parentNode.insertBefore(loadingSpinner, tableBody);
            }

            function hideLoading() {
                if (loadingSpinner.parentNode) {
                    loadingSpinner.parentNode.removeChild(loadingSpinner);
                }
            }

            function filterTable() {
                showLoading();
                const season = filters.season.value.trim();
                const state = filters.state.value.trim();
                const venue = filters.venue.value.trim();
                const round = filters.round.value.trim();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season');
                    const matchState = row.getAttribute('data-state');
                    const matchVenue = row.getAttribute('data-venue');
                    const matchRound = row.getAttribute('data-round');

                    const show =
                        (!season || matchSeason === season) &&
                        (!state || matchState === state) &&
                        (!venue || matchVenue === venue) &&
                        (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';
                hideLoading();
                });
            }

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });

            filterTable(); // Initial filter on page load
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

function func_mid_league_schedule($atts)
{
    ob_start();

    // Get current date in Y-m-d format
    $current_date = date('Y-m-d');

    // Get all needed data first
    $seasons = get_terms(['taxonomy' => 'sp_season', 'orderby' => 'name', 'hide_empty' => false]);
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);

    // Modify events query to only get today's matches
    $events = new WP_Query([
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => '_wp_old_date',
                'value' => $current_date,
                'compare' => '=',
                'type' => 'DATE'
            ]
        ]
    ]);

    // Get unique states and rounds
    $unique_states = [];
    $unique_rounds = [];
    if ($events->have_posts()) {
        while ($events->have_posts()) {
            $events->the_post();

            // Get unique states
            $state = get_post_meta(get_the_ID(), 'state', true);
            if (!empty($state) && !in_array($state, $unique_states)) {
                $unique_states[] = $state;
            }

            // Get unique rounds
            $rounds = maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)) ?: [];
            foreach ((array) $rounds as $round_group) {
                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
            }
        }
        $unique_rounds = array_unique($unique_rounds);
        $events->rewind_posts();
    }

    // Display filters
    ?>
    <form action="" class="filter_form player_info_venue">
        <div class="filter_group form-group_main">
            <div class="filter_item form-group">
                <select id="filter-season">
                    <option value=''>Select Season</option>
                    <?php foreach ($seasons as $season): ?>
                        <option value="<?php echo esc_attr($season->name); ?>"><?php echo esc_html($season->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-state">
                    <option value=''>Select State</option>
                    <?php foreach ($unique_states as $state): ?>
                        <option value="<?php echo esc_attr($state); ?>"><?php echo esc_html($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-venue">
                    <option value=''>Select Venue</option>
                    <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo esc_attr($venue->name); ?>"><?php echo esc_html($venue->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter_item form-group">
                <select id="filter-round">
                    <option value=''>Select Round</option>
                    <?php foreach ($unique_rounds as $round): ?>
                        <option value="<?php echo esc_attr(sanitize_title($round)); ?>"><?php echo esc_html($round); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        // Organize matches by round
        $matches_by_round = [];
        if ($events->have_posts()) {
            while ($events->have_posts()) {
                $events->the_post();

                // Prepare match data
                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true);

                // Skip if not today's match (shouldn't happen due to our query, but just in case)
                if ($match_date !== $current_date) {
                    continue;
                }

                $match_data = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'date' => date('F j, Y', strtotime($match_date)),
                    'time' => get_post_time('g:i A', false, get_the_ID(), true),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', ['fields' => 'names']),
                    'season' => wp_get_post_terms(get_the_ID(), 'sp_season', ['fields' => 'names']),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', ['fields' => 'names']),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => get_the_title($tournament_associate_id),
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'raw_date' => $match_date,
                    'state' => get_post_meta(get_the_ID(), 'state', true),
                ];

                // Get round name
                $rounds = $match_data['rounds'];
                $round_name = empty($rounds) ? 'Uncategorized' : (is_array($rounds) ? implode(', ', $rounds) : $rounds);

                if (!isset($matches_by_round[$round_name])) {
                    $matches_by_round[$round_name] = [];
                }

                $matches_by_round[$round_name][] = $match_data;
            }

            // Display matches by round
            foreach ($matches_by_round as $round_name => $matches) {
                $round_slug = sanitize_title($round_name);

                // Get venues for this round
                $round_venues = [];
                foreach ($matches as $match) {
                    foreach ($match['venue'] as $venue) {
                        if (!in_array($venue, $round_venues)) {
                            $round_venues[] = $venue;
                        }
                    }
                }
                $venue_display = !empty($round_venues) ? implode(', ', $round_venues) : 'Venue not specified';
        ?>
                <div class="rounds_wise_categories" data-round_cate="<?php echo esc_attr($round_slug); ?>">
                    <div class="rounds_wise_categories_inner">
                        <div class="rounds_wise_img">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/npl-leagues.png" alt="">
                        </div>
                        <div class="rounds_wise_cont">
                            <h2><?php echo esc_html($round_name); ?></h2>
                            <h3><?php echo esc_html($venue_display); ?></h3>
                        </div>
                    </div>
                    <div class="round-chevron"><i class="fas fa-chevron-down"></i></div>
                    <div style="clear: both;"></div>
                </div>

                <div class="round-matches-container" data-round_match="<?php echo esc_attr($round_slug); ?>">
                    <div class="match_date_wisess match-row">
                        <h2 class="heading_underline">Today's Matches - <?php echo date('F j, Y'); ?></h2>

                        <table class="team-players-table">
                            <thead>
                                <tr>
                                    <th>Match No</th>
                                    <th>Team 1</th>
                                    <th>Score</th>
                                    <th>Team 2</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($matches as $index => $match):
                                    $players_data = $match['players'] ?? [];
                                    $team_ids = array_keys($players_data);

                                    $team1_name = isset($team_ids[0]) ? esc_html(get_the_title($team_ids[0])) : 'Team 1';
                                    $team2_name = isset($team_ids[1]) ? esc_html(get_the_title($team_ids[1])) : 'Team 2';

                                    $teams_scores = [];

                                    if (!empty($match['results'])) {
                                        foreach ($match['results'] as $team_id => $result) {
                                            $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                                        }
                                    }

                                    $team_ids = array_keys($teams_scores);

                                ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $team1_name; ?></td>

                                        <td><?php echo esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0'); ?></td>

                                        <td><?php echo $team2_name; ?></td>
                                        <td><?php echo esc_html(implode(', ', $match['venue'])); ?></td>
                                    </tr>
                                    <?php
                                    echo '<tr>';
                                    echo '<td colspan="5" class="tr_2_styling"> ' . $match['event_type'][0] . ' | <a href="' . site_url() . '/gallery">Gallery</td>';
                                    echo '</tr>';
                                    ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<p>No matches scheduled for today.</p>';
        }
        wp_reset_postdata();
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filters = {
                season: document.getElementById('filter-season'),
                state: document.getElementById('filter-state'),
                venue: document.getElementById('filter-venue'),
                round: document.getElementById('filter-round')
            };

            function filterTable() {
                const season = filters.season.value.trim().toLowerCase();
                const state = filters.state.value.trim().toLowerCase();
                const venue = filters.venue.value.trim().toLowerCase();
                const round = filters.round.value.trim().toLowerCase();

                document.querySelectorAll('.match-row').forEach(row => {
                    const matchSeason = row.getAttribute('data-season')?.toLowerCase() || '';
                    const matchState = row.getAttribute('data-state')?.toLowerCase() || '';
                    const matchVenue = row.getAttribute('data-venue')?.toLowerCase() || '';
                    const matchRound = row.getAttribute('data-round')?.toLowerCase() || '';

                    const show =
                        (!season || matchSeason === season) &&
                        (!state || matchState === state) &&
                        (!venue || matchVenue === venue) &&
                        (!round || matchRound === round);

                    row.style.display = show ? '' : 'none';
                });
            }

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterTable);
            });

            // Toggle round containers
            document.querySelectorAll('.rounds_wise_categories').forEach(header => {
                header.addEventListener('click', function() {
                    const roundSlug = this.getAttribute('data-round_cate');
                    const container = document.querySelector(`.round-matches-container[data-round_match="${roundSlug}"]`);
                    const chevron = this.querySelector('.round-chevron i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        container.style.display = 'none';
                        chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('mid_league_schedule', 'func_mid_league_schedule');

function func_mid_league_overview($atts)
{
    ob_start();

    // Extract and sanitize attributes
    $atts = shortcode_atts([
        'match_id' => '',
    ], $atts, 'mid_league_overview');

    $match_id = sanitize_text_field($atts['match_id']);

    // Check if match_id is provided
    if (empty($match_id)) {
        echo '<p>No match ID provided. Please specify a match ID.</p>';
        return ob_get_clean();
    }

    // Get the specific post by ID
    $post = get_post($match_id);

    if ($post && $post->post_type === 'sp_event') {
        setup_postdata($post);

        $post_id = $post->ID;
        $title = get_the_title($post_id);
        $event_metas = get_post_meta($post_id);
        $venue_terms = wp_get_post_terms($post_id, 'sp_venue', ['fields' => 'names']);

        $team1_image_src = wp_get_attachment_image_src($event_metas['match_type_icon'][0] ?? '', 'full');
        $team1_image = $team1_image_src[0] ?? '';
        $video_url = $event_metas['sp_video'][0] ?? '';

        // echo "<pre>";
        // print_r($event_metas);
        // echo "</pre>";

    ?>
        <div class="mid-league-overview-container">
            <!-- Event Overview -->
            <div class="event_overview">
                <div class="event_image_sec">
                    <div class="image_sec">
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full')); ?>" alt="Event Image" />
                    </div>
                    <div class="image_content">
                        <h3>Watch Live</h3>
                        <p>
                            <?php echo wp_trim_words(get_the_content($post_id), 14, ''); ?>
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/PlayButton-1.svg"
                                class="play-button"
                                data-video-id="video_<?php echo esc_attr($post_id); ?>" />
                        </p>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="event_content_sec">
                    <div class="event-details">
                        <h3>Match</h3>
                        <h2 class="<?php echo esc_attr(sanitize_title($event_metas['match_type'][0] ?? 'N/A')); ?>">
                            <?php echo esc_html($event_metas['match_type'][0] ?? 'N/A'); ?>
                        </h2>
                        <h5><?php echo !empty($venue_terms) ? esc_html(implode(', ', $venue_terms)) : 'Not specified'; ?></h5>
                        <!-- <div class="match_type_icon">
                             <img src="<?php echo esc_url($team1_image); ?>" alt="Match Type Icon" />
                         </div> -->
                    </div>
                    <div class="event-details2">
                        <h4>Players</h4>
                        <h3><?php echo esc_html($title); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popup Modal -->
        <div class="replays_data_videos_inner">
            <div id="video_<?php echo esc_attr($post_id); ?>" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <?php if ($video_url): ?>
                        <iframe width="480" height="300" src="<?php echo esc_url($video_url); ?>" frameborder="0" allowfullscreen></iframe>
                    <?php else: ?>
                        <p>No video available for this event.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Open Modal
                const playButtons = document.querySelectorAll('.play-button');
                playButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const videoId = this.getAttribute('data-video-id');
                        const modal = document.getElementById(videoId);
                        if (modal) modal.style.display = 'block';
                    });
                });

                // Close Modal
                const closeButtons = document.querySelectorAll('.close');
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const modal = this.closest('.modal');
                        if (modal) modal.style.display = 'none';
                    });
                });

                // Close Modal on Outside Click
                window.addEventListener('click', function(event) {
                    const modals = document.querySelectorAll('.modal');
                    modals.forEach(modal => {
                        if (event.target === modal) {
                            modal.style.display = 'none';
                        }
                    });
                });
            });
        </script>
    <?php

        // Reset post data
        wp_reset_postdata();
    } else {
        echo '<p>No event found for the specified match ID.</p>';
    }

    return ob_get_clean();
}
add_shortcode('mid_league_overview', 'func_mid_league_overview');


function func_mid_league_teams($atts)
{
    ob_start();

    // Ensure jQuery is loaded
    wp_enqueue_script('jquery');

    // Handle shortcode attributes
    $atts = shortcode_atts(array(
        'league' => '',
        'season' => '',
    ), $atts);

    // Prepare query arguments
    $team_args = array(
        'post_type' => 'sp_team',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );

    // Add league filter if specified
    if (!empty($atts['league'])) {
        $team_args['tax_query'] = array(
            array(
                'taxonomy' => 'sp_league',
                'field' => 'slug',
                'terms' => sanitize_title($atts['league'])
            )
        );
    }

    $team_query = new WP_Query($team_args);

    if ($team_query->have_posts()) {
        // Prepare teams data and collect unique states
        $teams = array();
        $states = array('All' => 'All');
        $position = 1;

        while ($team_query->have_posts()) {
            $team_query->the_post();
            $team_id = get_the_ID();
            $team_state = get_post_meta($team_id, 'state', true);

            if ($team_state && !isset($states[$team_state])) {
                $states[$team_state] = $team_state;
            }

            $teams[] = array(
                'position' => $position++,
                'id' => $team_id,
                'logo' => get_the_post_thumbnail_url($team_id, 'thumbnail'),
                'name' => get_the_title(),
                'power' => get_post_meta($team_id, 'league_team_pwr', true),
                'wins' => get_post_meta($team_id, 'league_team_won', true),
                'losses' => get_post_meta($team_id, 'league_team_lost', true),
                'for' => get_post_meta($team_id, 'league_team_f', true),
                'r1' => get_post_meta($team_id, 'league_team_r1', true),
                'r2' => get_post_meta($team_id, 'league_team_r2', true),
                'r3' => get_post_meta($team_id, 'league_team_r3', true),
                'r4' => get_post_meta($team_id, 'league_team_r4', true),
                'points' => get_post_meta($team_id, 'league_team_pts', true),
                'streak' => get_post_meta($team_id, 'league_team_strk', true),
                'dupr' => get_post_meta($team_id, 'league_team_dupr', true),
                'state' => $team_state
            );
        }

        // Output the tabs and leaderboard
    ?>
        <div class="team-leaderboard-container1">
            <!-- State Tabs Navigation -->
            <h2 class="heading_underline">League Ladder</h2>

            <div class="state-tabs">
                <?php foreach ($states as $state_slug => $state_name): ?>
                    <button class="state-tab<?php echo $state_slug === 'All' ? ' active' : ''; ?>"
                        data-state="<?php echo esc_attr($state_slug); ?>">
                        <?php echo esc_html($state_name); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Teams Table -->
            <table class="team-leaderboard team-players-table">
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Team</th>
                        <th>State</th>
                        <th>PWR</th>
                        <th>Won</th>
                        <th>Lost</th>
                        <th>F</th>
                        <th>R1</th>
                        <th>R2</th>
                        <th>R3</th>
                        <th>R4</th>
                        <th>PTS</th>
                        <th>STRK</th>
                        <th>DUPR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr class="team-row" data-state="<?php echo esc_attr($team['state']); ?>">
                            <td><?php echo esc_html($team['position']); ?></td>
                            <td>
                                <div class="team-info">
                                    <?php if ($team['logo']): ?>
                                        <img src="<?php echo esc_url($team['logo']); ?>" alt="<?php echo esc_attr($team['name']); ?>" width="40" height="40" />
                                    <?php endif; ?>
                                    <span class="team-name"><?php echo esc_html($team['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo esc_html($team['state']); ?></td>
                            <td><?php echo esc_html($team['power']); ?></td>
                            <td><?php echo esc_html($team['wins']); ?></td>
                            <td><?php echo esc_html($team['losses']); ?></td>
                            <td><?php echo esc_html($team['for']); ?></td>
                            <td><?php echo esc_html($team['r1']); ?></td>
                            <td><?php echo esc_html($team['r2']); ?></td>
                            <td><?php echo esc_html($team['r3']); ?></td>
                            <td><?php echo esc_html($team['r4']); ?></td>
                            <td><?php echo esc_html($team['points']); ?></td>
                            <td><?php echo esc_html($team['streak']); ?></td>
                            <td><?php echo esc_html($team['dupr']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <style>
            /* Tab Styles */
            .team-leaderboard-container1 .state-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #e0e0e0;
            }

            .team-leaderboard-container1 .state-tab {
                padding: 8px 16px;
                background: #f5f5f5;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 14px;
            }

            .team-leaderboard-container1 .state-tab:hover {
                background: #e0e0e0;
            }

            .team-leaderboard-container1 .state-tab.active {
                background: #0073aa;
                color: white;
            }

            /* Table Styles */
            .team-leaderboard-container1 .team-leaderboard-container {
                width: 100%;
                overflow-x: auto;
                margin: 20px 0;
            }

            .team-leaderboard-container1 .team-leaderboard {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }

            .team-leaderboard-container1 .team-leaderboard th,
            .team-leaderboard-container1 .team-leaderboard td {
                padding: 8px 12px;
                text-align: center;
                border-bottom: 1px solid #e0e0e0;
            }

            .team-leaderboard-container1 .team-leaderboard th {
                background-color: #f5f5f5;
                font-weight: 600;
                white-space: nowrap;
            }

            /* Row visibility control */
            .team-leaderboard-container1 .team-row {
                display: table-row;
            }

            .team-leaderboard-container1 .team-row.hidden {
                display: none;
            }

            /* Team info styles */
            .team-leaderboard-container1 .team-info {
                display: flex;
                align-items: center;
                gap: 10px;
                text-align: left;
            }

            .team-leaderboard-container1 .team-info img {
                border-radius: 50%;
                object-fit: cover;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .team-leaderboard-container1 .state-tabs {
                    gap: 4px;
                }

                .team-leaderboard-container1 .state-tab {
                    padding: 6px 12px;
                    font-size: 13px;
                }

                .team-leaderboard-container1 .team-leaderboard th,
                .team-leaderboard-container1 .team-leaderboard td {
                    padding: 6px 8px;
                    font-size: 13px;
                }

                .team-leaderboard-container1 .team-info {
                    gap: 6px;
                }

                .team-leaderboard-container1 .team-info img {
                    width: 30px;
                    height: 30px;
                }
            }
        </style>

        <script type="text/javascript">
            (function($) {
                $(document).ready(function() {
                    // Initialize - hide all non-All state rows if All is active
                    if ($('.state-tab.active').data('state') === 'All') {
                        $('.team-row').removeClass('hidden');
                    } else {
                        var activeState = $('.state-tab.active').data('state');
                        $('.team-row').addClass('hidden');
                        $('.team-row[data-state="' + activeState + '"]').removeClass('hidden');
                    }

                    // Tab click handler
                    $('.state-tab').on('click', function() {
                        // Update active tab
                        $('.state-tab').removeClass('active');
                        $(this).addClass('active');

                        // Get selected state
                        var state = $(this).data('state');

                        // Show/hide teams
                        if (state === 'All') {
                            $('.team-row').removeClass('hidden');
                        } else {
                            $('.team-row').addClass('hidden');
                            $('.team-row[data-state="' + state + '"]').removeClass('hidden');
                        }
                    });
                });
            })(jQuery);
        </script>
    <?php
    } else {
        echo '<p>No teams found for the specified criteria.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('mid_league_teams', 'func_mid_league_teams');


function func_get_events_in_round_1()
{
    $args = array(
        'post_type'      => 'sp_event', // SportsPress events post type
        'posts_per_page' => -1,        // Retrieve all posts
        // 'tax_query'      => array(
        //     array(
        //         'taxonomy' => 'sp_round', // Taxonomy for rounds
        //         'field'    => 'slug',    // Use the slug to identify terms
        //         'terms'    => array(     // Specify the slugs for the 5 rounds
        //             'round-1',
        //             'round-2',
        //             'round-3',
        //             'round-4',
        //             'round-5',
        //         ),
        //         'operator' => 'IN', // Fetch posts matching any of these terms
        //     ),
        // ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table>';
        echo '<thead>
                         <tr>
                             <th>Match Title</th>
                             <th>Date</th>
                             <th>Venue</th>
                         </tr>
                       </thead>';
        echo '<tbody>';

        while ($query->have_posts()) {
            $query->the_post();

            $match_date = get_post_meta(get_the_ID(), 'sp_date', true); // Match date
            $venue_terms = wp_get_post_terms(get_the_ID(), 'sp_venue', array('fields' => 'names')); // Venue

            echo '<tr>';
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td>' . date('F j, Y', strtotime($match_date)) . '</td>';
            echo '<td>' . (!empty($venue_terms) ? esc_html(implode(', ', $venue_terms)) : 'Not specified') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No matches found in the specified rounds.</p>';
    }

    wp_reset_postdata();
}
add_shortcode('get_events_in_round_1', 'func_get_events_in_round_1');


function func_npl_league_players($atts)
{
    ob_start();

    // Get all teams in the NPL league
    $team_args = array(
        'post_type' => 'sp_team',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'sp_league',
                'field' => 'slug',
                'terms' => 'npl', // NPL league slug
            )
        ),
        'fields' => 'ids' // Only get team IDs
    );

    $team_ids = get_posts($team_args);

    if (empty($team_ids)) {
        return '<p>No teams found in the NPL league.</p>';
    }

    // Get all players from these teams
    $player_args = array(
        'post_type' => 'sp_player',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'sp_team',
                'value' => $team_ids,
                'compare' => 'IN'
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    );

    $players = new WP_Query($player_args);

    echo '<div class="npl-players-container">';
    echo '<h3>Players in NPL League</h3>';

    if ($players->have_posts()) {
        echo '<table class="player-table team-players-table">';
        echo '<thead>
                 <tr>
                     <th>#</th>
                     <th>Player</th>
                     <th>Power</th>
                 </tr>
               </thead>';
        echo '<tbody>';

        $count = 1;
        while ($players->have_posts()) {
            $players->the_post();
            $player_id = get_the_ID();
            $player_name = get_the_title();
            $player_image = get_the_post_thumbnail_url($player_id, 'thumbnail');
            $player_team_id = get_post_meta($player_id, 'sp_team', true);
            $player_team = $player_team_id ? get_the_title($player_team_id) : '—';
            $player_pos = get_post_meta($player_id, 'sp_position', true);
            $player_pwr = get_post_meta($player_id, 'player_pwr', true);

            echo '<tr>';
            echo '<td>' . esc_html($count++) . '</td>';
            echo '<td>';
            if ($player_image) {
                echo '<img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '" width="40" height="40" style="border-radius:50%; vertical-align:middle; margin-right:10px;">';
            }
            echo esc_html($player_name);
            echo '</td>';
            echo '<td>' . esc_html($player_pwr ? $player_pwr : '—') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No players found in the NPL league.</p>';
    }

    echo '</div>'; // .npl-players-container

    // Add responsive styling
    echo '
     <style>
         .npl-players-container {
             margin: 20px 0;
             overflow-x: auto;
         }
         .player-table {
             width: 100%;
             border-collapse: collapse;
             margin-top: 10px;
             font-size: 14px;
         }
         .player-table th, 
         .player-table td {
             padding: 10px 15px;
             text-align: left;
             border-bottom: 1px solid #eee;
         }
         .player-table th {
             background-color: #f5f5f5;
             font-weight: 600;
         }
         .player-table tr:hover {
             background-color: #f9f9f9;
         }
         @media (max-width: 768px) {
             .player-table {
                 font-size: 13px;
             }
             .player-table th, 
             .player-table td {
                 padding: 8px 10px;
             }
         }
     </style>
     ';

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('npl_league_players', 'func_npl_league_players');




// Tournaments Custom post type
// Tournament Pre Stat
function create_tournamentpreeventstate_cpt()
{
    $labels = array(
        'name' => _x('Tournament Pre Event States', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Tournament Pre Event State', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('Tournament Pre Event States', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Tournament Pre Event State', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('Tournament Pre Event State Archives', 'textdomain'),
        'attributes' => __('Tournament Pre Event State Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Tournament Pre Event State:', 'textdomain'),
        'all_items' => __('All Tournament Pre Event States', 'textdomain'),
        'add_new_item' => __('Add New Tournament Pre Event State', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Tournament Pre Event State', 'textdomain'),
        'edit_item' => __('Edit Tournament Pre Event State', 'textdomain'),
        'update_item' => __('Update Tournament Pre Event State', 'textdomain'),
        'view_item' => __('View Tournament Pre Event State', 'textdomain'),
        'view_items' => __('View Tournament Pre Event States', 'textdomain'),
        'search_items' => __('Search Tournament Pre Event State', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into Tournament Pre Event State', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this Tournament Pre Event State', 'textdomain'),
        'items_list' => __('Tournament Pre Event States list', 'textdomain'),
        'items_list_navigation' => __('Tournament Pre Event States list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Tournament Pre Event States list', 'textdomain'),
    );

    $args = array(
        'label' => __('Tournament Pre Event State', 'textdomain'),
        'description' => __('', 'textdomain'),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'),
        'taxonomies' => array('tournament_pre_category'), // Attach custom taxonomy
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 10,
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
    register_post_type('tournamentpreeventst', $args);
}
add_action('init', 'create_tournamentpreeventstate_cpt', 0);
// Register Custom Taxonomy
function create_tournament_pre_category_taxonomy()
{
    $labels = array(
        'name' => _x('Tournament Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('Tournament Category', 'Taxonomy Singular Name', 'textdomain'),
        'menu_name' => __('Tournament Categories', 'textdomain'),
        'all_items' => __('All Categories', 'textdomain'),
        'parent_item' => __('Parent Category', 'textdomain'),
        'parent_item_colon' => __('Parent Category:', 'textdomain'),
        'new_item_name' => __('New Category Name', 'textdomain'),
        'add_new_item' => __('Add New Category', 'textdomain'),
        'edit_item' => __('Edit Category', 'textdomain'),
        'update_item' => __('Update Category', 'textdomain'),
        'view_item' => __('View Category', 'textdomain'),
        'separate_items_with_commas' => __('Separate categories with commas', 'textdomain'),
        'add_or_remove_items' => __('Add or remove categories', 'textdomain'),
        'choose_from_most_used' => __('Choose from the most used', 'textdomain'),
        'popular_items' => __('Popular Categories', 'textdomain'),
        'search_items' => __('Search Categories', 'textdomain'),
        'not_found' => __('Not Found', 'textdomain'),
        'no_terms' => __('No categories', 'textdomain'),
        'items_list' => __('Categories list', 'textdomain'),
        'items_list_navigation' => __('Categories list navigation', 'textdomain'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true, // True for category-like behavior
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_in_rest' => true,
    );

    register_taxonomy('tournament_pre_category', array('tournamentpreeventst'), $args);
}
add_action('init', 'create_tournament_pre_category_taxonomy', 0);
/**
 * 
 * 
 */
// Tournament Post State
function create_tournamentposteventstate_cpt()
{
    $labels = array(
        'name' => _x('Tournament Post Event States', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Tournament Post Event State', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('Tournament Post Event States', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Tournament Post Event State', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('Tournament Post Event State Archives', 'textdomain'),
        'attributes' => __('Tournament Post Event State Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Tournament Post Event State:', 'textdomain'),
        'all_items' => __('All Tournament Post Event States', 'textdomain'),
        'add_new_item' => __('Add New Tournament Post Event State', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Tournament Post Event State', 'textdomain'),
        'edit_item' => __('Edit Tournament Post Event State', 'textdomain'),
        'update_item' => __('Update Tournament Post Event State', 'textdomain'),
        'view_item' => __('View Tournament Post Event State', 'textdomain'),
        'view_items' => __('View Tournament Post Event States', 'textdomain'),
        'search_items' => __('Search Tournament Post Event State', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into Tournament Post Event State', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this Tournament Post Event State', 'textdomain'),
        'items_list' => __('Tournament Post Event States list', 'textdomain'),
        'items_list_navigation' => __('Tournament Post Event States list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Tournament Post Event States list', 'textdomain'),
    );

    $args = array(
        'label' => __('Tournament Post Event State', 'textdomain'),
        'description' => __('', 'textdomain'),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'),
        'taxonomies' => array('tournament_post_category'), // Attach custom taxonomy
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 10,
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
    register_post_type('tournamentpostevents', $args);
}
add_action('init', 'create_tournamentposteventstate_cpt', 0);
// Register Custom Taxonomy
function create_tournament_post_category_taxonomy()
{
    $labels = array(
        'name' => _x('Tournament Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('Tournament Category', 'Taxonomy Singular Name', 'textdomain'),
        'menu_name' => __('Tournament Categories', 'textdomain'),
        'all_items' => __('All Categories', 'textdomain'),
        'parent_item' => __('Parent Category', 'textdomain'),
        'parent_item_colon' => __('Parent Category:', 'textdomain'),
        'new_item_name' => __('New Category Name', 'textdomain'),
        'add_new_item' => __('Add New Category', 'textdomain'),
        'edit_item' => __('Edit Category', 'textdomain'),
        'update_item' => __('Update Category', 'textdomain'),
        'view_item' => __('View Category', 'textdomain'),
        'separate_items_with_commas' => __('Separate categories with commas', 'textdomain'),
        'add_or_remove_items' => __('Add or remove categories', 'textdomain'),
        'choose_from_most_used' => __('Choose from the most used', 'textdomain'),
        'popular_items' => __('Popular Categories', 'textdomain'),
        'search_items' => __('Search Categories', 'textdomain'),
        'not_found' => __('Not Found', 'textdomain'),
        'no_terms' => __('No categories', 'textdomain'),
        'items_list' => __('Categories list', 'textdomain'),
        'items_list_navigation' => __('Categories list navigation', 'textdomain'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true, // True for category-like behavior
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_in_rest' => true,
    );

    register_taxonomy('tournament_post_category', array('tournamentpostevents'), $args);
}
add_action('init', 'create_tournament_post_category_taxonomy', 0);

/**
 * 
 * 
 */

// Tournament Live State
function create_tournamentliveeventstate_cpt()
{
    $labels = array(
        'name' => _x('Tournament Live Event States', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Tournament Live Event State', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('Tournament Live Event States', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Tournament Live Event State', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('Tournament Live Event State Archives', 'textdomain'),
        'attributes' => __('Tournament Live Event State Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Tournament Live Event State:', 'textdomain'),
        'all_items' => __('All Tournament Live Event States', 'textdomain'),
        'add_new_item' => __('Add New Tournament Live Event State', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Tournament Live Event State', 'textdomain'),
        'edit_item' => __('Edit Tournament Live Event State', 'textdomain'),
        'update_item' => __('Update Tournament Live Event State', 'textdomain'),
        'view_item' => __('View Tournament Live Event State', 'textdomain'),
        'view_items' => __('View Tournament Live Event States', 'textdomain'),
        'search_items' => __('Search Tournament Live Event State', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into Tournament Live Event State', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this Tournament Live Event State', 'textdomain'),
        'items_list' => __('Tournament Live Event States list', 'textdomain'),
        'items_list_navigation' => __('Tournament Live Event States list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Tournament Live Event States list', 'textdomain'),
    );

    $args = array(
        'label' => __('Tournament Live Event State', 'textdomain'),
        'description' => __('', 'textdomain'),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-post',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'),
        'taxonomies' => array('tournament_category'), // Attach custom taxonomy
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 10,
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
    register_post_type('tournamentliveevents', $args);
}
add_action('init', 'create_tournamentliveeventstate_cpt', 0);
// Register Custom Taxonomy for Tournament Live Event States
function create_tournament_category_taxonomy()
{
    $labels = array(
        'name' => _x('Tournament Categories', 'Taxonomy General Name', 'textdomain'),
        'singular_name' => _x('Tournament Category', 'Taxonomy Singular Name', 'textdomain'),
        'menu_name' => __('Tournament Categories', 'textdomain'),
        'all_items' => __('All Categories', 'textdomain'),
        'parent_item' => __('Parent Category', 'textdomain'),
        'parent_item_colon' => __('Parent Category:', 'textdomain'),
        'new_item_name' => __('New Category Name', 'textdomain'),
        'add_new_item' => __('Add New Category', 'textdomain'),
        'edit_item' => __('Edit Category', 'textdomain'),
        'update_item' => __('Update Category', 'textdomain'),
        'view_item' => __('View Category', 'textdomain'),
        'separate_items_with_commas' => __('Separate categories with commas', 'textdomain'),
        'add_or_remove_items' => __('Add or remove categories', 'textdomain'),
        'choose_from_most_used' => __('Choose from the most used', 'textdomain'),
        'popular_items' => __('Popular Categories', 'textdomain'),
        'search_items' => __('Search Categories', 'textdomain'),
        'not_found' => __('Not Found', 'textdomain'),
        'no_terms' => __('No categories', 'textdomain'),
        'items_list' => __('Categories list', 'textdomain'),
        'items_list_navigation' => __('Categories list navigation', 'textdomain'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true, // Set to true if you want a category-like behavior
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_in_rest' => true,
    );

    register_taxonomy('tournament_category', array('tournamentliveevents'), $args);
}
add_action('init', 'create_tournament_category_taxonomy', 0);


function func_tournaments_pre_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'tournamentpreeventst',
        'posts_per_page' => -1, // Fetch all posts that match the criteria
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'tournament_pre_category',
                'field'    => 'slug',
                'terms'    => array(
                    'overview',
                    'about-the-tournament',
                    'schedule',
                    'prize-pool',
                    'faqs',
                    'upcoming-events',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Initialize storage for tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active';
        $added_categories = array(); // Track categories already added as tabs

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = wp_kses_post(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'tournament_pre_category');

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    if (!in_array($category_slug, $added_categories)) {
                        $tabs .= '<div class="tab ' . $active_class . '" data-target="' . $category_slug . '">' . $category_name . '</div>';
                        $added_categories[] = $category_slug;
                        $active_class = ''; // Reset after first tab
                    }

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
add_shortcode('tournaments_pre_tabs_page', 'func_tournaments_pre_tabs_page');


function func_tournaments_post_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'tournamentpostevents',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'tournament_post_category',
                'field'    => 'slug',
                'terms'    => array(
                    'tournament-winners',
                    'final-bracket',
                    'results',
                    'next-events',
                    'about-the-tournament',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Initialize storage for tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active';
        $added_categories = array(); // Track categories already added as tabs

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = wp_kses_post(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'tournament_post_category');

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    if (!in_array($category_slug, $added_categories)) {
                        $tabs .= '<div class="tab ' . $active_class . '" data-target="' . $category_slug . '">' . $category_name . '</div>';
                        $added_categories[] = $category_slug;
                        $active_class = ''; // Reset after first tab
                    }

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
add_shortcode('tournaments_post_tabs_page', 'func_tournaments_post_tabs_page');

function func_tournaments_live_tabs_page()
{
    ob_start();

    $args = array(
        'post_type'      => 'tournamentliveevents',
        'posts_per_page' => -1, // Fetch all posts that match the criteria
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'tournament_category',
                'field'    => 'slug',
                'terms'    => array(
                    'live-matches',
                    'schedule',
                    'fixtures-results',
                    'about-the-tournament',
                    'faqs',
                ),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Initialize storage for tabs and panels
        $tabs = '';
        $panels = '';
        $active_class = 'active';
        $added_categories = array(); // Track categories already added as tabs

        while ($query->have_posts()) {
            $query->the_post();
            $post_title = esc_html(get_the_title());
            $post_content = wp_kses_post(get_the_content());
            $categories = get_the_terms(get_the_ID(), 'tournament_category');

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_slug = esc_attr($category->slug);
                    $category_name = esc_html($category->name);

                    if (!in_array($category_slug, $added_categories)) {
                        $tabs .= '<div class="tab ' . $active_class . '" data-target="' . $category_slug . '">' . $category_name . '</div>';
                        $added_categories[] = $category_slug;
                        $active_class = ''; // Reset after first tab
                    }

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
add_shortcode('tournaments_live_tabs_page', 'func_tournaments_live_tabs_page');


function func_tournaments_templates()
{
    if (is_page_template('live_tournament-template.php')) {
        return do_shortcode('[tournaments_live_tabs_page]');
    }

    if (is_page_template('post_tournament-template.php')) {
        return do_shortcode('[tournaments_post_tabs_page]');
    }

    if (is_page_template('pre_tournament-template.php')) {
        return do_shortcode('[tournaments_pre_tabs_page]');
    }
}
add_shortcode('tournament_templates', 'func_tournaments_templates');


function func_pre_tournamanet_tab_overview()
{
    ob_start();
    ?>

    <div class="main_league_overview pre_tour_overview">
        <div class="league_overview_sec">
            <div class="league_overview_inner">
                <div class="league_overview_inner_l">
                    <h2><?php echo the_field('tournament_overview_heading'); ?></h2>
                </div>
                <div class="league_overview_inner_r">
                    <?php echo the_field('tournament_overview_content'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_venue_sec">
            <h2>5 Events</h2>
            <div class="league_overview_inner league_venue_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r league_venue_inner_r">
                    <?php echo the_field('tournament_5_events'); ?>
                </div>
            </div>
        </div>

        <div class="league_overview_sec league_team_composition_sec">
            <h2>Open and Intermediate Categories!</h2>
            <div class="league_overview_inner team_composition_content_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r team_composition_content_inner_r">
                    <?php echo the_field('tournament_open_and_immediate_categories'); ?>
                </div>
            </div>
        </div>


        <div class="league_overview_sec league_format_sec">
            <h2>Registration Costs</h2>
            <div class="league_overview_inner team_format_inner">
                <div class="league_overview_inner_l">

                </div>
                <div class="league_overview_inner_r team_format_inner_r">
                    <?php echo the_field('tournaments_registration_cost'); ?>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('pre_tournamanet_tab_overview', 'func_pre_tournamanet_tab_overview');


function func_pre_tournamanet_tab_about()
{
    ob_start();
?>
    <div class="league_overview_sec pre_tour_about_sec">

        <div class="league_overview_inner league_venue_inner">
            <div class="league_overview_inner_l">
                <section class="pre_about_iframe">
                    <iframe width="100%" height="384" src="<?php echo the_field('tournament_about_video_code'); ?>" frameborder="0" allowfullscreen></iframe>
                </section>

                <?php echo the_field('tournament_about_video'); ?>
            </div>
            <div class="league_overview_inner_r league_venue_inner_r">
                <?php echo the_field('tournament_about_content'); ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pre_tournamanet_tab_about', 'func_pre_tournamanet_tab_about');

function func_pre_tournamanet_tab_schedule()
{
    ob_start();

    if (have_rows('tournament_schedule_fields')): // Check if the repeater field has rows
    ?>
        <div class="pre_league_key_dates_main">
            <div class="pre_league_key_dates_inner">
                <?php
                while (have_rows('tournament_schedule_fields')): the_row();
                    // Get subfield values
                    $tournament_date = get_sub_field('pre_tournament_date');
                    $tournament_text = get_sub_field('pre_tournament_text');
                    $tournament_status = get_sub_field('pre_tournament_status');
                ?>
                    <div class="pre_league_key_dates_items">
                        <h4><?php echo esc_html($tournament_date); ?></h4>
                        <h2><?php echo esc_html($tournament_text); ?></h2>
                        <h5><?php echo esc_html($tournament_status); ?></h5>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php
    else:
        // Display a fallback message if no rows exist
        echo '<p>No tournament schedule found.</p>';
    endif;

    return ob_get_clean();
}
add_shortcode('pre_tournamanet_tab_schedule', 'func_pre_tournamanet_tab_schedule');


function func_pre_tournamanet_tab_price_money()
{
    ob_start();

    ?>

    <table class="team-players-table price_poll_pre_tour" width="100%" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th>Category</th>
                <th>GOLD</th>
                <th>SILVER</th>
                <th>SEMI FINALIST/ BRONZE</th>
                <th>Quarter Finalists</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Single</td>
                <td><?php echo the_field('tournament_single_gold'); ?></td>
                <td><?php echo the_field('tournament_single_sliver'); ?></td>
                <td><?php echo the_field('tournament_single_bronze'); ?></td>
                <td><?php echo the_field('tournament_single_quarter_finalists'); ?></td>
            </tr>
            <tr>
                <td>Doubles</td>
                <td><?php echo the_field('tournament_double_gold'); ?></td>
                <td><?php echo the_field('tournament_double_sliver'); ?></td>
                <td><?php echo the_field('tournament_double_bronze'); ?></td>
                <td><?php echo the_field('tournament_double_quarter_finalists'); ?></td>
            </tr>
            <tr>
                <td>Mixed Doubles</td>
                <td><?php echo the_field('tournament_mix_double_gold'); ?></td>
                <td><?php echo the_field('tournament_mix_double_sliver'); ?></td>
                <td><?php echo the_field('tournament_mix_double_bronze'); ?></td>
                <td><?php echo the_field('tournament_mix_double_quarter_finalists'); ?></td>
            </tr>
        </tbody>


    </table>



    <?php
    return ob_get_clean();
}
add_shortcode('pre_tournamanet_tab_price_money', 'func_pre_tournamanet_tab_price_money');


function func_pre_tournamanet_tab_upcoming_event()
{
    ob_start();

    // Get the current date in 'Ymd' format
    $current_date = date('Ymd');

    // Query arguments to fetch upcoming or ongoing events
    $args = array(
        'post_type'      => 'sp_tournament',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'event_start_date', // Filter by the meta key
        'meta_value'     => $current_date,     // Compare with the current date
        'meta_compare'   => '>=',              // Include events on or after today
        'orderby'        => 'meta_value',
        'order'          => 'ASC',             // Show events in ascending order
    );

    $events_query = new WP_Query($args); // Initialize the query
    echo '<div class="events-list pre_tour_upcoming_event">';
    echo "<ul>";

    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();

            // Background image
            $bg_image = '';
            if (has_post_thumbnail()) {
                $bg_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $bg_image = $bg_image[0];
            }

            // Sponsor logo
            $sponsor_logo_id = get_post_meta(get_the_ID(), 'event_cover_image', true);
            $sponsor_logo = $sponsor_logo_id ? wp_get_attachment_url($sponsor_logo_id) : '';

            // Event date
            $event_date = get_post_meta(get_the_ID(), 'event_start_date', true);
            $formatted_event_date = $event_date ? DateTime::createFromFormat('Ymd', $event_date)->format('F j, Y') : '';

            echo "<li>";
            echo '<a href="' . site_url() . '/tournaments-post-event-states/" class="event-link">';

            // Venue details
            $venue = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_name = ($venue && !is_wp_error($venue)) ? $venue[0]->name : '';

            // Single event container
            echo '<div class="single-event" data-venue-id="' . esc_attr($venue_name) . '"' . ($sponsor_logo ? ' style="background-image: url(' . esc_url($sponsor_logo) . '); background-size: cover; background-position: center;"' : '') . '>';

            // Event badge
            $event_badge = get_field('event_badge');
            echo '<span class="event-badge">' . esc_html($event_badge ? $event_badge : 'PWR') . '</span>';

            // Logo image
            $logo_image = get_field('logo_image');
            if ($logo_image && is_array($logo_image)) {
                echo '<img src="' . esc_url($logo_image['url']) . '" class="event-logo" alt="' . esc_attr($logo_image['alt'] ?? 'Event Logo') . '">';
            } else {
                echo '<img src="' . esc_url(site_url('/wp-content/uploads/2025/03/npl-leagues.png')) . '" class="event-logo" alt="NPL Leagues Logo">';
            }

            // Event title
            echo '<h2>' . get_the_title() . '</h2>';

            // Venue name
            echo '<span class="event-venue">' . esc_html($venue_name) . '</span>';

            echo '</div>'; // Close single-event div

            // Event date and format
            echo '<div class="event-date-wrapper">';
            if ($formatted_event_date) {
                echo '<span class="event-date">' . esc_html($formatted_event_date) . '</span>';
            }

            $format = get_post_meta(get_the_ID(), 'tournament_format', true);
            if ($format) {
                echo '<span class="event-format">' . esc_html($format) . '</span>';
            }
            echo '</div>'; // Close event-date-wrapper div

            echo '</a>'; // Close event-link
            echo "</li>";
        }
    } else {
        echo '<p class="no-events">No upcoming events found.</p>';
    }

    wp_reset_postdata(); // Reset post data
    echo "</ul>";
    echo '</div>'; // Close events-list events-slider div
    return ob_get_clean();
}
add_shortcode('pre_tournamanet_tab_upcoming_event', 'func_pre_tournamanet_tab_upcoming_event');


function func_pre_tour_gallery_sponsor()
{
    ob_start();
    echo '<h2 class="heading_underline">Partners & Sponsors</h2>';

    $images = get_field('tournment_sponsor_gallery');
    if ($images): ?>
        <ul class="pre_tour_sponsor">
            <?php foreach ($images as $image): ?>
                <li>
                    <img src="<?php echo $image['sizes']['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" />
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif;

    return ob_get_clean();
}
add_shortcode('pre_tour_gallery_sponsor', 'func_pre_tour_gallery_sponsor');


function func_pre_tour_recent_anouncement()
{
    ob_start();
    ?>
    <?php
    // Query posts from category with ID 9
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 2, // Show all posts
        'cat' => 9, // Replace with your desired category ID
    );

    $query = new WP_Query($args);
    echo '<h2 class="heading_underline">Recent Announcements</h2>';
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
add_shortcode('pre_tour_recent_anouncement', 'func_pre_tour_recent_anouncement');


function func_pre_tour_shop()
{
    ob_start();
    echo '<h2 class="heading_underline">Shop</h2>';
    echo do_shortcode('[products limit="4" columns="4" best_selling="true"]');
    return ob_get_clean();
}
add_shortcode('pre_tour_shop', 'func_pre_tour_shop');

function func_pre_tour_news()
{
    ob_start();

    // Query posts from category with ID 9
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 4, // Show all posts
        'cat' => 9, // Replace with your desired category ID
    );

    $query = new WP_Query($args);
    echo '<h2 class="heading_underline">More News</h2>';
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
add_shortcode('pre_tour_news', 'func_pre_tour_news');


function func_live_tour_match($atts)
{
    ob_start();

    // Extract and sanitize attributes
    $atts = shortcode_atts([
        'match_id' => '',
    ], $atts, 'live_tour_match');

    $match_id = sanitize_text_field($atts['match_id']);

    // Check if match_id is valid
    if (empty($match_id) || !is_numeric($match_id)) {
        echo '<p>No valid match ID provided. Please specify a valid match ID.</p>';
        return ob_get_clean();
    }

    // Get the specific post by ID
    $post = get_post($match_id);

    if ($post && $post->post_type === 'sp_event') {
        setup_postdata($post);

        $post_id = $post->ID;
        $title = get_the_title($post_id);
        $event_metas = get_post_meta($post_id);
        $venue_terms = wp_get_post_terms($post_id, 'sp_venue', ['fields' => 'names']);

        // Safely retrieve meta values
        $match_type = sanitize_text_field($event_metas['match_type'][0] ?? 'N/A');
        $team1_image_src = wp_get_attachment_image_src($event_metas['match_type_icon'][0] ?? '', 'full');
        $team1_image = $team1_image_src[0] ?? '';
        $video_url = esc_url($event_metas['sp_video'][0] ?? '');
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full') ?: get_stylesheet_directory_uri() . '/assets/images/default-thumbnail.jpg';

        ?>
        <div class="mid-league-overview-container">
            <div class="event_overview">
                <div class="event_image_sec">
                    <div class="image_sec">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="Event Image" />
                    </div>
                    <div class="image_content">
                        <h3>Watch Live</h3>
                        <p>
                            <?php echo wp_trim_words(get_the_content($post_id), 14, ''); ?>
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/PlayButton-1.svg"
                                class="play-button"
                                data-video-id="video_<?php echo esc_attr($post_id); ?>" />
                        </p>
                    </div>
                </div>

                <div class="event_content_sec">
                    <div class="event-details">
                        <h3>Match</h3>
                        <h2 class="<?php echo esc_attr(sanitize_title($match_type)); ?>">
                            <?php echo esc_html($match_type); ?>
                        </h2>
                        <h5><?php echo !empty($venue_terms) ? esc_html(implode(', ', $venue_terms)) : 'Not specified'; ?></h5>
                    </div>
                    <div class="event-details2">
                        <h4>Players</h4>
                        <h3><?php echo esc_html($title); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popup Modal -->
        <?php if ($video_url): ?>
            <div class="replays_data_videos_inner">
                <div id="video_<?php echo esc_attr($post_id); ?>" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <iframe width="480" height="300" src="<?php echo esc_url($video_url); ?>" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>No video available for this event.</p>
        <?php endif; ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Open Modal
                const playButtons = document.querySelectorAll('.play-button');
                playButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const videoId = this.getAttribute('data-video-id');
                        const modal = document.getElementById(videoId);
                        if (modal) modal.style.display = 'block';
                    });
                });

                // Close Modal
                const closeButtons = document.querySelectorAll('.close');
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const modal = this.closest('.modal');
                        if (modal) modal.style.display = 'none';
                    });
                });

                // Close Modal on Outside Click
                window.addEventListener('click', function(event) {
                    const modals = document.querySelectorAll('.modal');
                    modals.forEach(modal => {
                        if (event.target === modal) {
                            modal.style.display = 'none';
                        }
                    });
                });
            });
        </script>
    <?php

        // Reset post data
        wp_reset_postdata();
    } else {
        echo '<p>No event found for the specified match ID.</p>';
    }

    return ob_get_clean();
}

add_shortcode('live_tour_match', 'func_live_tour_match');


function func_live_tournamanet_tab_schedule()
{
    ob_start();

    if (have_rows('live_tournament_schedule_fields')): // Check if the repeater field has rows
    ?>
        <div class="pre_league_key_dates_main">
            <div class="pre_league_key_dates_inner">
                <?php
                while (have_rows('live_tournament_schedule_fields')): the_row();
                    // Get the subfields
                    $tournament_date = get_sub_field('liv_tournament_date'); // Date field
                    $tournament_text = get_sub_field('liv_tournament_text'); // Text field
                    $tournament_status = get_sub_field('liv_tournament_status'); // Status field
                ?>
                    <div class="pre_league_key_dates_items">
                        <h4><?php echo esc_html($tournament_date); ?></h4>
                        <h2><?php echo esc_html($tournament_text); ?></h2>
                        <h5><?php echo esc_html($tournament_status); ?></h5>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php
    else:
        // Display a fallback message if no rows are found
        echo '<p>No tournament schedule available.</p>';
    endif;

    return ob_get_clean();
}
add_shortcode('live_tournamanet_tab_schedule', 'func_live_tournamanet_tab_schedule');


function func_live_tournamanet_tab_about()
{
    ob_start();
    ?>
    <div class="league_overview_sec pre_tour_about_sec">

        <div class="league_overview_inner league_venue_inner">
            <div class="league_overview_inner_l">
                <section class="pre_about_iframe">
                    <iframe width="100%" height="384" src="<?php echo the_field('live_tournament_about_video_code'); ?>" frameborder="0" allowfullscreen></iframe>
                </section>

                <?php echo the_field('live_tournament_about_video'); ?>
            </div>
            <div class="league_overview_inner_r league_venue_inner_r">
                <?php echo the_field('live_tournament_about_content'); ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('live_tournamanet_tab_about', 'func_live_tournamanet_tab_about');


function func_live_tournament_fixture_result() {
    ob_start();

    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1, 'order' => 'ASC'));
    ?>

    <form action="" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="filter-date">
                    <option value="">Select Date</option>
                    <?php
                    // Query for unique event dates
                    $unique_dates = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $post_date = get_the_date('F j, Y');

                            if (!in_array($post_date, $unique_dates)) {
                                $unique_dates[] = $post_date;
                                $formatted_date = date('F j, Y', strtotime($post_date));
                                echo '<option value="' . esc_html($formatted_date) . '">' . esc_html($formatted_date) . '</option>';
                            }
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<option value="">No dates available</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="round-select" name="round[]">
                    <option value="">Select Round</option>
                    <?php
                    $unique_rounds = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $rounds = get_post_meta(get_the_ID(), '_sp_labels', true) ?: [];
                            foreach ((array) $rounds as $round_group) {
                                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $round) {
                            echo '<option value="' . esc_attr($round) . '">' . esc_html($round) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="filter-venue" name="venue[]">
                    <option value="">Select Venue</option>
                    <?php
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_html($venue->name) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="filter-tournament">
                    <option value="">Select Tournament</option>
                    <?php
                    $unique_tournament_ids = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $tournament_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);

                            if ($tournament_id && !in_array($tournament_id, $unique_tournament_ids)) {
                                $unique_tournament_ids[] = $tournament_id;
                                $tournament_name = get_the_title($tournament_id);
                                echo '<option value="' . esc_html($tournament_name) . '">' . esc_html($tournament_name) . '</option>';
                            }
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<option value="">No tournaments available</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        $args = array(
            'post_type'      => 'sp_event',
            'posts_per_page' => -1,
            'order'          => 'ASC',
            'orderby' => 'ASC',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $matches_by_date = [];

            // Group matches by date
            while ($query->have_posts()) {
                $query->the_post();

                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $tournament_associate_name = get_the_title($tournament_associate_id);

                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true) ?: get_the_date('Y-m-d');
                $formatted_date = date('F j, Y', strtotime(get_the_date('F j, Y')));
                $timeposted = get_post_time('g:i A', false, get_the_ID(), true);

                if (!isset($matches_by_date[$formatted_date])) {
                    $matches_by_date[$formatted_date] = [];
                }

                $rounds = get_post_meta(get_the_ID(), '_sp_labels', true);
                $round_value = '';
                if (is_array($rounds)) {
                    foreach ($rounds as $round_group) {
                        if (is_array($round_group)) {
                            $round_value = implode(',', $round_group);
                        }
                    }
                }

                $matches_by_date[$formatted_date][] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'date' => date('F j, Y', strtotime($match_date)),
                    'time' => get_post_time('g:i A', false, get_the_ID(), true),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', ['fields' => 'names']),
                    'season' => wp_get_post_terms(get_the_ID(), 'sp_season', ['fields' => 'names']),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', ['fields' => 'names']),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => get_the_title($tournament_associate_id),
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'raw_date' => $match_date,
                    'state' => get_post_meta(get_the_ID(), 'state', true),
                ];

            }

            

            foreach ($matches_by_date as $date => $matches) {

                echo "<pre>";
                print_r($matches['rounds']);
                echo "</pre>";

                echo '<div class="match-date-group" data-date="' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '">';
                echo '<h2 class="heading_underline">' . esc_html($date) . '</h2>';

                foreach ($matches as $match) {
                    $teams_scores = [];
                    $team_names = [];

                    if (!empty($match['results'])) {
                        foreach ($match['results'] as $team_id => $result) {
                            $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                        }
                    }

                    if (!empty($match['players'])) {
                        foreach ($match['players'] as $team_id => $players) {
                            $team_names[$team_id] = get_the_title($team_id);
                        }
                    }

                    $team_ids = array_keys($teams_scores);

                    echo "<pre>";
                    print_r($matches['rounds']);
                    echo "</pre>";

                    echo '<div class="match" 
                            data-date="' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '"
                            data-tournament="' . htmlspecialchars($match['tournament_assoc'], ENT_QUOTES, 'UTF-8') . '"
                            data-venue="' . htmlspecialchars(!empty($match['venue'][0]) ? $match['venue'][0] : '', ENT_QUOTES, 'UTF-8') . '"
                            data-rounds="' . htmlspecialchars(!empty($match['rounds']) ? $match['rounds'] : '', ENT_QUOTES, 'UTF-8') . '"
                            data-title="' . htmlspecialchars($match['title'], ENT_QUOTES, 'UTF-8') . '">';
                    
                    echo '<h3 class="turna_time_s">' . esc_html($match['time']) . '</h3>';

                    echo '<table class="team-players-table">';
                    echo '<thead>
                            <tr>
                                <th>Match No</th>
                                <th>Team 1</th>
                                <th>Score</th>
                                <th>Team 2</th>
                                <th>Venue</th>
                            </tr>
                        </thead>';
                    echo '<tbody>';

                    echo '<tr>';
                    echo '<td>1 <input type="hidden" value="'.esc_html($match['rounds']).'"></td>';
                    echo '<td>' . $match['player1'] . '</td>';
                    echo '<td>' . esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0') . '</td>';
                    echo '<td>' . $match['player2'] . '</td>';
                    echo '<td>' . (!empty($match['venue']) ? esc_html(implode(', ', $match['venue'])) : 'Not specified') . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td colspan="5" class="tr_2_styling"> ' . (!empty($match['event_type'][0]) ? $match['event_type'][0] : '') . ' | <a href="' . site_url() . '/gallery">Gallery</a></td>';                          
                    echo '</tr>';

                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                }
                echo '</div>';
            }
        ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dateFilter = document.getElementById('filter-date');
        const tournamentFilter = document.getElementById('filter-tournament');
        const venueFilter = document.getElementById('filter-venue');
        const roundFilter = document.getElementById('round-select');

        if (!dateFilter || !tournamentFilter || !venueFilter || !roundFilter) {
            console.error('One or more filter elements are missing.');
            return;
        }

        const dateGroups = document.querySelectorAll('.matches-container .match-date-group');
        const matches = document.querySelectorAll('.matches-container .match');

        function filterMatches() {
            const filterTournament = tournamentFilter.value;
            const filterDate = dateFilter.value;
            const filterVenue = venueFilter.value;
            const filterRound = roundFilter.value;

            // First hide all matches
            matches.forEach(match => {
                match.style.display = 'none';
            });

            // Then show matching ones
            matches.forEach(match => {
                const tournament = match.getAttribute('data-tournament');
                const date = match.getAttribute('data-date');
                const venue = match.getAttribute('data-venue');
                const rounds = match.querySelector('input[type="hidden"]').value; // Note: Updated to match the data attribute

                const show =
                    (!filterTournament || tournament === filterTournament) &&
                    (!filterDate || date === filterDate) &&
                    (!filterVenue || venue === filterVenue) &&
                    (!filterRound || (rounds && rounds.split(',').includes(filterRound))); // Adjusted round filter logic

                if (show) {
                    match.style.display = 'block';
                }
            });

            // Handle date group headings - show if any matches are visible in the group
            dateGroups.forEach(group => {
                const groupDate = group.getAttribute('data-date');
                const hasVisibleMatches = Array.from(group.querySelectorAll('.match')).some(
                    match => match.style.display !== 'none'
                );

                group.style.display = (filterDate && groupDate !== filterDate) ? 'none' :
                                     (hasVisibleMatches ? 'block' : 'none');
            });
        }

        [tournamentFilter, dateFilter, venueFilter, roundFilter].forEach(filter => {
            filter.addEventListener('change', filterMatches);
        });

        // Initial filtering
        filterMatches();
    });
    </script>


        <?php
            wp_reset_postdata();
        } else {
            echo '<p>No events found.</p>';
        }
        ?>
    </div>

    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('live_tournament_fixture_result', 'func_live_tournament_fixture_result');






function func_tournament_head($atts)
{
    ob_start();

    // Sanitize and extract attributes
    $atts = shortcode_atts([
        'season' => '',
        'type'   => '',
    ], $atts);

    $season = sanitize_text_field($atts['season']);
    $tournament_type = sanitize_text_field($atts['type']);

    // Query for the latest tournament
    $tournament_args = [
        'post_type'      => 'sp_tournament',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    $tournament_query = new WP_Query($tournament_args);

    if ($tournament_query->have_posts()) {
        while ($tournament_query->have_posts()) {
            $tournament_query->the_post();

            $tournament_id = get_the_ID();
            $tournament_name = get_the_title();
            $league_logo = get_the_post_thumbnail_url($tournament_id, 'full');
            $start_date = get_post_meta($tournament_id, 'event_start_date', true);
            $end_date = get_post_meta($tournament_id, 'event_end_date', true);
            $sponsor_logo_id = get_post_meta($tournament_id, 'tournaments_head_sponsor_logo', true);
            $sponsor_logo = $sponsor_logo_id ? wp_get_attachment_url($sponsor_logo_id) : '';
            $tournament_format = get_post_meta($tournament_id, 'tournament_format', true);
            $tournament_state = get_post_meta($tournament_id, 'tournament_state', true);

            // Format dates
            $formatted_start_date = 'No date set';
            $formatted_end_date = 'No date set';

            if (!empty($start_date)) {
                $date_obj = DateTime::createFromFormat('Ymd', $start_date);
                $formatted_start_date = $date_obj ? $date_obj->format('F j') : 'Invalid date';
            }

            if (!empty($end_date)) {
                $date_obj = DateTime::createFromFormat('Ymd', $end_date);
                $formatted_end_date = $date_obj ? $date_obj->format('F j') : 'Invalid date';
            }
    ?>
            <div class="team_main_head tournemanet_main_head">
                <div class="team_heads">
                    <div class="team_heads_l">
                        <div class="ournment_logo">
                            <?php if ($league_logo): ?>
                                <img src="<?php echo esc_url($league_logo); ?>" alt="<?php echo esc_attr($tournament_name); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="team_details">
                            <h5>
                                <?php if ($tournament_format): ?>
                                    <span><?php echo esc_html($tournament_format); ?></span>
                                <?php endif; ?>
                                <?php echo esc_html($season); ?>
                            </h5>
                            <h2><?php echo esc_html($tournament_name); ?></h2>

                            <h6>
                                <div class="">
                                    <?php echo esc_html($tournament_state); ?>, Australia
                                </div>
                                <div class="">
                                    <?php echo esc_html("$formatted_start_date - $formatted_end_date"); ?>
                                </div>
                            </h6>

                            <?php
                            // Display buttons based on tournament type with fallback logic

                            $button_live_groups = 'tournaments_head_live_buttons';
                            $button_pre_groups = 'tournaments_head_pre_buttons';
                            $button_post_groups = 'tournaments_head_post_buttons';

                            $selected_group = '';
                            if (is_page_template('live_tournament-template.php') || $tournament_type === 'live') {
                                $selected_group = $button_live_groups;
                            } elseif (is_page_template('pre_tournament-template.php') || $tournament_type === 'pre') {
                                $selected_group = $button_pre_groups;
                            } elseif (is_page_template('post_tournament-template.php') || $tournament_type === 'post') {
                                $selected_group = $button_post_groups;
                            }


                            

                            if ($selected_group || have_rows($selected_group, $tournament_id)): ?>
                                <ul class="team_details_btns">
                                    <?php
                                    // Loop through each button in the selected group
                                    while (have_rows($selected_group, $tournament_id)): the_row();
                                        $button_text = get_sub_field('button_text');
                                        $button_link = get_sub_field('button_link');
                                        
                                        // echo "<pre>";
                                        // print_r($button_text);
                                        // print_r($button_link);
                                        // echo "</pre>";
                                        
                                        //if (!empty($button_text) && !empty($button_link)): 
                                        ?>
                                            <li>
                                                <a href="<?php echo esc_url($button_link); ?>" class="btn-<?php echo esc_attr(str_replace('tournaments_head_', '', $selected_group)); ?>">
                                                    <?php echo esc_html($button_text); ?>
                                                </a>
                                            </li>
                                    <?php 
                                    //endif;
                                    endwhile; ?>
                                </ul>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php if ($sponsor_logo): ?>
                        <div class="sponsor_logo team_heads_r">
                            <img decoding="async" src="<?php echo esc_url($sponsor_logo); ?>" alt="Sponsor Logo">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    <?php
        }
    } else {
        echo '<p>' . esc_html__('No tournaments found.', 'text-domain') . '</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('tournament_head', 'func_tournament_head');



function func_tournament_gallery()
{
    ob_start();

    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1, 'order' => 'ASC'));
    ?>

	<div class="tournament_page_bar">
		<a href="<?php echo site_url(); ?>/tournaments-post-event-states/">Back to Tournament</a>
		<span><i class="fa-solid fa-chevron-right"></i></span>
	</div>
	
	<div class="gallery_page_title">
		<h2>
			Gallery
		</h2>	
	</div>
			
    <form action="" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="filter-date">
                    <option value="">Select Date</option>
                    <?php
                    // Query for unique event dates
                    $unique_dates = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $post_date = get_the_date('Y-m-d');

                            if (!in_array($post_date, $unique_dates)) {
                                $unique_dates[] = $post_date;
                                $formatted_date = date('F j, Y', strtotime($post_date));
                                echo '<option value="' . esc_html($formatted_date) . '">' . esc_html($formatted_date) . '</option>';
                            }
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<option value="">No dates available</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="round-select" name="round[]">
                    <option value="">Select Round</option>
                    <?php
                    $unique_rounds = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $rounds = get_post_meta(get_the_ID(), '_sp_labels', true) ?: [];
                            foreach ((array) $rounds as $round_group) {
                                $unique_rounds = array_merge($unique_rounds, (array) $round_group);
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $round) {
                            echo '<option value="' . esc_attr($round) . '">' . esc_html($round) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- <div class="form-group">
                 <select id="filter-tourna_matches">
                     <option value="">Match</option>
                     <?php
                        if ($event_args->have_posts()) {
                            $match_count = 0;
                            while ($event_args->have_posts()) {
                                $event_args->the_post();
                                $match_count++;
                                echo '<option value="match-' . esc_html(get_the_title()) . '">' . get_the_title() . '</option>';
                            }
                            wp_reset_postdata();
                        } else {
                            echo '<option value="">No matches available</option>';
                        }
                        ?>
                 </select>
             </div> -->

            <div class="form-group">
                <select id="filter-venue" name="venue[]">
                    <option value="">Select Venue</option>
                    <?php
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_html($venue->name) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="filter-tournament">
                    <option value="">Select Tournament</option>
                    <?php
                    $unique_tournament_ids = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $tournament_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);

                            if ($tournament_id && !in_array($tournament_id, $unique_tournament_ids)) {
                                $unique_tournament_ids[] = $tournament_id;
                                $tournament_name = get_the_title($tournament_id);
                                echo '<option value="' . esc_html($tournament_name) . '">' . esc_html($tournament_name) . '</option>';
                            }
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<option value="">No tournaments available</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        $args = array(
            'post_type'      => 'sp_event',
            'posts_per_page' => -1,
            'order'          => 'ASC',
        );

        $query = new WP_Query($args);


        if ($query->have_posts()) {
            $matches_by_date = [];

            // Group matches by date
            while ($query->have_posts()) {
                $query->the_post();

                $event_meta_da = get_post_meta(get_the_ID());
                // echo "<pre>";
                // print_r($event_meta_da);
                // echo "</pre>";



                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $tournament_associate_name = get_the_title($tournament_associate_id);



                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true);
                $formatted_date = date('F j, Y', strtotime(get_the_date('F j, Y')));

                $matches_by_date[$formatted_date][] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', array('fields' => 'names')),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'event_meta' => maybe_unserialize(get_post_meta(get_the_ID(), 'event_gallery', true)),
                    'tournament_assoc' => $tournament_associate_name,
                    'event_date' => $formatted_date,
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                ];
            }

            // Display tables grouped by date
            foreach ($matches_by_date as $date => $matches) {

                // echo "<pre>";
                // print_r($matches);
                // echo "<pre>";


                    echo '<div class="match" 
                        data-date="' . htmlspecialchars($matches[0]['event_date'], ENT_QUOTES, 'UTF-8') . '"
                        data-tournament="' . htmlspecialchars($matches[0]['tournament_assoc'], ENT_QUOTES, 'UTF-8') . '"
                        data-venue="' . htmlspecialchars($matches[0]['venue'][0], ENT_QUOTES, 'UTF-8') . '"
                        data-rounds="' . htmlspecialchars($matches[0]['rounds'], ENT_QUOTES, 'UTF-8') . '"
                        data-title="' . htmlspecialchars($matches[0]['title'], ENT_QUOTES, 'UTF-8') . '"
                    >';
                    echo '<h2 class="heading_underline">' . esc_html($date) . '</h2>';
                    echo '<table class="team-players-table">';
                    echo '<thead>
                    <tr>
                        <th>Match No</th>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>Team 2</th>
                        <th>Venue</th>
                    </tr>
                    </thead>';
                    echo '<tbody>';

                    $match_no = 1;
                    foreach ($matches as $match) {
                        $teams_scores = [];
                        $team_names = [];

                        // Extract scores from results
                        if (!empty($match['results'])) {
                            foreach ($match['results'] as $team_id => $result) {
                                $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                            }
                        }

                        // Extract team names from players
                        if (!empty($match['players'])) {
                            foreach ($match['players'] as $team_id => $players) {
                                $team_names[$team_id] = get_the_title($team_id);
                            }
                        }

                        $team_ids = array_keys($teams_scores);

                        // echo "<pre>";
                        // print_r($match);
                        // echo "</pre>";
                        echo '<input type="hidden" id="event_tournament_name" class="event_tournament_name" value="' . $match['tournament_assoc'] . '">';
                        echo '<input type="hidden" id="event_tournament_date" class="event_tournament_date" value="' . $match['event_date'] . '">';
                        echo '<input type="hidden" id="event_tournament_venue" class="event_tournament_venue" value="' . esc_html(implode(', ', $match['venue'])) . '">';
                        echo '<input type="hidden" id="event_tournament_round" class="event_tournament_round" value="' . esc_html($match['rounds']) . '">';
                        echo '<input type="hidden" id="event_tournament_match" class="event_tournament_match" value="' . esc_html($match['title']) . '">';

                        // Display match row
                        echo '<tr>';
                        echo '<td>' . $match_no . '</td>';
                        echo '<td>' . (!empty($team_names[$team_ids[0]]) ? esc_html($team_names[$team_ids[0]]) : 'Team 1') . '</td>';
                        echo '<td>' . (!empty($teams_scores[$team_ids[0]]) ? esc_html($teams_scores[$team_ids[0]]) : '0') . ' - ' .
                            (!empty($teams_scores[$team_ids[1]]) ? esc_html($teams_scores[$team_ids[1]]) : '0') . '</td>';
                        echo '<td>' . (!empty($team_names[$team_ids[1]]) ? esc_html($team_names[$team_ids[1]]) : 'Team 2') . '</td>';
                        echo '<td>' . (!empty($match['venue']) ? esc_html(implode(', ', $match['venue'])) : 'Not specified') . '</td>';
                        echo '</tr>';

                        if (!empty($match['event_meta'])) {
                            echo '<tr>
                                <td class="hover_out" colspan="5">';
                            $image_data = [];
                            foreach ($match['event_meta'] as $attachment_id) {
                                $image_url = wp_get_attachment_image_src($attachment_id, 'full')[0];
                                $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'full')[0];
                                $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

                                $image_data[] = [
                                    'url' => $image_url,
                                    'thumbnail' => $thumbnail_url,
                                    'alt' => $alt_text,
                                ];
                            }

                            echo '<ul class="main_gallery_images">';
                            foreach ($image_data as $index => $image) {
                                echo '<li class="gallery-item" onclick="openModal(' . htmlspecialchars(json_encode($image_data), ENT_QUOTES, 'UTF-8') . ', ' . $index . ')">';
                                echo '<img src="' . esc_url($image['thumbnail']) . '" alt="' . esc_attr($image['alt']) . '">';
                                echo '</li>';
                            }
                            echo '</ul>';

                            echo '</td>
                            </tr>';
                        }
                        $match_no++;
                    }

                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                
            }

            wp_reset_postdata();
        } else {
            echo '<p>No events found.</p>';
        }
        ?>
    </div>


    <!-- Modal HTML -->
    <div id="showcase-modal" class="showcase-modal">
        <div class="showcase-content">
            <div class="gallery_model_popup_data">
                <h2>Gallery</h2>

            </div>
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="showcase-image-container">
                <img id="showcase-main-image" class="showcase-main-image" src="" alt="">
                <div class="showcase-arrows">
                    <button id="prev-arrow" class="arrow" onclick="changeImage(-1)">&#10094;</button>
                    <button id="next-arrow" class="arrow" onclick="changeImage(1)">&#10095;</button>
                </div>
            </div>
            <div class="showcase-thumbnails"></div>

        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFilter = document.getElementById('filter-date');
            const tournamentFilter = document.getElementById('filter-tournament');
            const venueFilter = document.getElementById('filter-venue');
            const roundFilter = document.getElementById('round-select');

            if (!dateFilter) console.error('Date filter element is missing.');
            if (!tournamentFilter) console.error('Tournament filter element is missing.');
            if (!venueFilter) console.error('Venue filter element is missing.');
            if (!roundFilter) console.error('Round filter element is missing.');

            if (!dateFilter || !tournamentFilter || !venueFilter || !roundFilter) {
                console.error('One or more filter elements are missing.');
                return;
            }

            const matches = document.querySelectorAll('.matches-container .match');

            function filterMatches() {
                matches.forEach(match => {
                    const tournament = match.getAttribute('data-tournament') || '';
                    const date = match.getAttribute('data-date') || '';
                    const venue = match.getAttribute('data-venue') || '';
                    const rounds = match.getAttribute('data-rounds') || '';

                    const show =
                        (!tournamentFilter.value || tournamentFilter.value === tournament) &&
                        (!dateFilter.value || dateFilter.value === date) &&
                        (!venueFilter.value || venueFilter.value === venue) &&
                        (!roundFilter.value || roundFilter.value === rounds);

                    match.style.display = show ? 'block' : 'none';
                });
            }

            [tournamentFilter, dateFilter, venueFilter, roundFilter].forEach(filter => {
                filter.addEventListener('change', filterMatches);
            });

            // Initial filtering to ensure the state matches filters on page load
            filterMatches();
        });




        let currentIndex = 0;
        let currentImages = [];

        function openModal(images, index) {
            currentImages = images;
            currentIndex = index;

            updateMainImage();

            const thumbnailsContainer = document.querySelector(".showcase-thumbnails");
            thumbnailsContainer.innerHTML = "";
            currentImages.forEach((image, idx) => {
                const thumbnail = document.createElement("img");
                thumbnail.src = image.thumbnail;
                thumbnail.alt = image.alt;
                thumbnail.className = idx === currentIndex ? "active" : "";
                thumbnail.onclick = () => {
                    currentIndex = idx;
                    updateMainImage();
                };
                thumbnailsContainer.appendChild(thumbnail);
            });

            document.getElementById("showcase-modal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("showcase-modal").style.display = "none";
        }

        function changeImage(direction) {
            currentIndex = (currentIndex + direction + currentImages.length) % currentImages.length;
            updateMainImage();
        }

        function updateMainImage() {
            const mainImage = document.getElementById("showcase-main-image");
            mainImage.src = currentImages[currentIndex].url;
            mainImage.alt = currentImages[currentIndex].alt;

            const thumbnails = document.querySelectorAll(".showcase-thumbnails img");
            thumbnails.forEach((thumbnail, idx) => {
                thumbnail.className = idx === currentIndex ? "active" : "";
            });
        }
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('tournament_gallery', 'func_tournament_gallery');

// Settig o event tpage
// Add the meta box for associating a tournament with an event
function add_event_tournament_meta_box()
{
    add_meta_box(
        'event_tournament_meta',       // ID
        __('Tournament Details', 'your-text-domain'), // Title
        'display_event_tournament_meta_box', // Callback
        'sp_event',                    // Post type
        'side',                        // Context (side, normal, etc.)
        'default'                      // Priority
    );
}
add_action('add_meta_boxes', 'add_event_tournament_meta_box');

// Display the meta box in the event edit screen
function display_event_tournament_meta_box($post)
{
    // Retrieve the associated tournament ID
    $tournament_id = get_post_meta($post->ID, 'associated_tournament_id', true);

    // Fetch all tournaments
    $tournaments = get_posts(array(
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    // Dropdown for selecting a tournament
    echo '<label for="associated_tournament_id">' . __('Select Tournament', 'your-text-domain') . '</label>';
    echo '<select name="associated_tournament_id" id="associated_tournament_id" style="width:100%;">';
    echo '<option value="">' . __('None', 'your-text-domain') . '</option>';

    foreach ($tournaments as $tournament) {
        $selected = $tournament->ID == $tournament_id ? 'selected' : '';
        echo '<option value="' . esc_attr($tournament->ID) . '" ' . $selected . '>' . esc_html($tournament->post_title) . '</option>';
    }

    echo '</select>';
}

// Save the selected tournament when the event is saved
function save_event_tournament_meta_box($post_id)
{
    // Check if the tournament ID is set and update the meta field
    if (isset($_POST['associated_tournament_id'])) {
        update_post_meta($post_id, 'associated_tournament_id', sanitize_text_field($_POST['associated_tournament_id']));
    }
}
add_action('save_post_sp_event', 'save_event_tournament_meta_box');

// Display the associated tournament title in the event list in the admin panel
function add_event_tournament_column($columns)
{
    $columns['tournament'] = __('Tournament', 'your-text-domain');
    return $columns;
}
add_filter('manage_sp_event_posts_columns', 'add_event_tournament_column');

function display_event_tournament_column($column, $post_id)
{
    if ($column === 'tournament') {
        // Retrieve the associated tournament ID
        $tournament_id = get_post_meta($post_id, 'associated_tournament_id', true);

        if ($tournament_id) {
            // Display the tournament title
            $tournament_title = get_the_title($tournament_id);
            echo esc_html($tournament_title);
        } else {
            echo __('None', 'your-text-domain');
        }
    }
}
add_action('manage_sp_event_posts_custom_column', 'display_event_tournament_column', 10, 2);


// Add the meta box for associating a tournament round with an event
function sp_add_tournament_round_meta_box()
{
    add_meta_box(
        'sp_tournament_round_meta', // Meta box ID
        __('Tournament Round', 'your-text-domain'), // Title
        'sp_tournament_round_meta_callback', // Callback
        'sp_event', // Post type
        'side', // Context (side, normal, etc.)
        'default' // Priority
    );
}
add_action('add_meta_boxes', 'sp_add_tournament_round_meta_box');

// Display the meta box in the event edit screen
function sp_tournament_round_meta_callback($post)
{
    wp_nonce_field('sp_save_tournament_round', 'sp_tournament_round_nonce');

    // Retrieve the selected tournament round
    $sp_labels = get_post_meta($post->ID, '_sp_labels', true);

    echo '<label for="sp_labels">' . __('Select Tournament Round:', 'your-text-domain') . '</label>';
    echo '<select id="sp_labels" name="sp_labels" style="width:100%;">';
    echo '<option value="">' . __('-- Select Round --', 'your-text-domain') . '</option>';
    echo '<option value="Round 1" ' . selected($sp_labels, 'Round 1', false) . '>Round 1</option>';
    echo '<option value="Round 2" ' . selected($sp_labels, 'Round 2', false) . '>Round 2</option>';
    echo '<option value="Round 3" ' . selected($sp_labels, 'Round 3', false) . '>Round 3</option>';
    echo '<option value="Round 4" ' . selected($sp_labels, 'Round 4', false) . '>Round 4</option>';
    echo '<option value="Round 5" ' . selected($sp_labels, 'Round 5', false) . '>Round 5</option>';
    echo '</select>';
}

// Save the meta box data when the event is saved
function sp_save_tournament_round_meta($post_id)
{
    // Verify nonce
    if (!isset($_POST['sp_tournament_round_nonce']) || !wp_verify_nonce($_POST['sp_tournament_round_nonce'], 'sp_save_tournament_round')) {
        return;
    }

    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save or delete the meta value
    if (isset($_POST['sp_labels'])) {
        update_post_meta($post_id, '_sp_labels', sanitize_text_field($_POST['sp_labels']));
    } else {
        delete_post_meta($post_id, '_sp_labels');
    }
}
add_action('save_post_sp_event', 'sp_save_tournament_round_meta');

// Add a column to display the tournament round in the event admin list
function sp_add_event_round_column($columns)
{
    $columns['tournament_round'] = __('Tournament Round', 'your-text-domain');
    return $columns;
}
add_filter('manage_sp_event_posts_columns', 'sp_add_event_round_column');

// Display the tournament round in the admin list column
function sp_display_event_round_column($column, $post_id)
{
    if ($column === 'tournament_round') {
        $round = get_post_meta($post_id, '_sp_labels', true);
        echo esc_html($round ? $round : __('None', 'your-text-domain'));
    }
}
add_action('manage_sp_event_posts_custom_column', 'sp_display_event_round_column', 10, 2);


function func_post_tournament_winner()
{
    ob_start();

    // Query the sp_event posts
    $args = [
        'post_type'      => 'sp_event',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ];

    $events = get_posts($args);

    // Organize data by match type
    $data_by_match_type = [];
    foreach ($events as $event) {
        $match_type = get_post_meta($event->ID, 'match_type', true);
        $player1_id = get_post_meta($event->ID, 'e_select_player1', true);
        $player2_id = get_post_meta($event->ID, 'e_select_player2', true);
        $player1_points = (int)get_post_meta($event->ID, 'e_player1_points', true);
        $player2_points = (int)get_post_meta($event->ID, 'e_player2_points', true);

        // Initialize match type group
        if (!isset($data_by_match_type[$match_type])) {
            $data_by_match_type[$match_type] = [];
        }

        // Add points for Player 1
        if ($player1_id || $player1_points) {
            if (!isset($data_by_match_type[$match_type][$player1_id])) {
                $data_by_match_type[$match_type][$player1_id] = [
                    'name'   => $player1_id,
                    'points' => 0,
                ];
            }
            $data_by_match_type[$match_type][$player1_id]['points'] += $player1_points;
        }

        // Add points for Player 2
        if ($player2_id || $player2_points) {
            if (!isset($data_by_match_type[$match_type][$player2_id])) {
                $data_by_match_type[$match_type][$player2_id] = [
                    'name'   => $player2_id,
                    'points' => 0,
                ];
            }
            $data_by_match_type[$match_type][$player2_id]['points'] += $player2_points;
        }
    }

    // Sort players within each match type by points in descending order
    foreach ($data_by_match_type as $match_type => $players) {
        uasort($players, function ($a, $b) {
            return $b['points'] - $a['points'];
        });
        $data_by_match_type[$match_type] = array_slice($players, 0, 8, true); // Keep only the top 8 players
    }

    // Generate HTML Output
    echo '<div class="wainer_match_tabs">';
    echo '<ul class="tab-list">';
    foreach ($data_by_match_type as $match_type => $players) {
        echo '<li class="' . esc_attr(sanitize_title($match_type)) . '">';
        echo '<a href="#' . esc_attr(sanitize_title($match_type)) . '">' . esc_html($match_type) . '</a>';
        echo '</li>';
    }
    echo '</ul>';

    foreach ($data_by_match_type as $match_type => $players) {
        echo '<div id="' . esc_attr(sanitize_title($match_type)) . '" class="tab-contents">';
        echo '<h3 class="heading_underline">' . esc_html($match_type) . '</h3>';
        echo '<div class="winner_tourn_sec">';
        $rank = 1;
        foreach ($players as $player_id => $player) {
            $rank_suffix = get_rank_suffix($rank); // Get rank suffix (e.g., 'st', 'nd', 'rd', etc.)
            echo '<div class="winner_tourn_sec_iner">';
            echo '<div class="winner_tourn_sec_l">';
            echo '<div class=" winner_tab_sec ' . esc_attr(sanitize_title($match_type)) . '"></div>';
            echo '</div>';
            echo '<div class="winner_tourn_sec_e">';
            echo '<h3>' . esc_html($rank) . '<sup>' . esc_html($rank_suffix) . '</sup></h3>';
            echo '<h2>' . esc_html($player['name']) . '</h2>';
            echo '</div>';
            echo '</div>';
            $rank++;
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
?>

    <script>
        jQuery(document).ready(function($) {
            // Hide all tab contents initially
            $('.tab-contents').hide();

            // Add event listener for tab clicks
            $('.tab-list a').click(function(e) {
                e.preventDefault();

                const target = $(this).attr('href');

                // Hide all tab content and remove active class from tabs
                $('.tab-contents').hide();
                $('.tab-list li').removeClass('active'); // Remove 'active' class from all <li>
                $('.tab-list a').removeClass('active'); // Remove 'active' class from all <a>

                // Show the target tab content and mark the tab as active
                $(target).show();
                $(this).addClass('active'); // Add 'active' class to the clicked <a>
                $(this).closest('li').addClass('active'); // Add 'active' class to the parent <li>
            });

            // Activate the first tab by default and show the first tab content
            $('.tab-list li:first').addClass('active'); // Add 'active' class to the first <li>
            $('.tab-list a:first').addClass('active'); // Add 'active' class to the first <a>
            $('.tab-contents:first').show(); // Show the first tab content
        });
    </script>


<?php
    return ob_get_clean();
}
add_shortcode('post_tournament_winner', 'func_post_tournament_winner');

// Helper function to get rank suffix
function get_rank_suffix($rank)
{
    if ($rank % 100 >= 11 && $rank % 100 <= 13) {
        return 'th';
    }
    switch ($rank % 10) {
        case 1:
            return 'st';
        case 2:
            return 'nd';
        case 3:
            return 'rd';
        default:
            return 'th';
    }
}



function func_post_tournament_bracket_with_match_type_and_round_tabs()
{
    ob_start();

    // Fetch events
    $args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => -1,
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);

?>
    <?php

    if (!$query->have_posts()) {
        echo '<p>No matches found.</p>';
        return ob_get_clean();
    }

    $matches_by_type_and_round = [];

    while ($query->have_posts()) {
        $query->the_post();

        $match_type = get_post_meta(get_the_ID(), 'match_type', true);
        $round = maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true));


        $event_metas = get_post_meta(get_the_ID(), 'sp_players', true);
        $players_data = $event_metas;

        if (is_serialized($event_metas)) {
            $players_data = maybe_unserialize($event_metas);
        } elseif (is_string($event_metas) && is_array(json_decode($event_metas, true))) {
            $players_data = json_decode($event_metas, true);
        }

        // echo "<pre>";
        // print_r(get_the_title($players_data));
        // echo "</pre>";


        $matches_by_type_and_round[$match_type][$round][] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', array('fields' => 'names')),
            'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
            'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
            'event_meta' => maybe_unserialize(get_post_meta(get_the_ID(), 'event_gallery', true)),
            'tournament_assoc' => get_the_title(get_post_meta(get_the_ID(), 'associated_tournament_id', true)),
            'event_date' => get_the_date('F j, Y'),
            'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', array('fields' => 'names')),
        ];
    }

    wp_reset_postdata();
    ?>

    <div class="tournament-bracket-tabs">
        <!-- Match Type Tabs -->
        <ul class="tab-navigation match-type-tabs">
            <?php foreach (array_keys($matches_by_type_and_round) as $index => $match_type): ?>
                <li class="tab-item <?php echo esc_attr(sanitize_title($match_type)); ?>" data-tab="match-type-<?php echo $index; ?>">
                    <?php echo esc_html($match_type); ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="">
            <?php foreach ($matches_by_type_and_round as $match_type => $rounds): ?>
                <div class="tab-panel match-type-panel" id="match-type-<?php echo md5($match_type); ?>">

                    <!-- Round Tabs -->
                    <ul class="tab-navigation round-tabs">
                        <?php foreach (array_keys($rounds) as $round_index => $round): ?>
                            <li class="tab-item" data-tab="round-<?php echo md5($round); ?>">
                                <?php echo esc_html($round); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="">
                        <?php foreach ($rounds as $round => $matches): ?>
                            <div class="tab-panel round-panel " .$round.'" id="round-<?php echo md5($round); ?>">
                                <h2 class="heading_underline"><?php echo esc_html($round); ?></h2>
                                <table class="team-players-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Team 1</th>
                                            <!-- <th>Team 1</th> -->
                                            <th>Score</th>
                                            <!-- <th>Player 2</th> -->
                                            <th>Team 2</th>
                                            <th>Venue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($matches as $match):
                                            $teams_scores = [];
                                            $team_names = [];
                                            $team_players = [];

                                            // Extract scores from results
                                            if (!empty($match['results'])) {
                                                foreach ($match['results'] as $team_id => $result) {
                                                    $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                                                }
                                            }

                                            // Extract team names and players
                                            if (!empty($match['players'])) {
                                                foreach ($match['players'] as $team_id => $players) {
                                                    $team_names[$team_id] = get_the_title($team_id);
                                                    $team_players[$team_id] = array_map('get_the_title', $players);
                                                }
                                            }

                                            $team_ids = array_keys($teams_scores);

                                            // $players_data = $match[];



                                        ?>
                                            <tr>
                                                <td><?php echo esc_html($match['event_date']); ?></td>
                                                <td><?php echo !empty($team_names[$team_ids[0]]) ? esc_html($team_names[$team_ids[0]]) : 'Team 1'; ?></td>
                                                <!-- <td><?php echo !empty($team_players[$team_ids[0]]) ? esc_html(implode(', ', $team_players[$team_ids[0]])) : 'Player 1'; ?></td> -->
                                                <td>
                                                    <?php echo !empty($teams_scores[$team_ids[0]]) ? esc_html($teams_scores[$team_ids[0]]) : '0'; ?>
                                                    -
                                                    <?php echo !empty($teams_scores[$team_ids[1]]) ? esc_html($teams_scores[$team_ids[1]]) : '0'; ?>
                                                </td>
                                                <!-- <td><?php echo !empty($team_players[$team_ids[1]]) ? esc_html(implode(', ', $team_players[$team_ids[1]])) : 'Player 2'; ?></td> -->
                                                <td><?php echo !empty($team_names[$team_ids[1]]) ? esc_html($team_names[$team_ids[1]]) : 'Team 2'; ?></td>
                                                <td><?php echo !empty($match['venue']) ? esc_html(implode(', ', $match['venue'])) : 'Not specified'; ?></td>
                                            </tr>
                                            <tr>
                                                <?php
                                                echo '<td colspan="5" class="tr_2_styling"> ' . $match['event_type'][0] . ' | <a href="' . site_url() . '/gallery">Gallery</td>';
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const setupTabs = (parentSelector, tabSelector, panelSelector) => {
                const tabs = document.querySelectorAll(`${parentSelector} ${tabSelector}`);
                const panels = document.querySelectorAll(`${parentSelector} ${panelSelector}`);

                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        panels.forEach(p => p.classList.remove('active'));

                        tab.classList.add('active');
                        panels[index].classList.add('active');
                    });

                    if (index === 0) {
                        tab.classList.add('active');
                        panels[index].classList.add('active');
                    }
                });
            };

            setupTabs('.tournament-bracket-tabs', '.match-type-tabs .tab-item', '.match-type-panel');
            document.querySelectorAll('.match-type-panel').forEach(panel => {
                setupTabs(`#${panel.id}`, '.round-tabs .tab-item', '.round-panel');
            });
        });
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('post_tournament_bracket_tabs', 'func_post_tournament_bracket_with_match_type_and_round_tabs');


function func_post_tournament_result()
{
    ob_start();

    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
    $event_args = new WP_Query(array('post_type' => 'sp_event', 'posts_per_page' => -1, 'order' => 'ASC'));
?>

    <form action="" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="filter-tournament">
                    <option value="">Select Tournament</option>
                    <?php
                    $unique_tournament_ids = [];
                    if ($event_args->have_posts()) {
                        while ($event_args->have_posts()) {
                            $event_args->the_post();
                            $tournament_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                            if ($tournament_id && !in_array($tournament_id, $unique_tournament_ids)) {
                                $unique_tournament_ids[] = $tournament_id;
                                $tournament_name = get_the_title($tournament_id);
                                echo '<option value="' . esc_html($tournament_name) . '">' . esc_html($tournament_name) . '</option>';
                            }
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<option value="">No tournaments available</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <div class="matches-container">
        <?php
        $args = array(
            'post_type'      => 'sp_event',
            'posts_per_page' => -1,
            'order'          => 'ASC',
            'orderby' => 'ASC',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $matches_by_date = [];

            // Group matches by date
            while ($query->have_posts()) {
                $query->the_post();

                $tournament_associate_id = get_post_meta(get_the_ID(), 'associated_tournament_id', true);
                $tournament_associate_name = get_the_title($tournament_associate_id);

                $match_date = get_post_meta(get_the_ID(), '_wp_old_date', true) ?: get_the_date('Y-m-d');
                $formatted_date = date('F j, Y', strtotime(get_the_date('F j, Y')));
                $timeposted = get_post_time('g:i A', false, get_the_ID(), true);

                if (!isset($matches_by_date[$formatted_date])) {
                    $matches_by_date[$formatted_date] = [];
                }

                $matches_by_date[$formatted_date][] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'time' => $timeposted,
                    'venue' => wp_get_post_terms(get_the_ID(), 'sp_venue', array('fields' => 'names')),
                    'results' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_results', true)),
                    'players' => maybe_unserialize(get_post_meta(get_the_ID(), 'sp_players', true)),
                    'player1' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player1', true)),
                    'player2' => maybe_unserialize(get_post_meta(get_the_ID(), 'e_select_player2', true)),
                    'tournament_assoc' => $tournament_associate_name,
                    'rounds' => maybe_unserialize(get_post_meta(get_the_ID(), '_sp_labels', true)),
                    'event_type' => wp_get_post_terms(get_the_ID(), 'event-type', array('fields' => 'names')),
                ];
            }

            // Display matches in a table
            echo '<table class="team-players-table">';

            echo '<thead>
                         <tr>
                             <th>Match No</th>
                             <th>Team 1</th>
                             <th>Score</th>
                             <th>Team 2</th>
                             <th>Venue</th>
                         </tr>
                     </thead>';
            echo '<tbody>';

            // Counter for match number
            $match_counter = 1;

            foreach ($matches_by_date as $date => $matches) {
                foreach ($matches as $match) {
                    $teams_scores = [];
                    $team_names = [];

                    if (!empty($match['results'])) {
                        foreach ($match['results'] as $team_id => $result) {
                            $teams_scores[$team_id] = !empty($result['points']) ? $result['points'] : '0';
                        }
                    }

                    if (!empty($match['players'])) {
                        foreach ($match['players'] as $team_id => $players) {
                            $team_names[$team_id] = get_the_title($team_id);
                        }
                    }

                    $team_ids = array_keys($teams_scores);

                    echo '<tr class="match hover_remove" data-tournament="' . esc_attr($match['tournament_assoc']) . '" data-venue="' . esc_attr(implode(', ', $match['venue'])) . '">';

                    echo '<td colspan="5" class="hover_out">';
                    echo '<table width="100%" cellspacing="0" cellpading="0">';
                    echo '<tr>';
                    echo '<td>1</td>';
                    // echo '<td>' . (!empty($team_names[$team_ids[0]]) ? esc_html($team_names[$team_ids[0]]) : '$match['player1']') . '</td>';
                    echo '<td>' . $match['player1'] . '</td>';
                    echo '<td>' . esc_html($teams_scores[$team_ids[0]] ?? '0') . ' - ' . esc_html($teams_scores[$team_ids[1]] ?? '0') . '</td>';
                    // echo '<td>' . (!empty($team_names[$team_ids[1]]) ? esc_html($team_names[$team_ids[1]]) : 'Team 2') . '</td>';
                    echo '<td>' . $match['player2'] . '</td>';
                    echo '<td>' . (!empty($match['venue']) ? esc_html(implode(', ', $match['venue'])) : 'Not specified') . '</td>';
                    echo '</tr>';
                    echo '<tr><td colspan="5" class="tr_2_styling"> ' . $match['event_type'][0] . ' | <a href="' . site_url() . '/gallery">Gallery</td></tr>';
                    echo '</table>';
                    echo '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No events found.</p>';
        }
        wp_reset_postdata();
        ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tournamentFilter = document.getElementById('filter-tournament');

                if (!tournamentFilter) {
                    console.error('One or more filter elements are missing.');
                    return;
                }

                const matches = document.querySelectorAll('.matches-container .match');

                function filterMatches() {
                    // Filter matches based on selected filters
                    matches.forEach(match => {
                        const tournament = match.getAttribute('data-tournament');

                        const filterTournament = tournamentFilter.value;

                        const show =
                            (!filterTournament || tournament === filterTournament);

                        match.style.display = show ? 'table-row' : 'none';
                    });
                }

                [tournamentFilter].forEach(filter => {
                    filter.addEventListener('change', filterMatches);
                });

                // Initial filtering
                filterMatches();
            });
        </script>

    </div>

<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('post_tournament_result', 'func_post_tournament_result');

function func_post_tournament_next_event()
{
    ob_start();

    // Current date in 'Ymd' format
    $current_date = date('Ymd');
?>

    <!-- Filter Form -->
    <form id="event-filter-form" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="event-filters" name="event_filter">
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past</option>
                </select>
            </div>
        </div>

    </form>

    <div id="events-lists" class="events-list pre_tour_upcoming_event"></div>

    <script>
        jQuery(document).ready(function($) {
            function loadEvents(filterType) {
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    method: "POST",
                    data: {
                        action: "load_events",
                        event_filter: filterType
                    },
                    success: function(response) {
                        $('#events-lists').html(response);
                    },
                    error: function() {
                        $('#events-lists').html('<p class="no-events">Failed to load events.</p>');
                    }
                });
            }

            // Initial load
            loadEvents($('#event-filters').val());

            // Handle filter change
            $('#event-filters').on('change', function() {
                const filterType = $(this).val();
                loadEvents(filterType);
            });
        });
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('post_tournament_next_event', 'func_post_tournament_next_event');

// AJAX handler for loading events
function load_events()
{
    $filter_type = $_POST['event_filter'] ?? 'upcoming';
    $current_date = date('Ymd');

    $args = array(
        'post_type'      => 'sp_tournament',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );

    if ($filter_type === 'upcoming') {
        $args['meta_value'] = $current_date;
        $args['meta_compare'] = '>=';
    } elseif ($filter_type === 'past') {
        $args['meta_value'] = $current_date;
        $args['meta_compare'] = '<';
        $args['order'] = 'DESC'; // Show past events in descending order
    }

    $events_query = new WP_Query($args);

    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $event_date = get_post_meta(get_the_ID(), 'event_start_date', true);
            $formatted_event_date = $event_date ? DateTime::createFromFormat('Ymd', $event_date)->format('F j, Y') : '';


            $bg_image = '';
            if (has_post_thumbnail()) {
                $bg_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $bg_image = $bg_image[0];
            }

            // Sponsor logo
            $sponsor_logo_id = get_post_meta(get_the_ID(), 'event_cover_image', true);
            $sponsor_logo = $sponsor_logo_id ? wp_get_attachment_url($sponsor_logo_id) : '';

            // Event date
            $event_date = get_post_meta(get_the_ID(), 'event_start_date', true);
            $formatted_event_date = $event_date ? DateTime::createFromFormat('Ymd', $event_date)->format('F j, Y') : '';

            $venue = get_the_terms(get_the_ID(), 'sp_venue');
            $venue_name = ($venue && !is_wp_error($venue)) ? $venue[0]->name : '';

            // Event badge
            $event_badge = get_field('event_badge');
            // Logo image
            $logo_image = get_field('logo_image');
            $format = get_post_meta(get_the_ID(), 'tournament_format', true);

            echo '<div class="upcoming_post_event_inner">';
            echo '<div class="upcoming_post_event_left">';
            echo '<div class="upcoming_post_event_left_l">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '</div>';
            echo '<div class="upcoming_post_event_left_r">';
            echo '<img src="' . esc_url($logo_image['url']) . '" class="event-logo" alt="' . esc_attr($logo_image['alt'] ?? 'Event Logo') . '">';
            echo '</div>';
            echo '</div>';

            echo '<div class="upcoming_post_event_right_r">';
            echo '<h3>' . esc_html($format) . '</h3>';
            echo '<h4>' . esc_html($formatted_event_date) . '</h4>';
            echo '</div>';

            echo '</div>';
        }
    } else {
        echo '<p class="no-events">No events found.</p>';
    }

    wp_reset_postdata();
    wp_die(); // Required for AJAX
}
add_action('wp_ajax_load_events', 'load_events');
add_action('wp_ajax_nopriv_load_events', 'load_events');



function func_port_tournament_about_tourna()
{
    ob_start();
?>
    <div class="league_overview_sec pre_tour_about_sec">

        <div class="league_overview_inner league_venue_inner">
            <div class="league_overview_inner_l">
                <section class="pre_about_iframe">
                    <iframe width="100%" height="384" src="<?php echo the_field('tournament_about_video_code'); ?>" frameborder="0" allowfullscreen></iframe>
                </section>

                <?php echo the_field('tournament_about_video'); ?>
            </div>
            <div class="league_overview_inner_r league_venue_inner_r">
                <?php echo the_field('tournament_about_content'); ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('port_tournament_about_tourna', 'func_port_tournament_about_tourna');
 



function func_display_matches_posts() {
    ob_start();
    
    // Enqueue Slick Slider CSS and JS only when this function is called
    if (!wp_script_is('slick', 'enqueued')) {
        wp_enqueue_style('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
        wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
        wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
    }
    
    // Get all states from tournament posts
    global $wpdb;
    $states = $wpdb->get_col(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
        WHERE meta_key = 'tournament_state' AND meta_value != ''"
    );
    sort($states);

    // Start output
    echo '<div class="events-list-wrapper">';
    echo '<div class="event-filter-wrapper">';
    echo '<select id="up-event-filter" class="event-filter">';
    echo '<option value="all">All Events</option>';
    echo '<option value="upcoming">Upcoming</option>';
    echo '<option value="past">Past</option>';
    echo '</select>';

    echo '<select id="state-filter" class="state-filter">';
    echo '<option value="all">All States</option>';
    foreach ($states as $state) {
        echo '<option value="' . esc_attr($state) . '">' . esc_html($state) . '</option>';
    }
    echo '</select>';
    echo '</div>'; // Close event-filter-wrapper

    // Get all events initially
    $current_date = date('Ymd');
    $args = array(
        'post_type'      => 'sp_tournament',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );

    $events_query = new WP_Query($args);
    
    echo '<div class="events-container">';
    echo '<ul class="events-list events-slider">';
    
    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            
            $event_date = get_post_meta(get_the_ID(), 'event_start_date', true);
            $formatted_event_date = $event_date ? date('F j, Y', strtotime($event_date)) : '';
            $state_val = get_post_meta(get_the_ID(), 'tournament_state', true);
            $is_past = ($event_date && $event_date < $current_date);
            $is_upcoming = ($event_date && $event_date >= $current_date);
            
            $bg_image = '';
            if (has_post_thumbnail()) {
                $bg_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $bg_image = $bg_image[0];
            }
            
            echo '<li class="event-item" data-state="' . esc_attr($state_val) . '" data-date="' . esc_attr($event_date) . '" data-event-type="' . ($is_past ? 'past' : 'upcoming') . '">';
            echo '<a href="' .site_url(). '/tournaments-live-event-states/" class="event-link">';
            
            echo '<div class="single-event"' . ($bg_image ? ' style="background-image: url(' . esc_url($bg_image) . ');"' : '') . '>';
            
            // Event badge
            $event_badge = get_field('event_badge');
            echo '<span class="event-badge">' . esc_html($event_badge ? $event_badge : 'PWR') . '</span>';
            
            // Logo image
            $logo_image = get_field('logo_image');
            if ($logo_image && is_array($logo_image)) {
                echo '<img src="' . esc_url($logo_image['url']) . '" class="event-logo" alt="' . esc_attr($logo_image['alt'] ?? 'Event Logo') . '">';
            } else {
                echo '<img src="' . esc_url(site_url('/wp-content/uploads/2025/03/npl-leagues.png')) . '" class="event-logo" alt="NPL Leagues Logo">';
            }
            
            echo '<h2 class="event-title">' . get_the_title() . '</h2>';
            echo '<span class="event-state">' . esc_html($state_val) . '</span>';
            echo '</div>'; // Close single-event
            
            echo '<div class="event-date-wrapper">';
            if ($formatted_event_date) {
                echo '<span class="event-date">' . esc_html($formatted_event_date) . '</span>';
            }
            
            $format = get_post_meta(get_the_ID(), 'tournament_format', true);
            if ($format) {
                echo '<span class="event-format">' . esc_html($format) . '</span>';
            }
            echo '</div>'; // Close event-date-wrapper
            
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="no-events">No events found.</p>';
    }
    
    echo '</div>'; // Close events-container
    echo '</div>'; // Close events-list-wrapper
    
    wp_reset_postdata();
    
    // JavaScript for filtering
    ob_start();
    ?>
    <script>
    jQuery(document).ready(function($) {
        const eventFilter = $('#up-event-filter');
        const stateFilter = $('#state-filter');
        const slider = $('.events-slider');
        let allEvents = $('.event-item').clone();
        let currentDate = '<?php echo date('Ymd'); ?>';

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
            const selectedState = stateFilter.val();

            if (slider.hasClass('slick-initialized')) {
                slider.slick('unslick');
            }

            slider.empty();
            $('.no-events').remove();

            let matchFound = false;

            allEvents.each(function() {
                const event = $(this);
                const eventDate = event.attr('data-date');
                const eventState = event.attr('data-state');
                
                // Check event type filter
                let matchesEventType = true;
                if (selectedEventType === 'upcoming') {
                    matchesEventType = eventDate >= currentDate;
                } else if (selectedEventType === 'past') {
                    matchesEventType = eventDate < currentDate;
                }
                
                // Check state filter
                const matchesState = (selectedState === 'all' || eventState === selectedState);

                if (matchesEventType && matchesState) {
                    slider.append(event.clone());
                    matchFound = true;
                }
            });

            if (!matchFound) {
                slider.after('<p class="no-events">No events found matching your criteria.</p>');
            } else {
                initSlider();
            }
        }

        // Initialize with upcoming events by default
        eventFilter.val('all').trigger('change');
        
        // Set up event listeners
        eventFilter.on('change', filterEvents);
        stateFilter.on('change', filterEvents);
    });
    </script>
    <?php
    $js = ob_get_clean();
    
    return ob_get_clean() . $js;
}
add_shortcode('home_show_matches', 'func_display_matches_posts');

/*
 * Haris function code end
 * */
