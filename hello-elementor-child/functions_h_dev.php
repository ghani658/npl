<?php

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
        <?php
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
            }
        }

        if ($team_query->have_posts()) {
            while ($team_query->have_posts()) {
                $team_query->the_post();
                $team_meta = get_post_meta(get_the_ID());
                $team_state =  maybe_unserialize($team_meta['state'] ?? []);
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

                $event_state =  maybe_unserialize($event_meta['state'][0] ?? []);
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

        ?>

    </div>


    <?php
    return ob_get_clean();
}
add_shortcode('league_tabs_result', 'func_league_tabs_result');


function func_league_tabs_schedule($atts)
{
    ob_start();

    // Fetch required data for dropdowns
    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);

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
                                if (is_array($round_group)) {
                                    $unique_rounds = array_merge($unique_rounds, $round_group);
                                }
                            }
                        }
                        $unique_rounds = array_unique($unique_rounds);
                        foreach ($unique_rounds as $round) {
                            echo '<option value="' . esc_attr($round_id) . '">' . esc_html($round) . '</option>';
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
                                echo '<option value="' . esc_attr($tournament_id) . '">' . esc_html($match_type) . '</option>';
                            }
                        }
                        wp_reset_postdata();
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
                                echo '<option value="' . esc_attr($state_id) . '">' . esc_html($state) . '</option>';
                            }
                        }
                        wp_reset_postdata();
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
        <?php
        $args = [
            'post_type' => 'sp_event',
            'posts_per_page' => -1,
            'meta_query' => [],
            'tax_query' => ['relation' => 'AND'],
        ];

        $query = new WP_Query($args);

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
                    <div class="content">
                        <table class="event-table league_table_res" border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Team 1</th> 
                                    <th>Score</th>
                                    <th>Team 2</th>
                                    <th>Venue</th>
                                </tr>
                            </thead>
                            <tbody>';

            while ($query->have_posts()) {
                $query->the_post();
                $event_meta = get_post_meta(get_the_ID());
                $team_match = isset($event_meta['match-type'][0]) ? maybe_unserialize($event_meta['match-type'][0]) : '';
                $event_state = isset($event_meta['state'][0]) ? maybe_unserialize($event_meta['state'][0]) : '';

                // Get round data
                $rounds_display = '';
                $tournament_id = isset($event_meta['sp_tournament'][0]) ? $event_meta['sp_tournament'][0] : '';
                if ($tournament_id) {
                    $tournament_rounds = get_post_meta($tournament_id, 'sp_labels', true);
                    if (is_array($tournament_rounds)) {
                        $rounds_display = implode(', ', $tournament_rounds);
                    }
                }

                $team_ids = isset($event_meta['sp_team'][0]) ? maybe_unserialize($event_meta['sp_team'][0]) : [];
                $team1 = isset($team_ids[0]) ? get_the_title($team_ids[0]) : '';
                $team2 = isset($team_ids[1]) ? get_the_title($team_ids[1]) : '';

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

                $scores = isset($event_meta['sp_results'][0]) ? maybe_unserialize($event_meta['sp_results'][0]) : [];
                $score_display = ' - ';
                if (is_array($scores) && !empty($scores)) {
                    $score1 = isset($scores[$team_ids[0]]['points']) ? $scores[$team_ids[0]]['points'] : '';
                    $score2 = isset($scores[$team_ids[1]]['points']) ? $scores[$team_ids[1]]['points'] : '';
                    $score_display = $score1 . ' - ' . $score2;
                }

                $venue_terms = get_the_terms(get_the_ID(), 'sp_venue');
                $venue_name = $venue_terms[0]->name ?? 'Unknown';

                echo '<tr>
                        <td>' . get_the_date('l, d F, Y g:i A') . '</td>
                        <td><img src="' . esc_url($team1_image) . '" alt="' . esc_attr($team1) . '">' . esc_html($team1) . '</td>
                        <td>' . esc_html($score_display) . '</td>
                        <td><img src="' . esc_url($team2_image) . '" alt="' . esc_attr($team2) . '">' . esc_html($team2) . '</td>
                        <td>' . esc_html($venue_name) . '</td>
                    </tr>';
            }

            echo '</tbody>
                </table>
                </div>
                </div>
                </div>';
        } else {
            echo '<p>No events found</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
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
        }
    }

    if ($team_query->have_posts()) {
        while ($team_query->have_posts()) {
            $team_query->the_post();
            $team_meta = get_post_meta(get_the_ID());
            $team_state =  maybe_unserialize($team_meta['state'] ?? []);
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

            $event_state =  maybe_unserialize($event_meta['state'][0] ?? []);
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

    ?>

    <form id="eventFilterForm" class="player_info_venue">
        <div class="form-group_main">
            <div class="form-group">
                <select id="round-select" name="round">
                    <option value="">Select Round</option>
                    <?php
                    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
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
                        wp_reset_postdata();
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="match_type_select" name="match_type">
                    <option value="">Select Match Type</option>
                    <?php
                    $tournament_query = new WP_Query(array('post_type' => 'sp_tournament', 'posts_per_page' => -1));
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
                        wp_reset_postdata();
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="state-select" name="state">
                    <option value="">Select State</option>
                    <?php
                    $team_query = new WP_Query(array('post_type' => 'sp_team', 'posts_per_page' => -1));
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
                        wp_reset_postdata();
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <select id="venue-select" name="venue">
                    <option value="">Select Venue</option>
                    <?php
                    $venues = get_terms(['taxonomy' => 'sp_venue', 'orderby' => 'name', 'hide_empty' => false]);
                    foreach ($venues as $venue) {
                        echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <div id="team-players-table">
        <?php
        $current_datetime = new DateTime('now');
        $post_id = get_the_ID();

        $args = array(
            'post_type' => 'sp_tournament',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'sp_league',
                    'field'    => 'slug',
                    'terms'    => 'npl',
                )
            ),
        );

        if (!empty($filters['round'])) {
            $args['post__in'] = array($filters['round']);
        }

        $args_tourna = new WP_Query($args);

        if ($args_tourna->have_posts()) {
            while ($args_tourna->have_posts()) {
                $args_tourna->the_post();

                $event_metas = get_post_meta(get_the_ID());

                if (!isset($event_metas['sp_events'][0]) || empty($event_metas['sp_events'][0])) {
                    continue;
                }

                $matches = maybe_unserialize($event_metas['sp_events'][0]);
                $venue_terms = wp_get_post_terms($post_id, 'sp_venue', ['fields' => 'names']);

                $matches_by_date = [];
                foreach ($matches as $match) {
                    if (!isset($match['date'], $match['hh'], $match['mm'], $match['teams'][0], $match['teams'][1])) {
                        continue;
                    }

                    $match_datetime = DateTime::createFromFormat(
                        'Y-m-d H:i',
                        $match['date'] . ' ' . $match['hh'] . ':' . $match['mm']
                    );

                    // Skip if match is in the past
                    if ($match_datetime < $current_datetime) {
                        continue;
                    }

                    if (!empty($filters['state'])) {
                        $team_ids = $match['teams'] ?? array();
                        if (!in_array($filters['state'], $team_ids)) {
                            continue;
                        }
                    }

                    if (!empty($filters['venue'])) {
                        $venue_id = $event_metas['venue'][0] ?? '';
                        if ($venue_id != $filters['venue']) {
                            continue;
                        }
                    }

                    $date = $match['date'];
                    $time = $match['hh'] . ':' . $match['mm'];

                    if (!isset($matches_by_date[$date])) {
                        $matches_by_date[$date] = [];
                    }

                    if (!isset($matches_by_date[$date][$time])) {
                        $matches_by_date[$date][$time] = [];
                    }

                    $matches_by_date[$date][$time][] = $match;
                }

                ksort($matches_by_date);

                if (empty($matches_by_date)) {
                    continue;
                }
        ?>
                <div class="">
                    <div class="round_1">
                        <div class="round_1_accordian">
                            <div class="round_main_cont">
                                <div class="round_1_img">
                                    <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="" />
                                </div>
                                <div class="round_1_content">
                                    <h4><?php echo esc_html($event_metas['start_date'][0] ?? ''); ?></h4>
                                    <h2><?php the_title(); ?></h2>
                                    <h5><?php echo esc_html($event_metas['venue'][0] ?? ''); ?></h5>
                                </div>
                            </div>

                            <div class="chevron">
                                <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                        </div>

                        <div class="round_inner" style="display: none;">
                            <?php foreach ($matches_by_date as $date => $times): ?>
                                <div class="date-container">
                                    <h2 class="match-date"><?php echo date('F j, Y', strtotime($date)); ?></h2>

                                    <?php foreach ($times as $time => $time_matches): ?>
                                        <div class="time-slot-container">
                                            <h3 class="match-time"><?php echo esc_html($time); ?></h3>

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
                                                    <?php foreach ($time_matches as $index => $match):
                                                        $home_team_id = $match['teams'][0];
                                                        $road_team_id = $match['teams'][1];
                                                        $home_team_name = get_the_title($home_team_id);
                                                        $road_team_name = get_the_title($road_team_id);
                                                        $home_team_logo = get_the_post_thumbnail_url($home_team_id, 'thumbnail');
                                                        $road_team_logo = get_the_post_thumbnail_url($road_team_id, 'thumbnail');
                                                        $home_team_score = $match['results'][0] ?? 0;
                                                        $road_team_score = $match['results'][1] ?? 0;
                                                        $venue = $event_metas['venue'][0] ?? 'Unknown Venue';
                                                    ?>
                                                        <tr>
                                                            <td><?php echo 'Match ' . ($index + 1); ?></td>
                                                            <td>
                                                                <?php if ($home_team_logo): ?>
                                                                    <img src="<?php echo esc_url($home_team_logo); ?>" alt="<?php echo esc_attr($home_team_name); ?> Logo" />
                                                                <?php endif; ?>
                                                                <?php echo esc_html($home_team_name); ?>
                                                            </td>
                                                            <td><?php echo esc_html($home_team_score) . ' Vs ' . esc_html($road_team_score); ?></td>
                                                            <td>
                                                                <?php if ($road_team_logo): ?>
                                                                    <img src="<?php echo esc_url($road_team_logo); ?>" alt="<?php echo esc_attr($road_team_name); ?> Logo" />
                                                                <?php endif; ?>
                                                                <?php echo esc_html($road_team_name); ?>
                                                            </td>
                                                            <td><?php echo esc_html($venue); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="5">
                                                                <span class="span1">Upcoming</span> | <span>Preview</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<p>No upcoming matches found matching your criteria.</p>';
        }
        wp_reset_postdata();
        ?>
    </div>

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


// Post League
function func_post_league_schedule($atts) {
    ob_start();
    
    try {
        // Enable debugging (remove in production)
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Tournament structure with round names and team counts
        $tournament_structure = array(
            'Round 1' => array(
                'teams' => 32,
                'matches' => 16,
                'label' => 'Round 1'
            ),
            'Round 2' => array(
                'teams' => 16,
                'matches' => 8,
                'label' => 'Round 2'
            ),
            'Round 3' => array(
                'teams' => 8,
                'matches' => 4,
                'label' => 'Round 3'
            ),
            'Round 4' => array(
                'teams' => 4,
                'matches' => 2,
                'label' => 'Round 4'
            ),
            'Round 5' => array(
                'teams' => 2,
                'matches' => 1,
                'label' => 'Round 5'
            )
        );

        // Debug: Check if SportsPress is active
        

        // Debug: Check for required post types
        if (!post_type_exists('sp_tournament') || !post_type_exists('sp_team')) {
            throw new Exception("Required SportsPress post types are not registered.");
        }
        
        // Output the filter form
        ?>
        <form id="eventFilterForm" class="player_info_venue">
            <div class="form-group_main">
                <div class="form-group">
                    <select id="round-select" name="round">
                        <option value="">Select Round</option>
                        <?php foreach ($tournament_structure as $round => $data): ?>
                            <option value="<?php echo esc_attr($round); ?>"><?php echo esc_html($data['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <select id="match_type_select" name="match_type">
                        <option value="">Select Match Type</option>
                        <?php
                        $tournament_query = new WP_Query(array(
                            'post_type' => 'sp_tournament',
                            'posts_per_page' => -1,
                            'no_found_rows' => true,
                            'update_post_term_cache' => false,
                            'update_post_meta_cache' => false
                        ));
                        
                        if ($tournament_query->have_posts()) {
                            $unique_match_types = array();
                            while ($tournament_query->have_posts()) {
                                $tournament_query->the_post();
                                $match_type = get_post_meta(get_the_ID(), 'match_type', true);
                                if ($match_type && !in_array($match_type, $unique_match_types)) {
                                    $unique_match_types[] = $match_type;
                                    echo '<option value="' . esc_attr($match_type) . '">' . esc_html($match_type) . '</option>';
                                }
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <select id="state-select" name="state">
                        <option value="">Select State</option>
                        <?php
                        $states = get_terms(array(
                            'taxonomy' => 'sp_state',
                            'hide_empty' => false
                        ));
                        
                        foreach ($states as $state) {
                            echo '<option value="' . esc_attr($state->term_id) . '">' . esc_html($state->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <select id="venue-select" name="venue">
                        <option value="">Select Venue</option>
                        <?php
                        $venues = get_terms(array(
                            'taxonomy' => 'sp_venue',
                            'hide_empty' => false
                        ));
                        
                        foreach ($venues as $venue) {
                            echo '<option value="' . esc_attr($venue->term_id) . '">' . esc_html($venue->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </form>

        <div id="team-players-table">
            <?php
            $args = array(
                'post_type' => 'sp_tournament',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'sp_league',
                        'field' => 'slug',
                        'terms' => 'npl',
                    )
                ),
                'no_found_rows' => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false
            );

            $tournaments_query = new WP_Query($args);

            if ($tournaments_query->have_posts()) {
                while ($tournaments_query->have_posts()) {
                    $tournaments_query->the_post();
                    $tournament_id = get_the_ID();
                    
                    // Get tournament meta safely
                    $event_metas = get_post_meta($tournament_id);
                    $start_date = isset($event_metas['start_date'][0]) ? $event_metas['start_date'][0] : '';
                    $venue = isset($event_metas['venue'][0]) ? $event_metas['venue'][0] : 'Unknown Venue';
                    
                    // Get matches safely
                    $matches = isset($event_metas['sp_events'][0]) ? maybe_unserialize($event_metas['sp_events'][0]) : array();
                    
                    // Organize matches by round
                    $matches_by_round = array();
                    foreach ($matches as $match) {
                        if (!is_array($match)) continue;
                        
                        $round = isset($match['round']) ? $match['round'] : 'Round 1';
                        if (!isset($matches_by_round[$round])) {
                            $matches_by_round[$round] = array();
                        }
                        $matches_by_round[$round][] = $match;
                    }
                    ?>
                    <div class="tournament-container">
                        <div class="tournament-header">
                            <div class="tournament-logo">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/images/tournament-default.png'); ?>" alt="<?php the_title_attribute(); ?>" />
                            </div>
                            <div class="tournament-info">
                                <h4><?php echo esc_html($start_date); ?></h4>
                                <h2><?php the_title(); ?></h2>
                                <h5><?php echo esc_html($venue); ?></h5>
                            </div>
                        </div>

                        <div class="tournament-rounds">
                            <?php
                            foreach ($tournament_structure as $round => $round_data) {
                                $round_matches = isset($matches_by_round[$round]) ? $matches_by_round[$round] : array();
                                ?>
                                <div class="round-container">
                                    <h3 class="round-title"><?php echo esc_html($round_data['label']); ?></h3>
                                    
                                    <?php if (!empty($round_matches)): ?>
                                        <?php
                                        // Group matches by date
                                        $matches_by_date = array();
                                        foreach ($round_matches as $match) {
                                            if (!isset($match['date'])) continue;
                                            $date = $match['date'];
                                            if (!isset($matches_by_date[$date])) {
                                                $matches_by_date[$date] = array();
                                            }
                                            $matches_by_date[$date][] = $match;
                                        }
                                        ksort($matches_by_date);
                                        
                                        foreach ($matches_by_date as $date => $date_matches):
                                            ?>
                                            <div class="match-date-container">
                                                <h4 class="match-date"><?php echo esc_html(date_i18n('F j, Y', strtotime($date))); ?></h4>
                                                
                                                <table class="match-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Time</th>
                                                            <th>Home Team</th>
                                                            <th>Score</th>
                                                            <th>Away Team</th>
                                                            <th>Venue</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($date_matches as $match): ?>
                                                            <?php
                                                            $home_team_id = isset($match['teams'][0]) ? $match['teams'][0] : 0;
                                                            $away_team_id = isset($match['teams'][1]) ? $match['teams'][1] : 0;
                                                            $home_team = $home_team_id ? get_post($home_team_id) : null;
                                                            $away_team = $away_team_id ? get_post($away_team_id) : null;
                                                            
                                                            $home_score = isset($match['results'][0]) ? $match['results'][0] : '-';
                                                            $away_score = isset($match['results'][1]) ? $match['results'][1] : '-';
                                                            $time = (isset($match['hh']) ? str_pad($match['hh'], 2, '0', STR_PAD_LEFT) : '00') . ':' . (isset($match['mm']) ? str_pad($match['mm'], 2, '0', STR_PAD_LEFT) : '00');
                                                            $match_venue = isset($match['venue']) ? $match['venue'] : $venue;
                                                            ?>
                                                            <tr>
                                                                <td><?php echo esc_html($time); ?></td>
                                                                <td>
                                                                    <?php if ($home_team): ?>
                                                                        <?php if (has_post_thumbnail($home_team_id)): ?>
                                                                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($home_team_id, 'thumbnail')); ?>" alt="<?php echo esc_attr($home_team->post_title); ?>" />
                                                                        <?php endif; ?>
                                                                        <?php echo esc_html($home_team->post_title); ?>
                                                                    <?php else: ?>
                                                                        TBD
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo esc_html($home_score) . ' - ' . esc_html($away_score); ?></td>
                                                                <td>
                                                                    <?php if ($away_team): ?>
                                                                        <?php if (has_post_thumbnail($away_team_id)): ?>
                                                                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($away_team_id, 'thumbnail')); ?>" alt="<?php echo esc_attr($away_team->post_title); ?>" />
                                                                        <?php endif; ?>
                                                                        <?php echo esc_html($away_team->post_title); ?>
                                                                    <?php else: ?>
                                                                        TBD
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo esc_html($match_venue); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-matches">
                                            <p>No matches scheduled yet for this round.</p>
                                            <?php for ($i = 1; $i <= $round_data['matches']; $i++): ?>
                                                <div class="empty-match">
                                                    <p>Match <?php echo $i; ?>: To be determined</p>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
                wp_reset_postdata();
            } else {
                echo '<div class="no-tournaments"><p>No tournaments found.</p></div>';
            }
            ?>
        </div>

        

        <script>
            jQuery(document).ready(function($) {
                // Initialize - hide all match containers by default
                $('.match-date-container, .no-matches').hide();
                
                // Toggle round visibility
                $('.round-title').click(function() {
                    $(this).closest('.round-container').find('.match-date-container, .no-matches').slideToggle();
                });
            });
        </script>
        <?php
        
    } catch (Exception $e) {
        // Display error message to admins only
        if (current_user_can('administrator')) {
            echo '<div class="error-message"><p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        } else {
            echo '<div class="error-message"><p>There was an error loading the tournament schedule. Please try again later.</p></div>';
        }
        
        error_log('Tournament Schedule Error: ' . $e->getMessage());
    }
    
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

    $atts = shortcode_atts([
        'league' => '',
    ], $atts, 'league_list');

    $league_slug = sanitize_title($atts['league']);
    $current_datetime = new DateTime('now', new DateTimeZone('Australia/Sydney')); // Adjust timezone as needed

    $args_tourna = new WP_Query([
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1, // Get all tournaments for the league
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

            $event_metas = get_post_meta(get_the_ID());

            if (!isset($event_metas['sp_events'][0]) || empty($event_metas['sp_events'][0])) {
                continue;
            }

            $matches = maybe_unserialize($event_metas['sp_events'][0]);
            $venue = $event_metas['venue'][0] ?? 'Unknown Venue';
            $start_date = $event_metas['start_date'][0] ?? '';

            // Group matches by date and then by time
            $matches_by_date = [];
            foreach ($matches as $match) {
                // Skip matches without date/time info
                if (!isset($match['date'], $match['hh'], $match['mm'])) {
                    continue;
                }

                // Create match datetime object
                try {
                    $match_datetime = new DateTime($match['date'] . ' ' . $match['hh'] . ':' . $match['mm'], new DateTimeZone('Australia/Sydney'));
                } catch (Exception $e) {
                    continue;
                }

                // Skip matches that are in the future
                if ($match_datetime > $current_datetime) {
                    continue;
                }

                // Skip matches without results or with both scores as 0
                if (
                    !isset($match['results']) || empty($match['results']) ||
                    (isset($match['results'][0]) && isset($match['results'][1]) &&
                        $match['results'][0] == 0 && $match['results'][1] == 0)
                ) {
                    continue;
                }

                $date = $match['date'];
                $time = $match['hh'] . ':' . $match['mm'];

                if (!isset($matches_by_date[$date])) {
                    $matches_by_date[$date] = [];
                }

                if (!isset($matches_by_date[$date][$time])) {
                    $matches_by_date[$date][$time] = [];
                }

                $matches_by_date[$date][$time][] = $match;
            }

            ksort($matches_by_date);

            if (empty($matches_by_date)) {
                continue;
            }
        ?>
            <div id="team-players-table">
                <div class="round_1">
                    <div class="round_1_accordian">
                        <div class="round_main_cont">
                            <div class="round_1_img">
                                <img src="http://localhost/npl/wp-content/uploads/2025/04/Frame-1000004890-1.png" alt="Round Image" />
                            </div>
                            <div class="round_1_content">
                                <h4><?php echo esc_html($start_date); ?></h4>
                                <h2><?php the_title(); ?></h2>
                                <h5><?php echo esc_html($venue); ?></h5>
                            </div>
                        </div>
                        <div class="chevron">
                            <span class="chevron-icon"><i class="fa-solid fa-chevron-down"></i></span>
                        </div>
                    </div>
                    <div class="round_inner" style="display: none;">
                        <?php foreach ($matches_by_date as $date => $times): ?>
                            <div class="date-container">
                                <h2 class="match-date"><?php echo date('F j, Y', strtotime($date)); ?></h2>

                                <?php foreach ($times as $time => $time_matches): ?>
                                    <div class="round_inner_time">
                                        <h3><?php echo esc_html($time); ?></h3>
                                    </div>
                                    <div>
                                        <table class="team-players-table" width="100%" cellspacing="0" cellpadding="0">
                                            <thead>
                                                <tr>
                                                    <th>Match</th>
                                                    <th>Home Team</th>
                                                    <th>Score</th>
                                                    <th>Away Team</th>
                                                    <th>Venue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($time_matches as $index => $match):
                                                    if (!isset($match['teams'][0], $match['teams'][1])) {
                                                        continue;
                                                    }

                                                    $home_team_id = $match['teams'][0];
                                                    $road_team_id = $match['teams'][1];
                                                    $home_team_name = get_the_title($home_team_id);
                                                    $road_team_name = get_the_title($road_team_id);
                                                    $home_team_logo = get_the_post_thumbnail_url($home_team_id, 'thumbnail');
                                                    $road_team_logo = get_the_post_thumbnail_url($road_team_id, 'thumbnail');
                                                    $home_team_score = isset($match['results'][0]) ? $match['results'][0] : '-';
                                                    $road_team_score = isset($match['results'][1]) ? $match['results'][1] : '-';
                                                ?>
                                                    <tr>
                                                        <td><?php echo 'Match ' . ($index + 1); ?></td>
                                                        <td>
                                                            <?php if ($home_team_logo): ?>
                                                                <img src="<?php echo esc_url($home_team_logo); ?>" alt="<?php echo esc_attr($home_team_name); ?> Logo" />
                                                            <?php endif; ?>
                                                            <?php echo esc_html($home_team_name); ?>
                                                        </td>
                                                        <td><?php echo esc_html($home_team_score) . ' - ' . esc_html($road_team_score); ?></td>
                                                        <td>
                                                            <?php if ($road_team_logo): ?>
                                                                <img src="<?php echo esc_url($road_team_logo); ?>" alt="<?php echo esc_attr($road_team_name); ?> Logo" />
                                                            <?php endif; ?>
                                                            <?php echo esc_html($road_team_name); ?>
                                                        </td>
                                                        <td><?php echo esc_html($venue); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5">
                                                            <span class="span1">Completed</span> | <span>Gallery</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php
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
                        <h2><?php echo esc_html($event_metas['match_type'][0] ?? 'N/A'); ?></h2>
                        <h5><?php echo !empty($venue_terms) ? esc_html(implode(', ', $venue_terms)) : 'Not specified'; ?></h5>
                        <div class="match_type_icon">
                            <img src="<?php echo esc_url($team1_image); ?>" alt="Match Type Icon" />
                        </div>
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


function func_tournament_players($atts) {
    ob_start();

    // Get all tournaments with match_type meta
    $tournament_args = array(
        'post_type' => 'sp_tournament',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'match_type',
                'compare' => 'EXISTS'
            )
        )
    );

    $tournaments = get_posts($tournament_args);

    if (empty($tournaments)) {
        return '<p>No tournaments found.</p>';
    }

    // Organize tournaments by match_type
    $tournaments_by_type = array();
    foreach ($tournaments as $tournament) {
        $match_type = get_post_meta($tournament->ID, 'match_type', true);
        if (!isset($tournaments_by_type[$match_type])) {
            $tournaments_by_type[$match_type] = array();
        }
        $tournaments_by_type[$match_type][] = $tournament;
    }

    // Create container and tabs
    echo '<div class="tournament-players-container">';
    echo '<h3>Tournament Players</h3>';
    
    // Tab navigation
    echo '<div class="match-type-tabs">';
    echo '<ul class="tab-nav">';
    foreach ($tournaments_by_type as $match_type => $type_tournaments) {
        echo '<li><a href="#match-type-' . esc_attr($match_type) . '" data-type="' . esc_attr($match_type) . '">Type ' . esc_html($match_type) . '</a></li>';
    }
    echo '</ul>';

    // Tab content
    foreach ($tournaments_by_type as $match_type => $type_tournaments) {
        echo '<div id="match-type-' . esc_attr($match_type) . '" class="tab-content">';
        
        // Get all teams from these tournaments
        $team_ids = array();
        foreach ($type_tournaments as $tournament) {
            $tournament_teams = get_post_meta($tournament->ID, 'sp_team', false);
            if (!empty($tournament_teams)) {
                $team_ids = array_merge($team_ids, $tournament_teams);
            }
        }
        
        $team_ids = array_unique($team_ids);
        
        if (empty($team_ids)) {
            echo '<p>No teams found in these tournaments.</p>';
            continue;
        }
        
        // Get all players from these teams with player_points
        $player_args = array(
            'post_type' => 'sp_player',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'sp_team',
                    'value' => $team_ids,
                    'compare' => 'IN'
                ),
                array(
                    'key' => 'player_points',
                    'compare' => 'EXISTS'
                )
            ),
            'orderby' => 'meta_value_num',
            'meta_key' => 'player_points',
            'order' => 'DESC'
        );

        $players = new WP_Query($player_args);

        if ($players->have_posts()) {
            echo '<div class="players-grid">';
            
            while ($players->have_posts()) {
                $players->the_post();
                $player_id = get_the_ID();
                $player_name = get_the_title();
                $player_image = get_the_post_thumbnail_url($player_id, 'medium');
                $player_points = get_post_meta($player_id, 'player_points', true);
                $player_team_id = get_post_meta($player_id, 'sp_team', true);
                $player_team = $player_team_id ? get_the_title($player_team_id) : '—';

                echo '<div class="player-card" data-player-id="' . esc_attr($player_id) . '">';
                echo '<div class="player-image">';
                if ($player_image) {
                    echo '<img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '">';
                } else {
                    echo '<div class="no-image">No Image</div>';
                }
                echo '</div>';
                echo '<div class="player-info">';
                echo '<h4>' . esc_html($player_name) . '</h4>';
                echo '<p class="team">' . esc_html($player_team) . '</p>';
                echo '<p class="points">Points: ' . esc_html($player_points ? $player_points : '0') . '</p>';
                echo '</div>';
                echo '</div>'; // .player-card
            }
            
            echo '</div>'; // .players-grid
        } else {
            echo '<p>No players found in these tournament teams.</p>';
        }
        
        wp_reset_postdata();
        echo '</div>'; // .tab-content
    }
    
    echo '</div>'; // .match-type-tabs
    
    // Player details modal
    echo '<div id="player-modal" class="modal">';
    echo '<div class="modal-content">';
    echo '<span class="close-modal">&times;</span>';
    echo '<div id="player-details"></div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // .tournament-players-container

    // Add styling
    echo '
    <style>
        .tournament-players-container {
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        .match-type-tabs {
            margin-top: 20px;
        }
        .tab-nav {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            border-bottom: 1px solid #ddd;
        }
        .tab-nav li {
            margin-right: 10px;
        }
        .tab-nav a {
            display: block;
            padding: 10px 15px;
            background: #f5f5f5;
            color: #333;
            text-decoration: none;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            transition: all 0.3s;
        }
        .tab-nav a:hover,
        .tab-nav a.active {
            background: #fff;
            color: #0073aa;
        }
        .tab-content {
            display: none;
            padding: 15px 0;
        }
        .tab-content.active {
            display: block;
        }
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .player-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
            cursor: pointer;
        }
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .player-image {
            height: 180px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .player-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .no-image {
            color: #999;
        }
        .player-info {
            padding: 15px;
        }
        .player-info h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .player-info .team {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }
        .player-info .points {
            margin: 0;
            font-weight: bold;
            color: #0073aa;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
            position: relative;
        }
        .close-modal {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        #player-details {
            display: flex;
            gap: 30px;
        }
        #player-details .player-image {
            width: 250px;
            height: 250px;
            flex-shrink: 0;
        }
        #player-details .player-meta {
            flex-grow: 1;
        }
        #player-details h3 {
            margin-top: 0;
            color: #333;
        }
        #player-details .meta-row {
            margin-bottom: 10px;
        }
        #player-details .meta-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        
        @media (max-width: 768px) {
            .players-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            .player-image {
                height: 150px;
            }
            #player-details {
                flex-direction: column;
            }
            #player-details .player-image {
                width: 100%;
                margin-bottom: 20px;
            }
            .modal-content {
                width: 90%;
                margin: 10% auto;
                padding: 20px;
            }
        }
    </style>
    ';
    
    // Add JavaScript for tabs and modal
    echo '
    <script>
    jQuery(document).ready(function($) {
        // Tab functionality
        $(".match-type-tabs .tab-nav a").click(function(e) {
            e.preventDefault();
            var tabId = $(this).attr("href");
            
            // Hide all tab content
            $(".tab-content").removeClass("active");
            
            // Deactivate all tab links
            $(".tab-nav a").removeClass("active");
            
            // Show current tab content
            $(tabId).addClass("active");
            
            // Activate current tab link
            $(this).addClass("active");
        });
        
        // Activate first tab by default
        $(".tab-nav a:first").addClass("active");
        $(".tab-content:first").addClass("active");
        
        // Player modal functionality
        $(".player-card").click(function() {
            var playerId = $(this).data("player-id");
            
            // Show loading
            $("#player-details").html("<p>Loading player details...</p>");
            $("#player-modal").show();
            
            // AJAX request to get player details
            $.ajax({
                url: "' . admin_url('admin-ajax.php') . '",
                type: "POST",
                data: {
                    action: "get_player_details",
                    player_id: playerId
                },
                success: function(response) {
                    if (response.success) {
                        $("#player-details").html(response.data);
                    } else {
                        $("#player-details").html("<p>Error loading player details.</p>");
                    }
                },
                error: function() {
                    $("#player-details").html("<p>Error loading player details.</p>");
                }
            });
        });
        
        // Close modal
        $(".close-modal").click(function() {
            $("#player-modal").hide();
        });
        
        // Close modal when clicking outside
        $(window).click(function(e) {
            if ($(e.target).is("#player-modal")) {
                $("#player-modal").hide();
            }
        });
    });
    </script>
    ';

    return ob_get_clean();
}
add_shortcode('tournament_players', 'func_tournament_players');

// AJAX handler for player details
add_action('wp_ajax_get_player_details', 'get_player_details_callback');
add_action('wp_ajax_nopriv_get_player_details', 'get_player_details_callback');

function get_player_details_callback() {
    $player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
    
    if (!$player_id) {
        wp_send_json_error('Invalid player ID');
    }
    
    $player = get_post($player_id);
    if (!$player) {
        wp_send_json_error('Player not found');
    }
    
    $output = '';
    
    $player_name = $player->post_title;
    $player_image = get_the_post_thumbnail_url($player_id, 'large');
    $player_points = get_post_meta($player_id, 'player_points', true);
    $player_team_id = get_post_meta($player_id, 'sp_team', true);
    $player_team = $player_team_id ? get_the_title($player_team_id) : '—';
    $player_position = get_post_meta($player_id, 'sp_position', true);
    $player_bio = get_post_meta($player_id, 'player_bio', true);
    
    $output .= '<div class="player-image">';
    if ($player_image) {
        $output .= '<img src="' . esc_url($player_image) . '" alt="' . esc_attr($player_name) . '">';
    } else {
        $output .= '<div class="no-image">No Image Available</div>';
    }
    $output .= '</div>';
    
    $output .= '<div class="player-meta">';
    $output .= '<h3>' . esc_html($player_name) . '</h3>';
    
    $output .= '<div class="meta-row"><span class="meta-label">Team:</span> ' . esc_html($player_team) . '</div>';
    $output .= '<div class="meta-row"><span class="meta-label">Position:</span> ' . esc_html($player_position ? $player_position : '—') . '</div>';
    $output .= '<div class="meta-row"><span class="meta-label">Points:</span> ' . esc_html($player_points ? $player_points : '0') . '</div>';
    
    if ($player_bio) {
        $output .= '<div class="meta-row bio"><span class="meta-label">Bio:</span> ' . wp_kses_post($player_bio) . '</div>';
    }
    
    $output .= '</div>';
    
    wp_send_json_success($output);
}


/*
 * Haris function code end
 * */



















