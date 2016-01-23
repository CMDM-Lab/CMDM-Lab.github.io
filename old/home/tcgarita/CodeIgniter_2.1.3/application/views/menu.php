<?
    $current_class = 'class="current"';
    $advisor = $pro_act = $courses = $research = $member = $service = $publication = $home = "";
    switch($type){
    case 'courses':
	$courses = $current_class;
	break;
    case 'research':
	$research = $current_class;
	break;
    case 'advisor':
	$advisor = $current_class;
	break;
    case 'members':
	$member = $current_class;
	break;
    case 'publication':
	$publication = $current_class;
	break;
    case 'service':
	$service = $current_class;
	break;
    case 'pro_act':
	$pro_act = $current_class;
	break;
    default:
	$research = $current_class;
	break;
    }
?>
<ul>
<!--<li <?=$home?> ><a href="/">Home</a></li>-->
<li <?=$research?> ><a href="/index.php/research">Research</a></li>
<li <?=$advisor?> ><a href="/index.php/advisor">Advisor</a></li>
<li <?=$publication?>><a href="/index.php/publication">Publications & Patents</a></li>
<li <?=$pro_act?>><a href="/index.php/pro_act">Professional activities</a></li>
<li <?=$member?>><a href="/index.php/members">Members</a></li>
<li <?=$courses?> ><a href="/index.php/courses">Courses</a></li>
<li <?=$service?>><a href="/index.php/service">Services</a></li>
</ul>
