<?php

/**
 * The template for displaying single player pages
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
	$team_number = get_post_meta($post_id, 'sp_number', true);
	$sp_nationality = get_post_meta($post_id, 'sp_nationality', true);

	// Fetching current, past teams, league, and season names
	$current_team_id = get_post_meta($post_id, 'sp_current_team', true);
	$current_team_name = $current_team_id ? get_the_title($current_team_id) : 'N/A';

	$past_team_ids = get_post_meta($post_id, 'sp_past_team', true);
	$past_team_name = $current_team_id ? get_the_title($past_team_ids) : 'N/A';
	$past_teams = [];
	if (!empty($past_team_ids)) {
		foreach ((array)$past_team_ids as $team_id) {
			$past_teams[] = get_the_title($team_id);
		}
	}
	$past_team_names = !empty($past_teams) ? implode(', ', $past_teams) : 'N/A';

	$league_ids = get_post_meta($post_id, 'sp_league', true);
	$leagues = [];
	if (!empty($league_ids)) {
		foreach ((array)$league_ids as $league_id) {
			$league_name = get_the_title($league_id);
			if ($league_name) {
				$leagues[] = $league_name;
			} else {
				$leagues[] = "Invalid League ID: $league_id";
			}
		}
	}
	$league_names = !empty($leagues) ? implode(', ', $leagues) : 'No leagues found';

	$season_ids = get_post_meta($post_id, 'sp_season', true);
	$seasons = [];
	if (!empty($season_ids)) {
		foreach ((array)$season_ids as $season_id) {
			$season_name = get_the_title($season_id);
			if ($season_name) {
				$seasons[] = $season_name;
			} else {
				$seasons[] = "Invalid Season ID: $season_id";
			}
		}
	}
	$season_names = !empty($seasons) ? implode(', ', $seasons) : 'No seasons found';

	$league_idss = get_post_meta($post_id, 'sp_league', true);
	$season_idss = get_post_meta($post_id, 'sp_season', true);

	// echo '<pre>';
	// echo 'Leagues: ';
	// print_r($league_idss);
	// echo 'Seasons: ';
	// print_r($season_idss);
	// echo '</pre>';

	$position_display = !empty($position_names) ? implode(', ', $position_names) : 'N/A';
?>

	<div class="main_team_single_page">
		<div class="wrapwidth">
			<div class="main_team_single">
				<article class="single-post-wrapper">

					<!-- Player Overview -->
					<div class="player_details_single1">
						<div class="player_details_single1_l">
							<?php if ($post_thumbnail) : ?>
								<img src="<?php echo esc_url($post_thumbnail); ?>" alt="<?php echo esc_attr($post_title); ?>">
							<?php endif; ?>
						</div>
						<div class="player_details_single1_r">
							<h1 class="page_title_player_sigle">
								<?php echo esc_html(wp_strip_all_tags($post_title)); ?>
							</h1>
							<table class="team-players-table">
								<tr>
									<td>Team Number</td>
									<td><?php echo  esc_html($team_number);?></td>
								</tr>
								<tr>
									<td>Nationality</td>
									<td><?php echo  esc_html($sp_nationality);?></td>
								</tr>
								<tr>
									<td>Current Team</td>
									<td><?php echo  esc_html($current_team_name);?></td>
								</tr>
								<tr>
									<td>Past Team</td>
									<td><?php echo  esc_html($past_team_names);?></td>
								</tr>
								<tr>
									<td>League</td>
									<td><?php echo  esc_html($league_names);?></td>
								</tr>
								<tr>
									<td>Season</td>
									<td><?php echo  esc_html($season_names);?></td>
								</tr>
								<tr>
									<td>Position</td>
									<td><?php echo  esc_html($position_display);?></td>
								</tr>
								
							</table>
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
							$player_pwr = get_post_meta($post_id, 'player_pwr', true);
							$player_won = get_post_meta($post_id, 'player_won', true);
							$player_lost = get_post_meta($post_id, 'player_lost', true);
							$player_f = get_post_meta($post_id, 'player_f', true);
							$player_a = get_post_meta($post_id, 'player_a', true);
							$player_points = get_post_meta($post_id, 'player_points', true);
							$player_strick = get_post_meta($post_id, 'player_strick', true);
							$player_dupr = get_post_meta($post_id, 'player_dupr', true);
							?>
							<tr>
								<td><?php echo $counter++; ?></td>
								<td class="player_name">
									<img src="<?php echo esc_url($post_thumbnail); ?>" alt="<?php echo esc_attr($post_title); ?>">
									<h4><?php echo esc_html(wp_strip_all_tags($post_title)); ?></h4>
								</td>
								<td><?php echo esc_html($player_pwr); ?></td>
								<td><?php echo esc_html($player_won); ?></td>
								<td><?php echo esc_html($player_lost); ?></td>
								<td><?php echo esc_html($player_f); ?></td>
								<td><?php echo esc_html($player_a); ?></td>
								<td><?php echo esc_html($player_points); ?></td>
								<td><?php echo esc_html($player_strick); ?></td>
								<td><?php echo esc_html($player_dupr); ?></td>
							</tr>
						</tbody>
					</table>
					</div>
				</article>
				<div class="team_leaderboard sidebarWidget">
					<?php echo do_shortcode('[leader_board id="3248" league="NPL" season="2025" sponsor="ahm"]'); ?>
				</div>
			</div>
		</div>
	</div>

<?php
endwhile;
get_footer();
