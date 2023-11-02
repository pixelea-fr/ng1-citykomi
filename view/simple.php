
<?php $events =$response['data']['informations']; ?>
<div class="citykomi-card__items">
 <?php foreach ($events as $event):
     extract($event["content"]);
 ?>
 <?php include 'card.php'; ?>
 <?php endforeach; ?>
 </div>