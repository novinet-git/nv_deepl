<?php

rex_extension::register('PACKAGES_INCLUDED', function ($ep) {
    rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', 'nvDeepl::getPanel', rex_extension::LATE);
}, rex_extension::LATE);
