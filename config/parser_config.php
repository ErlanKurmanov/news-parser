<?php // config/parser_config.php

return [
    'rt' => [
        'listUrl' => 'https://www.rt.com/news/',
        'baseUrl' => 'https://www.rt.com',
        // Selectors for one of the RT news pages
        'itemSelector' => 'li.js-listing__item',
        // Title
        'titleSelector' => '.list-card__content--title a',
        // Image
        'imageSelector' => 'picture img.media__item',
        'imageAttribute' => 'data-src', 
        'descriptionSelector' => '.list-card__content--summary a',
        // The link to the article
        'linkSelector' => '.list-card__content--title a',
        'linkAttribute' => 'href',
    ],

        'vesti' => [
        'listUrl' => 'https://vesti.kg/',
        'baseUrl' => 'https://vesti.kg',
        'itemSelector' => '.itemContainer article.itemView, .news-item, article.item',
        'titleSelector' => 'h2 a, .item-title a, .title a',
        'imageSelector' => '.itemImageBlock img, .item-image img, .news-image img, img.lazy',
        'imageAttribute' => 'data-src', 
        'alternativeImageSelector' => '.itemImageBlock img, .item-image img',
        'descriptionSelector' => '.itemIntroText p, .item-description, .news-description p',
        'linkSelector' => 'h2 a, .item-title a, .title a',
        'linkAttribute' => 'href',
    ],

    'azattyk' => [
        'listUrl' => 'https://rus.azattyq.org/z/23725',
        'baseUrl' => 'https://rus.azattyk.org',
        'itemSelector' => 'li.archive-list__item .media-block',
        'titleSelector' => '.media-block__title',
        'imageSelector' => '.img-wrap img',
        'imageAttribute' => 'src',
        'alternativeImageSelector' => '.img-wrap img',
        'descriptionSelector' => '.perex',
        'linkSelector' => '.media-block__content a',
        'linkAttribute' => 'href',
    ],



];