<?php

if (!$this->hasConfig()) {

    $this->setConfig([
        "date_install" => time(),
        "date_update" => time(),
    ]);
}
