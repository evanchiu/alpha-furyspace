<?php
	//prioritiesPage.php
	//this page talks about the priorities for future game development
	session_start();
	require_once('global.php');
		
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } alpha priorities";
	$data['styles'] = array('space.css');
	View::header($data);
	View::messages();
?>
	<h1>Major Priorities</h1>
	<div id = "tab">
	<ul>
		<li>Add custom user options</li>
		<ul>
			<li>Add "remember me" cookie functionality</li>
			<li>Add SMS Notification</li>
			<li>Track by user id's rather than emails</li>
			<li>Add username/friendly-name (as opposed to hello email)</li>
			<li>Allow change/reset password</li>
		</ul>
		<li>Change 'facility' to 'lab' through the game to avoid confusion with factory</li>
		<li>Next Interface Upgrade</li>
		<ul>
			<li>Icons</li>
			<li>alt/title texts</li>
			<li>Clearly delineate tabs</li>
		</ul>
		<li>Add global event notification</li>
		<ul>
			<li>Game Specific: player conquered</li>
			<li>Turn force-rollover (due to maintenance</li>
			<li>Game Updated</li>
		</ul>
		<li>Use Request class for all input processing</li>
		<li>Combine fleets/map page (arrows for fleets + list)</li>
	</ul>
	</div>
	<h1>Ideas</h1>
	<div id = "tab">
	<ul>
		<li>New Buildings</li>
		<ul>
			<li>Build planetary shields/lasers</li>
			<li>Warp Gates</li>
			<li>"Terraformation" buildings that improve size or class</li>
		</ul>
		<li>Other Tasks</li>
		<ul>
			<li>Prevent planets with the same or naughty names</li>
			<li>Increase customization options on create galaxy page</li>
			<li>Separate three time periods - morning, afternoon, evening</li>
			<li>Add ping/chron options to remind players of turn</li>
			<li>friends/rematch</li>
			<li>simple message board</li>
		</ul>
		<li>Improve Planets Page</li>
		<ul>
			<li>Merge it onto the map page?</li>
			<li>Add minimap for context?</li>
		</ul>
		<li>Indicate planets that have recently been conquered/taken?</li>
		<li>Achievement-based title upgrades/changes?</li>
		<ul>
			<li>Scientist 		-- techLevel over X</li>
			<li>Bailout 		-- money of X</li>
			<li>Titan			-- more than X ships</li>
			<li>Emperor			-- owns more than half the planets</li>
			<li>Rebel Alliance	-- owns less than half the planets, and the other player is emperor</li>
			<li>Conqueror		-- defeats the last opposing commander (already implemented)</li>
			<li>Rank points - winner takes 10% from each loser</li>
		</ul>
		<li>Alliances</li>
		<ul>
			<li>Shared Vision - at different levels</li>
			<li>Joint Victory</li>
			<li>Gold Transfer</li>
			<li>Names</li>
			<li>Victory through diplomacy (Power to the people!)</li>
		</ul>
		<li>Group Votes?</li>
		<ul>
			<li>Kick Player</li>
			<li>Obliterate Galaxy</li>
		</ul>
		<li>Change Planet layout?</li>
		<ul>
			<li>Clusters</li>
			<li>Guarantee "good" primary expansion?</li>
			<li>Solar Systems with orbit?</li>
			<li>borders/frontiers</li>
			<li>Push home planets apart</li>
		</ul>
		<li>Feelers</li>
		<ul>
			<li>Probe - unmanned just looks around</li>
			<li>Cheap means of exploration</li>
			<li>unmanned</li>
			<li>Scanners - passive or active but use ground based sensors</li>
			<li>Scouts - manned, can take over</li>
			<li>Passive scanning to see all planets X distance away</li>
			<li>Can only move to planets that you can see (or have seen)</li>
		</ul>
		<li>Differentiation</li>
		<ul>
			<li>Reward/advancement on certain days</li>
			<li>Special Bonus based on how focused you were</li>
			<li>Differentiated such that you can't play all at once</li>
			<li>Whatever you focused the most one for the last 10 days</li>
			<li>Researcher</li>
			<ul>
				<li>Scan information</li>
			</ul>
			<li>Economist</li>
			<ul>
				<li>More money</li>
				<li>Hire ships</li>
			</ul>
			<li>Ship builder</li>
			<ul>
				<li>Build double ships</li>
			</ul>
			<li>One timer rewards, rather than buildup skills</li>
		</ul>
		<li>Homeplanet specialties</li>
		<ul>
			<li>Home planet migration - costs gold, time</li>
			<li>Have defensive bonus</li>
			<li>Have a gun so it kills the first ships that come</li>
		</ul>
		<li>Macros</li>
		<ul>
			<li>Build all ships</li>
			<li>Send all ships to ___</li>
			<li>Send all except home planet ships to ___</li>
		</ul>
		<li>Jumplanes</li>
		<ul>
			<li>Hyperspace lanes</li>
			<li>Space concept of roads</li>
			<li>Intersteller highways</li>
			<li>Long time to go not on the roads</li>
		</ul>
		<li>Distress Signal</li>
		<ul>
			<li>When a home planet is invaded, it sends out a distress call recalling all ships</li>
			<li>Player dies when all fleets are killed or revives if home planet is recaptured</li>
		</ul>
		<li>Party Mode</li>
		<ul>
			<li>auto refresh</li>
			<li>no notifications</li>
			<li>in-game communication</li>
			<li>Integrate with some VOIP service (Skype or one of the IM services)</li>
			<li>Indicate whether players are online</li>
		</ul>
	</ul>
	<div class = "puller"></div>
	</div><!--tab-->
<?php View::footer($renderstart); ?>
