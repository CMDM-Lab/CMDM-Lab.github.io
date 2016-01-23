<div>
    <h3>Professional activities</h3>
    <h5>A. Technical Program Committee</h5>
    <ul class=publication>
<?
    foreach($activities as $a):
	print "<li>$a[1],$a[0]($a[2])</li>";
    endforeach;
?>
</ul>
<h5>B. Talks and Presentations</h5>
<ul class=publication>
<?
    foreach($talks as $a):
	print "<li>$a[0]($a[1])</li>";
    endforeach;
?>
</ul>
<h5>C. External Paper Reviewer</h5>
<ul class=publication>
<?
    foreach($reviewers as $a):
	print "<li>$a[0]($a[2])</li>";
    endforeach;
?>
</ul>
</div>
