<div class="citykomi-card citykomiFilterItemJs">
    <h2 class="citykomi-card__title"><?= $title ?></h2>
    <p class="citykomi-card__date citykomi-card__date--start"><strong>Date de début :</strong> <?= date('d-m-Y', strtotime($startAt)) ?></p>
    <p class="citykomi-card__date citykomi-card__date--end"><strong>Date de fin :</strong> <?= date('d-m-Y', strtotime($endAt)) ?></p>
    <p class="citykomi-card__detail"><?= CityKomiPlugin::transformeEnParagraphes($detail) ?></p>
    <img class="citykomi-card__img" src="data:image/jpeg;base64,<?= $imageSource['small_base64'] ?>" alt="Image de l'événement">
</div>