<?php

// This file has been auto-generated by the Symfony Routing Component.

return [
    '_preview_error' => [['code', '_format'], ['_controller' => 'error_controller::preview', '_format' => 'html'], ['code' => '\\d+'], [['variable', '.', '[^/]++', '_format', true], ['variable', '/', '\\d+', 'code', true], ['text', '/_error']], [], [], []],
    '_wdt' => [['token'], ['_controller' => 'web_profiler.controller.profiler::toolbarAction'], [], [['variable', '/', '[^/]++', 'token', true], ['text', '/_wdt']], [], [], []],
    '_profiler_home' => [[], ['_controller' => 'web_profiler.controller.profiler::homeAction'], [], [['text', '/_profiler/']], [], [], []],
    '_profiler_search' => [[], ['_controller' => 'web_profiler.controller.profiler::searchAction'], [], [['text', '/_profiler/search']], [], [], []],
    '_profiler_search_bar' => [[], ['_controller' => 'web_profiler.controller.profiler::searchBarAction'], [], [['text', '/_profiler/search_bar']], [], [], []],
    '_profiler_phpinfo' => [[], ['_controller' => 'web_profiler.controller.profiler::phpinfoAction'], [], [['text', '/_profiler/phpinfo']], [], [], []],
    '_profiler_xdebug' => [[], ['_controller' => 'web_profiler.controller.profiler::xdebugAction'], [], [['text', '/_profiler/xdebug']], [], [], []],
    '_profiler_search_results' => [['token'], ['_controller' => 'web_profiler.controller.profiler::searchResultsAction'], [], [['text', '/search/results'], ['variable', '/', '[^/]++', 'token', true], ['text', '/_profiler']], [], [], []],
    '_profiler_open_file' => [[], ['_controller' => 'web_profiler.controller.profiler::openAction'], [], [['text', '/_profiler/open']], [], [], []],
    '_profiler' => [['token'], ['_controller' => 'web_profiler.controller.profiler::panelAction'], [], [['variable', '/', '[^/]++', 'token', true], ['text', '/_profiler']], [], [], []],
    '_profiler_router' => [['token'], ['_controller' => 'web_profiler.controller.router::panelAction'], [], [['text', '/router'], ['variable', '/', '[^/]++', 'token', true], ['text', '/_profiler']], [], [], []],
    '_profiler_exception' => [['token'], ['_controller' => 'web_profiler.controller.exception_panel::body'], [], [['text', '/exception'], ['variable', '/', '[^/]++', 'token', true], ['text', '/_profiler']], [], [], []],
    '_profiler_exception_css' => [['token'], ['_controller' => 'web_profiler.controller.exception_panel::stylesheet'], [], [['text', '/exception.css'], ['variable', '/', '[^/]++', 'token', true], ['text', '/_profiler']], [], [], []],
    'app_categorie_index_back' => [[], ['_controller' => 'App\\Controller\\CategorieController::index'], [], [['text', '/categorie/show/back']], [], [], []],
    'app_categorie_new' => [[], ['_controller' => 'App\\Controller\\CategorieController::new'], [], [['text', '/categorie/new']], [], [], []],
    'app_categorie_show' => [['id'], ['_controller' => 'App\\Controller\\CategorieController::show'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/categorie']], [], [], []],
    'app_categorie_edit' => [['id'], ['_controller' => 'App\\Controller\\CategorieController::edit'], [], [['text', '/edit'], ['variable', '/', '[^/]++', 'id', true], ['text', '/categorie']], [], [], []],
    'app_categorie_delete' => [['id'], ['_controller' => 'App\\Controller\\CategorieController::delete'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/categorie']], [], [], []],
    'app_produit_index_back' => [[], ['_controller' => 'App\\Controller\\ProduitController::AffichageBack'], [], [['text', '/produit/show/back']], [], [], []],
    'app_produit_index_front' => [[], ['_controller' => 'App\\Controller\\ProduitController::AffichageFront'], [], [['text', '/produit/show/front']], [], [], []],
    'app_produit_new' => [[], ['_controller' => 'App\\Controller\\ProduitController::new'], [], [['text', '/produit/new']], [], [], []],
    'app_produit_show' => [['id'], ['_controller' => 'App\\Controller\\ProduitController::show'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/produit']], [], [], []],
    'app_produit_edit' => [['id'], ['_controller' => 'App\\Controller\\ProduitController::edit'], [], [['text', '/edit'], ['variable', '/', '[^/]++', 'id', true], ['text', '/produit']], [], [], []],
    'app_produit_delete' => [['id'], ['_controller' => 'App\\Controller\\ProduitController::delete'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/produit']], [], [], []],
    'back' => [[], ['_controller' => 'App\\Controller\\TraitementController::index'], [], [['text', '/back']], [], [], []],
    'app_front' => [[], ['_controller' => 'App\\Controller\\TraitementController::index_front'], [], [['text', '/front']], [], [], []],
];
