<h1>Multicanaux</h1>
<div class="citykomi-multicanal__container">
<?php
 $canaux = json_decode($this->sendRequest('/v1/public/partners/channels/179?generate_qrcode=false'),true);
$available_canal = get_option('citykomi_canaux');



if (array_key_exists('data', $canaux) && !empty($canaux['data'])) {
    $canaux_id = [];
?>
<div class="citykomi-multicanal__gauche">
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
<div class='citykomi-multicanal__droite'>
<?php foreach ($canaux_id as $canal_id => $canal_data): ?>
    <?php if ($canal_data['status'] == 1 && in_array($canal_id, $available_canal)) : ?>
        <div class='citykomi-card__group'>
        <!-- <h2 class="citykomi__canal__title"><?php echo $canal_data['name']; ?></h2> -->
        <div class="citykomi-card__items citykomiFilterItemsJs" data-slug="<?php echo $canal_data['slug']; ?>" data-id="<?php echo $canal_id; ?>" data-page="1">
        <?php $canal_data = json_decode($this->sendRequest('/v1/public/partners/messages/' . $canal_id), true); ?>
            <?php $events = $canal_data['data']['informations']; ?>
            <?php foreach ($events as $event):
                extract($event["content"]);
            ?>
            <?php include 'card.php'; ?>
            
            <?php endforeach; ?>
        </div>
            </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>

(function ($) {
    $(document).ready(function () {
        $(".citykomiFilterJs").click(function () {
            var dataId = $(this).data("id");

            // Masquer tous les éléments .citykomiFilterItemJs
            $(".citykomiFilterItemsJs").hide();
            // Afficher les éléments ayant le même data-id que le filtre cliqué
            $parent = $(".citykomiFilterItemsJs[data-id='" + dataId + "']").show();
            $count = $parent.find(".citykomiFilterItemJs").length;
            $elementsParPage = 4;
            $nbpage = Math.ceil($count / $elementsParPage);
            $parent.attr('data-nb', $count).attr('data-nbpage', $nbpage);

            if ($nbpage > 1) {
                $parent.parent().append('<ul class="citykomiPagination"></ul>');
                var $pagination = $parent.parent().find('.citykomiPagination');

                for (var i = 1; i <= $nbpage; i++) {
                    var $pageLink = $('<li class="citykomiPaginationItem"><a href="#" class="citykomiPaginationLink">Page ' + i + '</a></li>');
                    $pagination.append($pageLink);
                }
            }
        });

        $("body").on('click', ".citykomiPaginationLink", function () {
            var $parent = $(this).parent().css('background', 'red').find('.citykomiFilterItemsJs').css('background', 'red');
            var currentPage = $(this).data('page');
            alert('Page ' + currentPage);
            var elementsPerPage = 4;

            // Mettre à jour l'attribut data-currentpage
            $parent.attr('data-currentpage', currentPage);

            $parent.find('.citykomiFilterItemJs').removeClass('current');

            var startIndex = (currentPage - 1) * elementsPerPage;
            var endIndex = startIndex + elementsPerPage;

            $parent.find('.citykomiFilterItemJs').slice(startIndex, endIndex).addClass('current');
        });
    });
})(jQuery);

</script>
</div>
</div>