<?php

/**
 * The template for displaying single team pages
 * 
 * @package HelloElementor
 */
get_header();

if (!defined('ABSPATH')) {
	exit;
}

while (have_posts()) : the_post();
	$post_id = get_the_ID();
	$post_title = get_the_title();
	$post_thumbnail = get_the_post_thumbnail_url($post_id, 'large');
	$team_state = get_post_meta($post_id, 'team_state', true);
	$coach_name = get_post_meta($post_id, 'coach', true);
	$leagues = get_the_terms($post_id, 'sp_league');
?>
	<div class="main_team_single_page">
		<div class="wrapwidth">
			<div class="main_team_single">
				<article class="single-post-wrapper">

					<!-- Team Overview -->
					<div class="team_overview">
						<div class="team_overview_l">
							<h4>Our Story</h4>
							<img src="<?php echo esc_url($post_thumbnail); ?>" alt="<?php echo esc_attr($post_title); ?>" />
							<h1><?php echo esc_html($post_title); ?></h1>
						</div>
						<div class="team_overview_r">
							<h3>About</h3>
							<p><?php the_content(); ?></p>
						</div>
					</div>

					<!-- Players Grid -->
					<div class="team_players_section">
						<div class="player_head">
							<h2>Players</h2>
						</div>
						<div class="team_players_grid">
							<?php
							$positions = ['captain', 'vice-captain', 'member'];
							foreach ($positions as $position_slug) :
								$position_term = get_term_by('slug', $position_slug, 'sp_position');
								if (!$position_term) continue;

								$players = new WP_Query([
									'post_type' => 'sp_player',
									'posts_per_page' => -1,
									'tax_query' => [[
										'taxonomy' => 'sp_position',
										'field' => 'slug',
										'terms' => $position_slug,
									]],
									'meta_query' => [[
										'key' => 'sp_team',
										'value' => $post_id,
										'compare' => '=',
									]],
								]);

								if ($players->have_posts()) :
									while ($players->have_posts()) : $players->the_post();
										$player_id = get_the_ID();
										$player_name = get_the_title();
										$player_image = get_the_post_thumbnail_url($player_id, 'full');
										$players_state = get_post_meta($player_id, 'states', true);
										$team_permalink = get_permalink($player_id);
							?>
										<div class="player_list">
											<div class="player_lists_main">
												<div class="player_image">
													<img class="player_image_img" src="<?php echo esc_url($player_image); ?>" alt="<?php echo esc_attr($player_name); ?>">
													<div class="team_player_head">
														<div class="team_player_head_l">
															<img src="<?php echo esc_url($post_thumbnail); ?>" alt="<?php echo esc_attr($post_title); ?>" />
														</div>
														<div class="team_player_head_r">
															<a href="<?php echo esc_url($team_permalink); ?>"><i class="fa-solid fa-chevron-right"></i></a>
														</div>
													</div>
												</div>
												<div class="player_last">
													<div class="player_last_l">
														<h3><?php echo esc_html(ucwords(str_replace('-', ' ', $position_slug))); ?>
															<span><?php echo esc_html($player_name); ?></span>
														</h3>
													</div>
													<div class="player_last_r">
														<h4><?php echo esc_html($players_state); ?></h4>
													</div>
												</div>
											</div>
										</div>
							<?php
									endwhile;
									wp_reset_postdata();
								endif;
							endforeach;
							?>
						</div>
					</div>

					<!-- Player Stats Table -->
					<div class="player_head">
						<h2>Player Ranking & Stats</h2>
					</div>
					<div class="team_table-area">
					<table class="team-players-table">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>PWR</th>
								<th>Won</th>
								<th>Lost</th>
								<th>F</th>
								<th>A</th>
								<th>PTS</th>
								<th>STRK</th>
								<th>DUPR</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$counter = 1;
							foreach ($positions as $position_slug) :
								$players = new WP_Query([
									'post_type' => 'sp_player',
									'posts_per_page' => -1,
									'tax_query' => [[
										'taxonomy' => 'sp_position',
										'field' => 'slug',
										'terms' => $position_slug,
									]],
									'meta_query' => [[
										'key' => 'sp_team',
										'value' => $post_id,
										'compare' => '=',
									]],
								]);

								if ($players->have_posts()) :
									while ($players->have_posts()) : $players->the_post();
										$player_id = get_the_ID();
										$player_name = get_the_title();
										$player_image = get_the_post_thumbnail_url($player_id, 'thumbnail');
										$meta = fn($key) => get_post_meta($player_id, $key, true);
							?>
										<tr>
											<td><?php echo $counter++; ?></td>
											<td class="player_name">
												<img src="<?php echo esc_url($player_image); ?>" alt="<?php echo esc_attr($player_name); ?>">
												<h4><?php echo esc_html($player_name); ?></h4>
											</td>
											<td><?php echo esc_html($meta('player_pwr')); ?></td>
											<td><?php echo esc_html($meta('player_won')); ?></td>
											<td><?php echo esc_html($meta('player_lost')); ?></td>
											<td><?php echo esc_html($meta('player_f')); ?></td>
											<td><?php echo esc_html($meta('player_a')); ?></td>
											<td><?php echo esc_html($meta('player_points')); ?></td>
											<td><?php echo esc_html($meta('player_strick')); ?></td>
											<td><?php echo esc_html($meta('player_dupr')); ?></td>
										</tr>
							<?php
									endwhile;
									wp_reset_postdata();
								endif;
							endforeach;
							?>
						</tbody>
					</table>
					</div>
					<!-- Matches Title -->
					<div class="player_head">
						<h2><?php echo esc_html($post_title); ?> Matches</h2>
					</div>

					<!-- Filters -->
					<!-- <form class="player_info_venue" id="eventFilterForm">
						<div class="form-group_main">
							<div class="form-group">
								<select name="season" onchange="filterEvents()">
									<option value="">Select Season</option>
									<?php
									$seasons = get_terms(['taxonomy' => 'sp_season', 'orderby' => 'name', 'hide_empty' => false]);
									foreach ($seasons as $season) :
										echo '<option value="' . esc_attr($season->term_id) . '">' . esc_html($season->name) . '</option>';
									endforeach;
									?>
								</select>
							</div>
							<div class="form-group">
								<select name="state" onchange="filterEvents()">
									<option value="">Select State</option>
									<?php
									$state = get_post_meta(get_the_ID(), 'state', true);
									if ($state) {
										echo '<option value="' . esc_attr($state) . '">' . esc_html($state) . '</option>';
									}
									?>
								</select>
							</div>
							<div class="form-group">
								<select name="round" onchange="filterEvents()">
									<option value="">Select Round</option>
									<?php
									$rounds = get_posts(['post_type' => 'sp_tournament', 'posts_per_page' => -1, 'orderby' => 'title']);
									foreach ($rounds as $round) {
										echo '<option value="' . esc_attr($round->ID) . '">' . esc_html($round->post_title) . '</option>';
									}
									?>
								</select>
							</div>
							<div class="form-group">
								<select name="venue" onchange="filterEvents()">
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
						<input type="hidden" name="team_id" value="<?php echo esc_attr(get_the_ID()); ?>">
					</form> -->

					<div id="event-matches">
						<?php
						$current_team_name = get_the_title();
						$current_team_id = get_the_ID(); // Current team ID for the page

						$event_args = array(
							'post_type' => 'sp_event',
							'posts_per_page' => -1,
							'tax_query' => array(
								array(
									'taxonomy' => 'sp_venue',
									'operator' => 'EXISTS',
								),
							),
						);

						$query = new WP_Query($event_args);

						if ($query->have_posts()) {
							echo '<div class="event-matches">';
							echo '<table class="team-event-table team-players-table" border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">';
							echo '<thead>
				<tr>
					<th>Date</th>
					<th>Team</th>
					<th>Score</th>
					<th>Opponent</th>
					<th>Venue</th>
				</tr>
			</thead>';
							echo '<tbody>';

							while ($query->have_posts()) {
								$query->the_post();
								$event_id = get_the_ID();
								$event_meta = get_post_meta($event_id);

								// Get team IDs
								$team_ids = isset($event_meta['sp_team']) ? $event_meta['sp_team'] : [];

								// Check if the current team is involved
								if (in_array($current_team_id, $team_ids)) {
									// Get team names
									$team_1_name = isset($team_ids[0]) ? get_the_title($team_ids[0]) : 'TBD';
									$team_2_name = isset($team_ids[1]) ? get_the_title($team_ids[1]) : 'TBD';



									// Determine opponent team
									$current_team_is_1 = ($team_ids[0] === $current_team_id);
									$opponent_team_name = $current_team_is_1 ? $team_2_name : $team_1_name;



									// Venue
									$venues = get_the_terms($event_id, 'sp_venue');
									$venue_name = !empty($venues) && !is_wp_error($venues) ? $venues[0]->name : 'Unknown';

									// Date and Time (from _wp_old_date)
									$match_date_raw = isset($event_meta['_wp_old_date'][0]) ? $event_meta['_wp_old_date'][0] : '';
									if ($match_date_raw && strlen($match_date_raw) <= 10) {
										$match_date_raw .= ' 12:00:00'; // Add default time if date only
									}
									$match_date_timestamp = !empty($match_date_raw) ? strtotime(str_replace('/', '-', $match_date_raw)) : false;

									$match_date = $match_date_timestamp ? date('l, d F, Y', $match_date_timestamp) : esc_html($match_date_raw);
									$match_time = $match_date_timestamp ? date('g:i A', $match_date_timestamp) : 'TBD';

									// Scores from sp_results
									$results_raw = isset($event_meta['sp_results'][0]) ? maybe_unserialize($event_meta['sp_results'][0]) : [];
									$score_1 = isset($team_ids[1], $results_raw[$team_ids[1]]['points']) ? $results_raw[$team_ids[1]]['points'] : '-';
									$score_2 = isset($team_ids[0], $results_raw[$team_ids[0]]['points']) ? $results_raw[$team_ids[0]]['points'] : '-';
									$score_display = $score_2 . ' - ' . $score_1;



									// Skip row if both scores are missing
									if (
										($score_1 === '' || $score_1 === null || $score_1 === '-') &&
										($score_2 === '' || $score_2 === null || $score_2 === '-')
									) {
										continue;
									}

									// Determine the winner based on score
									$winner_icon = '';
									if (is_numeric($score_1) && is_numeric($score_2)) {
										if ($score_1 < $score_2) {
											$winner_team_id = $team_ids[0];
											$winner_icon = ' ✅'; // Add tick icon for team 1 as winner
										} elseif ($score_2 < $score_1) {
											$winner_team_id = $team_ids[1];
											$winner_icon = ' ✅'; // Add tick icon for team 2 as winner
										}
									}

									// Build team labels with winner badge
									$current_team_label = esc_html($current_team_name);
									$opponent_team_label = esc_html($opponent_team_name);


									// if ($current_team_id === $team_ids[1]) {
									// 	echo "yes";
									// } else {
									// 	echo "no" . $current_team_id;
									// }

									// echo "<pre>";
									// print_r($team_ids[1]);
									// echo "</pre>";

									if ($winner_icon && $winner_team_id === $current_team_id) {
										$current_team_label .= $winner_icon;
									} elseif ($winner_icon && $winner_team_id !== $current_team_id) {
										$opponent_team_label .= $winner_icon;
									}

									// Output row with winner badge
									echo '<tr>';
									echo '<td>' . esc_html($match_date) . ' ' . esc_html($match_time) . '</td>';
									echo '<td>' . $current_team_label . '</td>';
									echo '<td>' . esc_html($score_display) . '</td>';
									echo '<td>' . $opponent_team_label . '</td>';
									echo '<td>' . esc_html($venue_name) . '</td>';
									echo '</tr>';
								}
							}

							echo '</tbody>';
							echo '</table>';
							echo '</div>';
						} else {
							echo '<p>No events found for this team.</p>';
						}

						wp_reset_postdata();
						?>
					</div>

					<?php
					$current_team_name = get_the_title();
					$current_team_id = get_the_ID(); // Current team ID for the page

					$event_args = array(
						'post_type' => 'sp_event',
						'posts_per_page' => -1,
						'tax_query' => array(
							array(
								'taxonomy' => 'sp_venue',
								'operator' => 'EXISTS',
							),
						),
					);

					$query = new WP_Query($event_args);

					if ($query->have_posts()) {
						echo '<div class="event-matches">';
						// echo '<table class="team-event-table" border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">';
						// echo '<thead>
						// 		<tr>
						// 			<th>Date</th>
						// 			<th>Team</th>
						// 			<th>Score</th>
						// 			<th>Opponent</th>
						// 			<th>Venue</th>
						// 		</tr>
						// 	</thead>';
						// echo '<tbody>';

						while ($query->have_posts()) {
							$query->the_post();
							$event_id = get_the_ID();
							$event_meta = get_post_meta($event_id);

							// Get team IDs
							$team_ids = isset($event_meta['sp_team']) ? $event_meta['sp_team'] : [];

							// Check if the current team is involved
							if (in_array($current_team_id, $team_ids)) {
								// Get team names
								$team_1_name = isset($team_ids[0]) ? get_the_title($team_ids[0]) : 'TBD';
								$team_2_name = isset($team_ids[1]) ? get_the_title($team_ids[1]) : 'TBD';



								// Determine opponent team
								$current_team_is_1 = ($team_ids[0] === $current_team_id);
								$opponent_team_name = $current_team_is_1 ? $team_2_name : $team_1_name;



								// Venue
								$venues = get_the_terms($event_id, 'sp_venue');
								$venue_name = !empty($venues) && !is_wp_error($venues) ? $venues[0]->name : 'Unknown';

								// Date and Time (from _wp_old_date)
								$match_date_raw = isset($event_meta['_wp_old_date'][0]) ? $event_meta['_wp_old_date'][0] : '';
								if ($match_date_raw && strlen($match_date_raw) <= 10) {
									$match_date_raw .= ' 12:00:00'; // Add default time if date only
								}
								$match_date_timestamp = !empty($match_date_raw) ? strtotime(str_replace('/', '-', $match_date_raw)) : false;

								$match_date = $match_date_timestamp ? date('l, d F, Y', $match_date_timestamp) : esc_html($match_date_raw);
								$match_time = $match_date_timestamp ? date('g:i A', $match_date_timestamp) : 'TBD';

								// Scores from sp_results
								$results_raw = isset($event_meta['sp_results'][0]) ? maybe_unserialize($event_meta['sp_results'][0]) : [];
								$score_1 = isset($team_ids[1], $results_raw[$team_ids[1]]['points']) ? $results_raw[$team_ids[1]]['points'] : '-';
								$score_2 = isset($team_ids[0], $results_raw[$team_ids[0]]['points']) ? $results_raw[$team_ids[0]]['points'] : '-';
								$score_display = $score_2 . ' - ' . $score_1;



								if (
									($score_1 === '' || $score_1 === null || $score_1 === '-') &&
									($score_2 === '' || $score_2 === null || $score_2 === '-')
								) {
									continue;
								}

								$winner_icon = '';
								if (is_numeric($score_1) && is_numeric($score_2)) {
									if ($score_1 < $score_2) {
										$winner_team_id = $team_ids[0];
										$winner_icon = ' ✅'; // Add tick icon for team 1 as winner
									} elseif ($score_2 < $score_1) {
										$winner_team_id = $team_ids[1];
										$winner_icon = ' ✅'; // Add tick icon for team 2 as winner
									}
								}

								// Build team labels with winner badge
								$current_team_label = esc_html($current_team_name);
								$opponent_team_label = esc_html($opponent_team_name);

								if ($winner_icon && $winner_team_id === $current_team_id) {
									$current_team_label .= $winner_icon;
								} elseif ($winner_icon && $winner_team_id !== $current_team_id) {
									$opponent_team_label .= $winner_icon;
								}

								// Output row with winner badge
								// echo '<tr>';
								// echo '<td>' . esc_html($match_date) . ' ' . esc_html($match_time) . '</td>';
								// echo '<td>' . $current_team_label . '</td>';
								// echo '<td>' . esc_html($score_display) . '</td>';
								// echo '<td>' . $opponent_team_label . '</td>';
								// echo '<td>' . esc_html($venue_name) . '</td>';
								// echo '</tr>';
							}
						}

						// echo '</tbody>';
						// echo '</table>';
						echo '</div>';
					} else {
						echo '<p>No events found for this team.</p>';
					}

					wp_reset_postdata();
					?>




					<div class="blog_replay_main">
						<div class="player_head">
							<h2><?php echo esc_html($post_title); ?> Replays</h2>
						</div>
						<div class="post_data_team">
							<?php
							// Query posts from category with ID 9
							$args = array(
								'post_type' => 'post',
								'posts_per_page' => -1, // Show all posts
								'cat' => 9, // Replace with your desired category ID
							);

							$query = new WP_Query($args);

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

							wp_reset_postdata(); // Reset query data
							?>
						</div>
					</div>

				</article>
				<div class="team_leaderboard sidebarWidget">
					<?php echo do_shortcode('[leader_board id="3248" league="NPL" season="2025" sponsor="ahm"]'); ?>
				</div>
			</div>

		</div>
	</div>

	<script>
		// function filterEvents() {
		// 	const form = document.getElementById('eventFilterForm');
		// 	const formData = new FormData(form);
		// 	formData.append('action', 'filter_events');

		// 	fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
		// 			method: 'POST',
		// 			body: formData
		// 		})
		// 		.then(response => response.text())
		// 		.then(html => {
		// 			console.log(html); // Debug the response here
		// 			document.getElementById('event-matches').innerHTML = html;
		// 		})
		// 		.catch(error => console.error('Error:', error));
		// }
	</script>
<?php
endwhile;
get_footer();
