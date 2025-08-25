<?php

function view($path, $data = []) {
    extract($data);
    require __DIR__ . '/../views/' . $path . '.php';
}
