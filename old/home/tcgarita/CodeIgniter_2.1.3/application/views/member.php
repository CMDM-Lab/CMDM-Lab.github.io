<div>
	<h3>Members</h3>
	<div class="first">
			<h2><a name=faculty>Faculty</a></h2>
			<div><img src=http://cmdd.csie.ntu.edu.tw/images/image001.png>
			    <h2>曾宇鳳(Y. Jane Tseng)</h2>
			    <p>Tel: +886-2-33664888 Ext. 529</p>
			    <p>Email: yjtseng [at] csie.ntu.edu.tw</p>
			    <p>Office: R529, Department of Computer Science and Information Engineering, No. 1 Roosevelt Rd. Sec. 4, Taipei, Taiwan 106</p>
			    <h5>Education</h5>
			    <p><ul>
				<li>Ph.D, Medicinal Chemistry and Pharmacognosy, University of Illinois at Chicago</li>
				<li>BS, School of Pharmacy, College of Medicine, National Taiwan University</li>
			    </ul></p>
			</div>
		<div class="section">
			<div id="aside">
			<?php foreach($members as $cat => $people):?>
			   <h2><a name="<?php echo $cat?>"><?php echo $cat?></a>
				<a href="<?php echo current_url()?>#body"><img class=top_arrow src="<?php echo base_url()?>images/top_arrow.jpg"/></a></h2>
			<?php	foreach($people as $std):?>
			<?php
							if(array_key_exists('photo',$std)):
							print '<div><img src="'.base_url().'images/'.$std['photo'].'" style=height:110px class=member_photo>';
							else:
							print '<div><img src="'.base_url().'images/no_photo.png">';
							endif;
						?>
					<h2><?php echo $std['ch_name']?>(<?php if (isset($std['en_name'])) echo $std['en_name']?>)</h2>
					<p>Email: <?php if (isset($std['email'])) echo $std['email']?></p>
						<?php if(array_key_exists('topic',$std)):?>
							<p><ul class=research_topic>
							<?php foreach($std['topic'] as $p):?>
								<li><?php echo $p?></li>
							<?php endforeach;?>
							</ul></p>
						<?php endif; ?>

						<?php if(array_key_exists('award',$std)):?>
							<p><ul class=member_lists>
							<?php foreach($std['award'] as $a):?>
								<li><?php echo $a?></li>
							<?php endforeach;?>
							</ul></p>
						<?php endif; ?>
						<?php if(array_key_exists('pub',$std)):?>
							<ul style="float:left;font-style:italic;font-size:small;margin:5px 0px 0px -30px;list-style-type:none;">
							<li><span style="font-weight:bold">Journal papers</span></li>
							<?php foreach($std['pub'] as $a):?>
							<li><?php echo $a['author']?>,<?php echo $a['title']?>,<?php echo $a['info']?></li>
							<?php endforeach;?>
							</ul>
						<?php endif; ?>
						<?php if(array_key_exists('con',$std)):?>
							<ul style="float:left;font-style:italic;font-size:small;margin:5px 0px 0px -30px;list-style-type:none;">
							<li><span style="font-weight:bold">Conference papers</span></li>
							<?php foreach($std['con'] as $a):?>
							<li><?php echo $a['author']?>,<?php echo $a['title']?>,<?php echo $a['info']?></li>
							<?php endforeach;?>
							</ul>
						<?php endif; ?>
						</div>
			<?php endforeach;?>
			<?php endforeach;?>
		</div>
		<div id="sidebar">
			<h6>Members</h6>
			<ul class="links">
				<li><a href="<?php echo current_url()?>#faculty">Faculty</a></li>
			<?php $keys = array_keys($members);
			    foreach($keys as $k):?>
				<li><a href="<?php echo current_url()?>#<?php echo $k?>"><?php echo $k?></a></li>
			<?php  endforeach;?>
			</ul>
		</div>
	    </div>
	</div>
</div>
