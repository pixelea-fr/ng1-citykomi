
<div class="citykomi-multicanal__container">
<?php
 $canaux = json_decode($this->sendRequest('/v1/public/partners/channels/179?generate_qrcode=false'),true);
$available_canal = get_option('citykomi_canaux');



if (array_key_exists('data', $canaux) && !empty($canaux['data'])) {
    $canaux_id = [];
?>
<div class="citykomi-multicanal__gauche">
    <div class="citykomi-multicanal__bg">
        <?php echo file_get_contents(__DIR__.'/../assets/img/citycomi-fond-a.svg')?>    
    </div>
    <div class="citykomi-multicanal__gauche__content">
    <h1 class="is-style-h2"> Les profils sont classés en <br>
<strong>9 domaines professionnels :</strong></h1>
<p>Sélectionnez le domaine professionnel correspondant à votre besoin en utilisant les catégories ci-dessous :</p>
<div class="citykomi-filter__items">
<?php
    foreach ($canaux['data'] as $item) {
        $name = $item['name'];
        $id = $item['id'];
        $status = $item['status'];
        $slug = CitykomiPlugin::text_to_html_class( $name);

        if ($status == 1 && in_array($id, $available_canal)) :
            ?>
            <label class="citykomi-filter__item citykomi-filter_label" >
                <input class='citykomi-filter__radio citykomiFilterJs ' type="radio" name="category" value="<?= $slug ?>" data-id="<?= $id ?>">
                <?= $name ?>
            </label>
       
            <?php
            $canaux_id[ $id] = array("name" => $name,'status' => $status,'slug' => $slug);
        endif;
    }
    ?>
    </div>
    <?php
} else {
    echo 'Les données "data" sont vides ou n\'existent pas.';
}
?>
</div>
</div>
<?php 
    // CREATION D4UN ARRAY d'IMAGE Des catégorie
    foreach ($canaux_id as $canal_id => $canal_data):
        if ($canal_data['status'] == 1 && in_array($canal_id, $available_canal)):
            $canal_slug = $canal_data['slug'];
            $category = $canal_data['name'];
            $canal_data = json_decode($this->sendRequest('/v1/public/partners/messages/' . $canal_id), true);
            $events = $canal_data['data']['informations'];
    
            foreach ($events as $event):
                extract($event["content"]);
                if (preg_match('/^sur ce canal/i', strtolower($detail))):
                    $img_category_array[$canal_id] = "<div class='citykomi-card__category__img__container'><img class='citykomi-card__category__img' src='data:image/jpeg;base64," . $imageSource['small_base64']."'/></div>";
                endif;
            endforeach;
        endif;
    endforeach;
//end 
    ?>


<div class='citykomi-multicanal__droite'>
    <div class="citykomi-multicanal__bg">
        <?php echo file_get_contents(__DIR__.'/../assets/img/citycomi-fond-b.svg')?>    
    </div>


<?php $i=0;?>

<?php foreach ($canaux_id as $canal_id => $canal_data): ?>
    <?php if ($canal_data['status'] == 1 && in_array($canal_id, $available_canal)) : ?>
        <?php $i++;;?>
        <?php $canal_slug = $canal_data['slug']; ?>
        <div class='citykomi-card__group citykomiGroupJs <?php if($i===1){echo 'active';} ?>' data-canal="<?php echo $canal_slug;?>" >
        <?php $category = $canal_data['name']; ?>
        <div class="citykomi-card__items citykomiFilterItemsJs" data-slug="<?php echo $canal_slug; ?>" data-id="<?php echo $canal_id; ?>" data-page="1">
        <?php $canal_data = json_decode($this->sendRequest('/v1/public/partners/messages/' . $canal_id), true); ?>
            <?php $events = $canal_data['data']['informations']; ?>
            <?php foreach ($events as $event):
                extract($event["content"]);
            ?>
            <?php // if (!preg_match('/^sur ce canal/i', strtolower($detail))): ?>
                 <?php include 'card.php'; ?>
            <?php // endif; ?>
            
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>

(function ($) {
    $(document).ready(function () {
        $(".citykomiFilterJs").click(function () {
            var cat = $(this).val();
            $('.citykomiGroupJs').removeClass('active');
            $('.citykomiGroupJs[data-canal="' + cat + '"]').addClass('active');
        });
        // $(".citykomiFilterJs").click(function () {
        //     var dataId = $(this).data("id");

        //     // Masquer tous les éléments .citykomiFilterItemJs
        //     $(".citykomiFilterItemsJs").hide();
        //     // Afficher les éléments ayant le même data-id que le filtre cliqué
        //     $parent = $(".citykomiFilterItemsJs[data-id='" + dataId + "']").show();
        //     $count = $parent.find(".citykomiFilterItemJs").length;
        //     $elementsParPage = 4;
        //     $nbpage = Math.ceil($count / $elementsParPage);
        //     $parent.attr('data-nb', $count).attr('data-nbpage', $nbpage);

        //     if ($nbpage > 1) {
        //         $parent.parent().append('<ul class="citykomiPagination"></ul>');
        //         var $pagination = $parent.parent().find('.citykomiPagination');

        //         for (var i = 1; i <= $nbpage; i++) {
        //             var $pageLink = $('<li class="citykomiPaginationItem"><a href="#" class="citykomiPaginationLink">Page ' + i + '</a></li>');
        //             $pagination.append($pageLink);
        //         }
        //     }
        // });

        // $("body").on('click', ".citykomiPaginationLink", function () {
        //     var $parent = $(this).parent().css('background', 'red').find('.citykomiFilterItemsJs').css('background', 'red');
        //     var currentPage = $(this).data('page');
     
        //     var elementsPerPage = 4;

        //     // Mettre à jour l'attribut data-currentpage
        //     $parent.attr('data-currentpage', currentPage);

        //     $parent.find('.citykomiFilterItemJs').removeClass('current');

        //     var startIndex = (currentPage - 1) * elementsPerPage;
        //     var endIndex = startIndex + elementsPerPage;

        //     $parent.find('.citykomiFilterItemJs').slice(startIndex, endIndex).addClass('current');
        // });
          // Lorsqu'un bouton radio est changé (sélectionné/désélectionné)
          $("body").on('change', 'input[type="radio"]', function() {

            // Si le bouton radio est coché (sélectionné)
            if ($(this).is(':checked')) {
            // Ajoute la classe "active" au label associé
            $(this).parent().parent().children().removeClass('active');
                $(this).parent().addClass('active');
            } else {
            // Si le bouton radio est désélectionné, supprime la classe "active"
            $(this).parent().removeClass('active');
            }
            });
        });
        $(window).load(function() { 
            $('.citykomi-filter__item:first-of-type').trigger('click');
        });

})(jQuery);

</script>
</div>
</div>