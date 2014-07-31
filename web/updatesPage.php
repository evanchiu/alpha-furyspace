<?php
	//prioritiesPage.php
	//this page talks about the priorities for future game development
	session_start();
	require_once('global.php');
		
	$furyspace = (date("F j") == "April 1") ? "furryspace" : "furyspace";
	$data['title'] = "$furyspace } alpha updates";
	$data['styles'] = array('space.css');
	View::header($data);
	View::messages();
?>
	<h1>Alpha Updates</h1>
	<div id = "tab">
	<p>I'll post here when I change things.  Reverse chronological order so you can see the latest.</p>
	<h2>Twitter Updates (<a href = "http://twitter.com/furyspace">@furyspace</a>)</h2>
        <ul class = "readable">
<?php
$updates = Model::get_updates();
foreach($updates as $update){
    $date = date("n/j", $update['timestamp']);
    print "<li>$date - {$update['html']}</li>";
}
?>
        </ul>
        
<h2>pretwitter</h2>
	<ul class = "readable">
		<li>5/19 - You can <i>finally</i> recall fleets.</li>
		<li>5/19 - Added Owner information to planets on the map page.</li>
		<li>5/16 - Added Queue all ships on a particular planet to map page.</li>
		<li>5/16 - Turns will now auto-rollover when a committed player checks on a game that has an active day over one week.  So if you don't play for a week, you may miss your turn.</li>
		<li>5/10 - Wow, four lines of code, added alt text to map page.  Thanks to Jaymo for the idea.</li>
		<li>5/7 - Created a Twitter account, <a href = "http://twitter.com/furyspace">Furyspace on Twitter</a>!  I intend to be pushing updates from there, then using their API to make them show up here.  We'll see.</li>
		<li>5/6 - Prevented home planets from being within a certain distance.</li>
		<li>5/2 - Added battle logging</li>
		<li>5/2 - Added archive feature so the game is backed up every turn, this will be crucial to watching the movie of galaxy development at the end of the game.</li>
		<li>4/8	- Now you can click on planets in the map page to view them.</li>
		<li>4/8	- Enlarged Planet appearance when selected.</li>
		<li>4/7 - Added Tech Per Turn and Tech Units to Next Level on Commander Stats Bar</li>
		<li>4/5 - Totally restructured the reporting plan to show morning/evening, links, and smarter counts</li>
		<li>4/4 - Fixed an information leak where you could see how many ships were on any planet.</li>
		<li>4/1 - Gave you what you've all been wanting, a little space to get your furry on.</li>
		<li>3/30 - Fixed a bug where you were redirected to the map page after dispatching ships from the planets page.  Thanks to Matt White for the error report.  Also, MY MONITOR WORKS AGAIN!!! Yay!</li>
		<li>3/18 - Finally showing planets on the map page.  Click map page links to see them.  I know it looks terrible right now, you should see some good improvements Friday.</li>
		<li>3/10 - Cleaned up planet view</li>
		<li>3/10 - Added commit button trigger to every page.</li>
		<li>3/10 - Added Stats bar, you'll see this on your next turn.</li>
		<li>2/26 - Changed mining formula back.  Matt White's right, there's just not enough money to start on a third class planet.</li>
		<li>2/23 - Updated formula for mines.  If you're not mining first class, you'll get less than before.  The formula's on the rules page.</li>
		<li>2/23 - Buildings can now be recycled for 1/4 of their build cost. (this one's for you, Churchey)</li>
		<li>2/23 - All planetary functions work by slot number, rather than the first available slot (this one's for you, White Storm)</li>
		<li>2/23 - Created this updates page.  Link is in the lower right.  Added everything I could remember.</li>
		<li>2/23 - Changed background and css.  I used <a href = "http://www.smashingmagazine.com/">Smashing Magazine</a>'s <a href = "http://www.smashingmagazine.com/2009/02/22/space-explosion-photoshop-tutorial/">Space Explosion</a> tutorial.  If you like the change, why don't you help them out by posting on their <a href = "http://www.smashingmagazine.com/2009/02/23/hardware-giveaway-5000-comments-challenge/#comment-319483">5000 comment contest</a>?</li>
		<li>2/22 - Added "build all ships" macro</li>
		<li>2/21 - Limited visibility.  Check the rules page for the details, especially the "sight" section.</li>
	</ul>
	<div class = "puller"></div>
	</div><!--tab-->
<?php View::footer($renderstart); ?>
