<div class="citykomi-card citykomiFilterItemJs">
<?php  if (!empty($category)): ?>
    <div class="citykomi-card__title">
    <?php  if (!empty($img_category_array) && isset($img_category_array[$canal_id ])): ?>
        <?php echo $img_category_array[$canal_id ]; ?>
    <?php endif; ?>
         <?php if (!empty($category)){ echo $category; } ?>
        </div>
        <?php endif; ?>
    <div  class="citykomi-card__inner">
        <div class="citykomi-card__top"> 
        <?php if(!empty($title)): ?>
                <div class="citykomi-card__name">
                    <?php echo $title ?>
                </div>
                <?php endif;?>
            <?php if(!empty($imageSource['small_base64'])): ?>
                <div class="citykomi-card__img__container">
                    <img class="citykomi-card__img" src="data:image/jpeg;base64,<?php echo $imageSource['small_base64'] ?>" alt="Image de l'événement">
                </div>
                <?php endif;?>
        </div>
      

        <?php if(!empty($detail)): ?>
        <p class="citykomi-card__detail"><?php echo CityKomiPlugin::transformeEnParagraphes(CityKomiPlugin::linkifyHTML($detail)); ?></p>
        <?php endif; ?>
        
       
        <p class="citykomi-card__date citykomi-card__date--start"><strong>Date de début :</strong> <?php echo date('d-m-Y', strtotime($startAt)) ?></p>
        <p class="citykomi-card__date citykomi-card__date--end"><strong>Date de fin :</strong> <?php echo date('d-m-Y', strtotime($endAt)) ?></p>
    </div>
</div>